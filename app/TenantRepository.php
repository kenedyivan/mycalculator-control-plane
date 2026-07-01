<?php

namespace App;

use Illuminate\Support\Str;

class TenantRepository
{
    public function filePath(): string
    {
        return storage_path('app/tenants.json');
    }
    public function all(): array
    {
        if (!file_exists($this->filePath())) return [];
        $data = json_decode(file_get_contents($this->filePath()), true);
        return is_array($data) ? $data : [];
    }
    public function find(string $id): ?array
    {
        foreach ($this->all() as $tenant) if (($tenant['id'] ?? '') === $id) return $tenant;
        return null;
    }
    public function create(array $data): array
    {
        $tenants = $this->all();
        $tenant = [
            'id' => 'tenant_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(5)),
            'name' => trim($data['name']),
            'subdomain' => trim($data['subdomain']),
            'target_host' => trim($data['target_host']),
            'ssh_user' => trim($data['ssh_user'] ?? 'ubuntu'),
            'app_port' => (int)($data['app_port'] ?? 8401),
            'image' => trim($data['image'] ?? env('TENANT_DEFAULT_IMAGE')),
            'status' => 'PENDING',
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ];
        $tenants[] = $tenant;
        $this->save($tenants);
        return $tenant;
    }
    public function updateStatus(string $id, string $status): ?array
    {
        $allowed = [
            'PENDING',
            'PROVISIONING',
            'ACTIVE',
            'SUSPENDED',
            'FAILED',
        ];
        if (!in_array($status, $allowed, true)) return null;
        $tenants = $this->all();
        foreach ($tenants as &$tenant) {
            if (($tenant['id'] ?? '') === $id) {
                $tenant['status'] = $status;
                $tenant['updated_at'] = now()->toIso8601String();
                $this->save($tenants);
                return $tenant;
            }
        }
        return null;
    }
    private function save(array $tenants): void
    {
        if (!is_dir(dirname($this->filePath()))) mkdir(dirname($this->filePath()), 0775, true);
        file_put_contents($this->filePath(), json_encode(array_values($tenants), JSON_PRETTY_PRINT) . PHP_EOL);
    }

    public function delete(string $id): bool
    {
        $tenants = $this->all();
        $filtered = array_filter($tenants, function ($tenant) use ($id) {
            return ($tenant['id'] ?? '') !== $id;
        });
        if (count($filtered) === count($tenants)) {
            return false;
        }
        $this->save(array_values($filtered));
        return true;
    }
}
