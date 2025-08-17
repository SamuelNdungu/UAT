@extends('layouts.appPages')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 mt-4 mb-5">
                <div class="card-body p-4">
                    <h1 class="mb-2">Edit Insurance Company</h1>
                    <p class="text-muted mb-4"></p>
                    <form method="POST" action="{{ route('insurance_companies.update', $company->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="group-heading">Company Details</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="e.g. Jubilee Insurance" required value="{{ old('name', $company->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="e.g. info@jubilee.com" value="{{ old('email', $company->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" placeholder="e.g. +254700000000" value="{{ old('phone', $company->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" placeholder="e.g. P.O. Box 1234" value="{{ old('address', $company->address) }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="group-heading">Location</div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-city"></i></span>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" placeholder="e.g. Nairobi" value="{{ old('city', $company->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" placeholder="e.g. Kenya" value="{{ old('country', $company->country) }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="street" class="form-label">Street</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-road"></i></span>
                                    <input type="text" class="form-control @error('street') is-invalid @enderror" id="street" name="street" placeholder="e.g. Kimathi Street" value="{{ old('street', $company->street) }}">
                                    @error('street')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="physical_address" class="form-label">Physical Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-location-arrow"></i></span>
                                    <input type="text" class="form-control @error('physical_address') is-invalid @enderror" id="physical_address" name="physical_address" placeholder="e.g. 1st Floor, Jubilee House" value="{{ old('physical_address', $company->physical_address) }}">
                                    @error('physical_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                                        <i class="fas fa-save"></i> Update
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('insurance_companies.index') }}" class="btn btn-secondary btn-lg w-100 shadow-sm">
                                        <i class="fas fa-arrow-left"></i> Back 
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
