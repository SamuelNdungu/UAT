@extends('layouts.appPages')

@section('content')
<div class="container">
    <h2>Agent Details</h2>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">{{ $agent->name }}</h5>
            <p><strong>Agent Code:</strong> {{ $agent->agent_code }}</p>
            <p><strong>Phone:</strong> {{ $agent->phone }}</p>
            <p><strong>Email:</strong> {{ $agent->email }}</p>
            <p><strong>KRA PIN:</strong> {{ $agent->kra_pin }}</p>
            <p><strong>Commission Rate:</strong> {{ $agent->commission_rate }}</p>
            <p><strong>Status:</strong> {{ $agent->status }}</p>
            <p><strong>Created By:</strong> {{ $agent->user ? $agent->user->name : '-' }}</p>
            <p><strong>Created At:</strong> {{ $agent->created_at->format('d-m-Y H:i') }}</p>
        </div>
    </div>
    <a href="{{ route('settings.agents.index') }}" class="btn btn-secondary">Back to Agents</a>
    <a href="{{ route('settings.agents.edit', $agent) }}" class="btn btn-warning">Edit Agent</a>
</div>
@endsection
