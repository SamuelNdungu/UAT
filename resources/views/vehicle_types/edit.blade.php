@extends('layouts.appPages')
@section('content')
<div class="container fancy-container">
    <div class="gradient-banner mb-4">
        <div class="d-flex align-items-center">
            <span class="banner-icon me-2"><i class="fas fa-car"></i></span>
            <h1 class="my-2 mb-0" style="font-weight: 700; letter-spacing: 1px;">Edit Vehicle Type</h1>
        </div>
        <p class="text-muted mb-0" style="font-size:1.1rem;">Update the details below to modify this vehicle type.</p>
    </div>
    <hr class="section-divider mb-4">
    <div class="card shadow-sm border-0 mb-3" style="background: #f8fafc; border-radius: 12px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('vehicle_types.update', $vehicleType->id) }}">
                @csrf
                @method('PUT')
                <div class="mb-3 position-relative">
                    <label for="make" class="form-label">Make</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car"></i></span>
                        <input type="text" class="form-control" id="make" name="make" placeholder="e.g. Toyota" value="{{ old('make', $vehicleType->make) }}">
                    </div>
                    @error('make')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3 position-relative">
                    <label for="model" class="form-label">Model</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-car-side"></i></span>
                        <input type="text" class="form-control" id="model" name="model" placeholder="e.g. Corolla" value="{{ old('model', $vehicleType->model) }}">
                    </div>
                    @error('model')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3" style="font-size:1.1rem; font-weight:600;">
                    <i class="fas fa-save"></i> Update Vehicle Type
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
