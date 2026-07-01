<?php

namespace App\Console\Commands;

use App\TenantRepository;
use Illuminate\Console\Command;

class SuspendTenantCommand extends Command
{
    protected $signature = 'tenant:suspend {tenantId}';
    protected $description = 'Suspend a tenant environment';

    public function handle(TenantRepository $tenants, \App\Services\TenantServer $server): int
    {
        $tenant = $tenants->find($this->argument('tenantId'));

        if (! $tenant) {
            $this->error('Tenant not found.');
            return self::FAILURE;
        }

        try {
            $this->output->write($server->suspend($tenant));
            $tenants->updateStatus($tenant['id'], 'SUSPENDED');
            $this->info('Tenant suspended.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
