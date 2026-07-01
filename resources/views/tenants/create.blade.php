@extends('layout')
@section('content')
<div class="card">
    <h2>Create Tenant</h2>
    <form method="post" action="{{ route('tenants.store') }}">@csrf<div class="grid">
            <div><label>Tenant Name</label><input name="name" value="{{ old('name', 'Besania SACCO') }}"></div>
            <div><label>Subdomain</label><input name="subdomain" value="{{ old('subdomain', 'besania') }}"></div>
            <div><label>Target Host / Multipass VM IP</label><input name="target_host" value="{{ old('target_host', '192.168.64.6') }}"></div>
            <div><label>SSH User</label><input name="ssh_user" value="{{ old('ssh_user', 'ubuntu') }}"></div>
            <div><label>App Port</label><input name="app_port" value="{{ old('app_port', '8401') }}"></div>
            <div><label>Docker Image</label><input name="image" value="{{ old('image', $defaultImage) }}"></div>
        </div><button>Create Tenant</button></form>
</div>
@endsection