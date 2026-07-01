<?php

namespace App\Console\Commands;

use App\TenantRepository;
use Illuminate\Console\Command;

class ResumeTenantCommand extends Command
{
    protected $signature = 'tenant:resume {tenantId}';
    protected $description = 'Resume a tenant environment';

    public function handle(
        TenantRepository $tenants,
        \App\Services\TenantServer $server
    ): int {
        $tenant = $tenants->find($this->argument('tenantId'));

        if (! $tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        try {
            $this->info("Resuming tenant {$tenant['name']}...");

            $output = $server->resume($tenant);

            if (! empty($output)) {
                $this->line($output);
            }

            $tenants->updateStatus($tenant['id'], 'ACTIVE');

            $this->info('Tenant resumed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to resume tenant.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
