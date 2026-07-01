<?php

namespace App\Console\Commands;

use App\Services\TenantServer;
use App\TenantRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenant:provision {tenantId} {--no-ssh : Only render deployment files locally}';

    protected $description = 'Render, bootstrap server, and deploy MyCalculator tenant files to a target VM over SSH';

    public function handle(TenantRepository $tenants, TenantServer $server): int
    {
        $tenant = $tenants->find($this->argument('tenantId'));

        if (! $tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        $tenants->updateStatus($tenant['id'], 'PROVISIONING');

        $deployDir = base_path('deployments/' . $tenant['id']);

        File::deleteDirectory($deployDir);
        File::ensureDirectoryExists($deployDir);

        $this->renderTemplate('tenant.env', $deployDir . '/.env', $tenant);
        $this->renderTemplate('docker-compose.prod.yml', $deployDir . '/docker-compose.prod.yml', $tenant);
        $this->renderTemplate('nginx.conf', $deployDir . '/nginx.conf', $tenant);

        $this->info("Deployment files rendered: {$deployDir}");

        if ($this->option('no-ssh')) {
            $this->warn('Skipped SSH deployment.');
            return self::SUCCESS;
        }

        try {
            $this->info('Bootstrapping tenant server...');
            $this->line($server->bootstrap($tenant));

            $this->info('Copying deployment files...');
            $this->line($server->copyDeploymentFiles($tenant, $deployDir));

            $this->info('Starting application...');
            $this->line($server->start($tenant));

            $tenants->updateStatus($tenant['id'], 'ACTIVE');

            $this->info('Tenant provisioned successfully.');
            $this->info('Open: http://' . $tenant['target_host'] . ':' . $tenant['app_port']);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $tenants->updateStatus($tenant['id'], 'FAILED');

            $this->error('Provisioning failed.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function renderTemplate(string $template, string $destination, array $tenant): void
    {
        $content = File::get(base_path('templates/' . $template));

        File::put($destination, strtr($content, [
            '{{TENANT_ID}}' => $tenant['id'],
            '{{TENANT_NAME}}' => $tenant['name'],
            '{{TARGET_HOST}}' => $tenant['target_host'],
            '{{APP_PORT}}' => (string) $tenant['app_port'],
            '{{IMAGE}}' => $tenant['image'],
            '{{APP_KEY}}' => 'base64:' . base64_encode(random_bytes(32)),
        ]));
    }
}