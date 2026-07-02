<?php

namespace App\Jobs;

use App\Services\TenantServer;
use App\TenantRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use App\Models\TenantOperation;

class ProvisionTenantJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public function __construct(public string $tenantId) {}

    public function handle(TenantRepository $tenants, TenantServer $server): void
    {
        $tenant = $tenants->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $tenants->updateStatus($tenant['id'], 'PROVISIONING');

        $operation = TenantOperation::create([
            'tenant_id' => $tenant['id'],
            'operation' => 'provision',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $deployDir = base_path('deployments/' . $tenant['id']);

            File::deleteDirectory($deployDir);
            File::ensureDirectoryExists($deployDir);

            $this->renderTemplate('tenant.env', $deployDir . '/.env', $tenant);
            $this->renderTemplate('docker-compose.prod.yml', $deployDir . '/docker-compose.prod.yml', $tenant);
            $this->renderTemplate('nginx.conf', $deployDir . '/nginx.conf', $tenant);

            $operation->update([
                'current_step' => 'Bootstrapping Server',
            ]);
            $output = $server->bootstrap($tenant);
            $operation->appendLog($output);
            $operation->update([
                'current_step' => 'Copying Deployment Files',
            ]);
            $output = $server->copyDeploymentFiles($tenant, $deployDir);
            $operation->appendLog($output);
            $operation->update([
                'current_step' => 'Starting Containers',
            ]);
            $output = $server->start($tenant);
            $operation->appendLog($output);
            $operation->update([
                'status' => 'success',
                'current_step' => null,
                'finished_at' => now(),
            ]);
            $tenants->updateStatus($tenant['id'], 'ACTIVE');
        } catch (\Throwable $e) {
            $operation->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            $tenants->updateStatus($tenant['id'], 'FAILED');
            throw $e;
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
