@extends('layouts.appPages')

@section('content')
<style>
    /* Styling for search results */
    .result-item {
        padding: 8px;
        cursor: pointer;
        background-color: #f8f9fa;
        border-bottom: 1px solid #ddd;
    }
    .result-item:hover {
        background-color: #e9ecef;
        color: #0056b3;
    }

    /* Form styling for a modern look */
    .form-group label {
        font-weight: bold;
    }

    /* Group headings */
    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 mt-4 mb-5">
                <div class="card-body p-4">
                  
                    <div class="group-heading mb-13">Company Details</div>
                     
                    <div class="row mt-4 mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-building me-2"></i>Name</label>
                            <input type="text" class="form-control" value="{{ $company->name }}" readonly placeholder="e.g. Jubilee Insurance">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                            <input type="text" class="form-control" value="{{ $company->email }}" readonly placeholder="e.g. info@jubilee.com">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-phone me-2"></i>Phone</label>
                            <input type="text" class="form-control" value="{{ $company->phone }}" readonly placeholder="e.g. +254700000000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Address</label>
                            <input type="text" class="form-control" value="{{ $company->address }}" readonly placeholder="e.g. P.O. Box 1234">
                        </div>
                    </div>
                     <div class="group-heading">Location</div>
                    <div class="row mb-3 mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-city me-2"></i>City</label>
                            <input type="text" class="form-control" value="{{ $company->city }}" readonly placeholder="e.g. Nairobi">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-flag me-2"></i>Country</label>
                            <input type="text" class="form-control" value="{{ $company->country }}" readonly placeholder="e.g. Kenya">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label"><i class="fas fa-road me-2"></i>Street</label>
                            <input type="text" class="form-control" value="{{ $company->street }}" readonly placeholder="e.g. Kimathi Street">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label"><i class="fas fa-location-arrow me-2"></i>Physical Address</label>
                            <input type="text" class="form-control" value="{{ $company->physical_address }}" readonly placeholder="e.g. 1st Floor, Jubilee House">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-plus me-2"></i>Created At</label>
                            <input type="text" class="form-control" value="{{ $company->created_at }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-check me-2"></i>Updated At</label>
                            <input type="text" class="form-control" value="{{ $company->updated_at }}" readonly>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <a href="{{ route('insurance_companies.index') }}" class="btn btn-secondary btn-lg w-100 shadow-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
