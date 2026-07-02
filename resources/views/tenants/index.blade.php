@extends('layout')
@section('content')
<div class="card">
    <h2>Tenants</h2>
    <p>This Laravel control plane stores tenant records and gives you a provisioning command for Multipass targets.</p><a class="button" href="{{ route('tenants.create') }}">Create Tenant</a>
</div>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Target</th>
                <th>Status</th>
                <th>Logs</th>
                <th>App URL</th>
                <th>Command</th>
            </tr>
        </thead>
        <tbody>@forelse($tenants as $tenant)<tr>
                <td><strong><a href="{{ route('tenants.show', $tenant['id']) }}">{{ $tenant['name'] }}</a></strong><br><code>{{ $tenant['id'] }}</code></td>
                <td>{{ $tenant['ssh_user'] }} @ {{ $tenant['target_host'] }} <br>Port {{ $tenant['app_port'] }}</td>
                <td class="status {{ $tenant['status'] }}">{{ $tenant['status'] }}</td>
                <td>
                    <a href="{{ route('tenants.operations', $tenant['id']) }}">
                        View Logs
                    </a>
                </td>
                <td><a target="_blank" href="http://{{ $tenant['target_host'] }}:{{ $tenant['app_port'] }}">Open app</a></td>
                <td><code>php artisan tenant:provision {{ $tenant['id'] }}</code></td>
            </tr>@empty<tr>
                <td colspan="5">No tenants yet.</td>
            </tr>@endforelse</tbody>
    </table>
</div>
@endsection