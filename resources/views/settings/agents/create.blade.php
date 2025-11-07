@extends('layouts.appPages')

@section('content')

<div class="container">
    <h2>Add Agent</h2>
    <form method="POST" action="{{ route('settings.agents.store') }}">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-3">
            <label>KRA PIN</label>
            <input type="text" name="kra_pin" class="form-control">
        </div>
        <div class="mb-3">
            <label>Commission Rate</label>
            <input type="number" step="0.01" name="commission_rate" class="form-control" value="0.00">
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <button class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
