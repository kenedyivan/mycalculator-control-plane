@extends('layout')

@section('content')

<h2>{{ strtoupper($operation->operation) }}</h2>

<p><strong>Status:</strong> {{ $operation->status }}</p>

<p><strong>Current Step:</strong> {{ $operation->current_step }}</p>

@if($operation->error)
<div class="alert alert-danger">
    <pre>{{ $operation->error }}</pre>
</div>
@endif

<h3>Deployment Log</h3>

<pre>{{ $operation->log }}</pre>

@endsection