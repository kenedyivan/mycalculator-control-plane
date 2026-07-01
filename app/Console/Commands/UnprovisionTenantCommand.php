<?php

namespace App\Console\Commands;

use App\TenantRepository;
use Illuminate\Console\Command;

class UnprovisionTenantCommand extends Command
{
    protected $signature = 'tenant:unprovision {tenantId}';
    protected $description = 'Remove a tenant environment';

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
            $this->info("Unprovisioning tenant {$tenant['name']}...");

            $output = $server->unprovision($tenant);

            if (! empty($output)) {
                $this->line($output);
            }

            $tenants->delete($tenant['id']);

            $this->info('Tenant environment removed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to unprovision tenant.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
