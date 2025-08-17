@extends('layouts.appPages')
@section('content')
<div class="container fancy-container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-car"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Vehicle Type Details</h1>
        </div>
        <p class="text-muted mb-0" style="font-size:1.1rem;">View all details for this vehicle type below.</p>
    </div>
    <hr class="section-divider mb-4">
    <div class="card shadow-sm border-0 mb-3" style="background: #f8fafc; border-radius: 12px;">
        <div class="card-body p-4">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong><i class="fas fa-car"></i> Make:</strong> {{ $vehicleType->make }}</li>
                <li class="list-group-item"><strong><i class="fas fa-car-side"></i> Model:</strong> {{ $vehicleType->model }}</li>
                <li class="list-group-item"><strong><i class="fas fa-user"></i> User ID:</strong> {{ $vehicleType->user_id }}</li>
                <li class="list-group-item"><strong><i class="fas fa-calendar-plus"></i> Created At:</strong> {{ $vehicleType->created_at }}</li>
                <li class="list-group-item"><strong><i class="fas fa-calendar-check"></i> Updated At:</strong> {{ $vehicleType->updated_at }}</li>
            </ul>
            <a href="{{ route('vehicle_types.index') }}" class="btn btn-secondary mt-4"><i class="fas fa-arrow-left"></i> Back to List</a>
        </div>
    </div>
</div>
@endsection
