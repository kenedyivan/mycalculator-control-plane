<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class TenantServer
{
    public function target(array $tenant): string
    {
        return "{$tenant['ssh_user']}@{$tenant['target_host']}";
    }

    public function remoteDir(array $tenant): string
    {
        return "/opt/mycalculator-tenants/{$tenant['id']}";
    }

    public function run(array $tenant, string $command, int $timeout = 300, ?string $input = null): string
    {
        $process = new Process([
            'ssh',
            '-o',
            'StrictHostKeyChecking=no',
            $this->target($tenant),
            $command,
        ]);

        $process->setInput($input);
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'Remote command failed.');
        }

        return $process->getOutput();
    }

    public function suspend(array $tenant): string
    {
        return $this->run(
            $tenant,
            "cd {$this->remoteDir($tenant)} && sudo docker compose -f docker-compose.prod.yml stop"
        );
    }

    public function resume(array $tenant): string
    {
        return $this->run(
            $tenant,
            "cd {$this->remoteDir($tenant)} && sudo docker compose -f docker-compose.prod.yml up -d"
        );
    }

    public function unprovision(array $tenant): string
    {
        $remoteDir = $this->remoteDir($tenant);

        return $this->run(
            $tenant,
            "cd {$remoteDir} && sudo docker compose -f docker-compose.prod.yml down -v && cd .. && sudo rm -rf {$remoteDir}",
            600
        );
    }

    public function bootstrap(array $tenant): string
    {
        $sshUser = $tenant['ssh_user'];

        $script = <<<BASH
set -e

sudo apt update -y
sudo apt install -y ca-certificates curl gnupg git unzip openssh-server docker.io docker-compose-v2

sudo systemctl enable ssh || true
sudo systemctl start ssh || true

sudo systemctl enable docker
sudo systemctl start docker

sudo usermod -aG docker {$sshUser} || true

sudo mkdir -p /opt/mycalculator-tenants
sudo chown -R {$sshUser}:{$sshUser} /opt/mycalculator-tenants

sudo docker --version
sudo docker compose version
BASH;

        return $this->run($tenant, 'bash -s', 600, $script);
    }

    public function copyDeploymentFiles(array $tenant, string $deployDir): string
    {
        $remoteDir = $this->remoteDir($tenant);

        $this->run(
            $tenant,
            "sudo mkdir -p {$remoteDir} && sudo chown -R {$tenant['ssh_user']}:{$tenant['ssh_user']} {$remoteDir}"
        );

        $target = $this->target($tenant);

        $this->localProcess([
            'scp',
            '-o',
            'StrictHostKeyChecking=no',
            $deployDir . '/.env',
            $target . ':' . $remoteDir . '/.env',
        ]);

        $this->localProcess([
            'scp',
            '-o',
            'StrictHostKeyChecking=no',
            $deployDir . '/docker-compose.prod.yml',
            $target . ':' . $remoteDir . '/docker-compose.prod.yml',
        ]);

        $this->localProcess([
            'scp',
            '-o',
            'StrictHostKeyChecking=no',
            $deployDir . '/nginx.conf',
            $target . ':' . $remoteDir . '/nginx.conf',
        ]);

        return 'Deployment files copied.';
    }

    public function start(array $tenant): string
    {
        return $this->run(
            $tenant,
            "cd {$this->remoteDir($tenant)} && sudo docker compose -f docker-compose.prod.yml pull && sudo docker compose -f docker-compose.prod.yml up -d",
            900
        );
    }

    private function localProcess(array $command, int $timeout = 300): string
    {
        $process = new Process($command, base_path(), null, null, $timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: 'Local command failed.');
        }

        return $process->getOutput();
    }
}
