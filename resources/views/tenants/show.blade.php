@extends('layout')

@section('content')

<div class="card">
    <h2>{{ $tenant['name'] }}</h2>

    <p><strong>ID:</strong> <code>{{ $tenant['id'] }}</code></p>

    <p>
        <strong>Status:</strong>
        <span class="status {{ $tenant['status'] }}">
            {{ $tenant['status'] }}
        </span>
    </p>

    <p><strong>Target:</strong> {{ $tenant['ssh_user'] }}@{{ $tenant['target_host'] }}</p>

    <p><strong>Image:</strong> <code>{{ $tenant['image'] }}</code></p>

    <p>
        <strong>App URL:</strong>
        <a target="_blank"
           href="http://{{ $tenant['target_host'] }}:{{ $tenant['app_port'] }}">
            http://{{ $tenant['target_host'] }}:{{ $tenant['app_port'] }}
        </a>
    </p>
</div>

<div class="card">
    <h3>Actions</h3>

    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:15px;">

        <form method="POST" action="{{ route('tenants.provision', $tenant['id']) }}">
            @csrf
            <button type="submit">
                🚀 Provision
            </button>
        </form>

        <form method="POST" action="{{ route('tenants.suspend', $tenant['id']) }}">
            @csrf
            <button type="submit">
                ⏸ Suspend
            </button>
        </form>

        <form method="POST" action="{{ route('tenants.resume', $tenant['id']) }}">
            @csrf
            <button type="submit">
                ▶ Resume
            </button>
        </form>

        <form method="POST"
              action="{{ route('tenants.unprovision', $tenant['id']) }}"
              onsubmit="return confirm('This will permanently remove the tenant environment. Continue?')">
            @csrf
            <button
                type="submit"
                style="background:#dc2626;color:white;">
                🗑 Unprovision
            </button>
        </form>

    </div>
</div>

<div class="card">
    <h3>Provisioning Command</h3>

    <p>Run this from your host:</p>

    <p>
        <code>
            docker compose exec control-plane php artisan tenant:provision {{ $tenant['id'] }}
        </code>
    </p>

    <p>Render files only:</p>

    <p>
        <code>
            docker compose exec control-plane php artisan tenant:provision {{ $tenant['id'] }} --no-ssh
        </code>
    </p>
</div>

<div class="card">
    <h3>Update Status</h3>

    <form method="POST" action="{{ route('tenants.status', $tenant['id']) }}">
        @csrf

        <select name="status">
            @foreach(['PENDING','PROVISIONING','ACTIVE','SUSPENDED','FAILED'] as $status)
                <option
                    value="{{ $status }}"
                    @selected($tenant['status'] === $status)>
                    {{ $status }}
                </option>
            @endforeach
        </select>

        <button type="submit">
            Save Status
        </button>
    </form>
</div>

@endsection