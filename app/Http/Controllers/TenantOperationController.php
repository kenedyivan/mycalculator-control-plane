<?php

namespace App\Http\Controllers;

use App\Models\TenantOperation;
use Illuminate\View\View;
use App\TenantRepository;

class TenantOperationController extends Controller
{
    public function index(string $tenantId, TenantRepository $tenants): View
    {
        $tenant = $tenants->find($tenantId);

        $operations = TenantOperation::where('tenant_id', $tenantId)
            ->latest()
            ->get();

        return view('operations.index', [
            'tenant' => $tenant,
            'operations' => $operations,
        ]);
    }

    public function show(TenantOperation $operation): View
    {
        return view('operations.show', compact('operation'));
    }
}
