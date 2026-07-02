<?php

namespace App\Http\Controllers;

use App\TenantRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ProvisionTenantJob;

class TenantController extends Controller
{
    public function __construct(private TenantRepository $tenants) {}
    public function index(): View
    {
        return view('tenants.index', ['tenants' => array_reverse($this->tenants->all())]);
    }
    public function create(): View
    {
        return view('tenants.create', ['defaultImage' => env('TENANT_DEFAULT_IMAGE', 'ghcr.io/kenedyivan/mycalculator:latest')]);
    }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'subdomain' => ['required', 'string', 'max:80'],
            'target_host' => ['required', 'string', 'max:120'],
            'ssh_user' => ['required', 'string', 'max:80'],
            'app_port' => ['required', 'integer', 'min:80', 'max:65535'],
            'image' => ['required', 'string', 'max:255'],
        ]);
        $tenant = $this->tenants->create($data);
        $this->provision($tenant['id']);
        return redirect()->route('tenants.show', $tenant['id'])->with('success', 'Tenant created. Run the provisioning command shown below.');
    }
    public function show(string $tenant): View|RedirectResponse
    {
        $found = $this->tenants->find($tenant);
        if (!$found) return redirect()->route('tenants.index')->with('error', 'Tenant not found.');
        return view('tenants.show', ['tenant' => $found]);
    }
    public function updateStatus(Request $request, string $tenant): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:PENDING,PROVISIONING,ACTIVE,SUSPENDED,FAILED']]);
        $this->tenants->updateStatus($tenant, $data['status']);
        return back()->with('success', 'Tenant status updated.');
    }

    public function provision(string $tenant): RedirectResponse
    {
        $this->tenants->updateStatus($tenant, 'PROVISIONING');
        ProvisionTenantJob::dispatch($tenant);
        return back()->with('success', 'Provisioning started in the background.');
    }

    public function suspend(string $tenant): RedirectResponse
    {
        $code = Artisan::call('tenant:suspend', ['tenantId' => $tenant]);

        if ($code !== 0) {
            return back()->with('error', Artisan::output());
        }

        $this->tenants->updateStatus($tenant, 'SUSPENDED');

        return back()->with('success', 'Tenant suspended.');
    }

    public function resume(string $tenant): RedirectResponse
    {
        $code = Artisan::call('tenant:resume', ['tenantId' => $tenant]);

        if ($code !== 0) {
            return back()->with('error', Artisan::output());
        }

        $this->tenants->updateStatus($tenant, 'ACTIVE');

        return back()->with('success', 'Tenant resumed.');
    }

    public function unprovision(string $tenant): RedirectResponse
    {
        $code = Artisan::call('tenant:unprovision', ['tenantId' => $tenant]);

        if ($code !== 0) {
            return back()->with('error', Artisan::output());
        }

        $this->tenants->delete($tenant);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant unprovisioned and removed.');
    }
}
