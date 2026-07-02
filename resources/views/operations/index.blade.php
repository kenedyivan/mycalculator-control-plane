@extends('layout')

@section('content')

<div class="card">
    <h2>{{ $tenant['name'] }}</h2>

    <p>
        <strong>Tenant ID:</strong>
        <code>{{ $tenant['id'] }}</code>
    </p>

    <p>
        <strong>Operations History</strong>
    </p>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Operation</th>
                <th>Status</th>
                <th>Step</th>
                <th>Started</th>
                <th>Finished</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($operations as $operation)
            <tr>
                <td>{{ $operation->operation }}</td>
                <td>{{ $operation->status }}</td>
                <td>{{ $operation->current_step }}</td>
                <td>{{ $operation->started_at }}</td>
                <td>{{ $operation->finished_at }}</td>
                <td>
                    <a href="{{ route('operations.show', $operation) }}">
                        View
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection