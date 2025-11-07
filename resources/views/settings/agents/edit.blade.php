@extends('layouts.appPages')

@section('content')

<div class="container">
    <h2>Edit Agent</h2>
    <form method="POST" action="{{ route('settings.agents.update', $agent) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $agent->name }}" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="{{ $agent->phone }}">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $agent->email }}">
        </div>
        <div class="mb-3">
            <label>KRA PIN</label>
            <input type="text" name="kra_pin" class="form-control" value="{{ $agent->kra_pin }}">
        </div>
        <div class="mb-3">
            <label>Commission Rate</label>
            <input type="number" step="0.01" name="commission_rate" class="form-control" value="{{ $agent->commission_rate }}">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Active" @if($agent->status == 'Active') selected @endif>Active</option>
                <option value="Inactive" @if($agent->status == 'Inactive') selected @endif>Inactive</option>
            </select>
        </div>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
