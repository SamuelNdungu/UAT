@extends('layouts.appPages')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 mt-4 mb-5">
                <div class="card-body p-4">
                    <h1 class="mb-2">Policy Type Details</h1>
                    <p class="text-muted mb-4">Below are the details for this policy type.</p>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-file-signature me-2"></i>Type Name</label>
                        <input type="text" class="form-control" value="{{ $type->type_name }}" readonly placeholder="e.g. Motor Private">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-user me-2"></i>User ID</label>
                        <input type="text" class="form-control" value="{{ $type->user_id }}" readonly placeholder="e.g. 1">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-plus me-2"></i>Created At</label>
                            <input type="text" class="form-control" value="{{ $type->created_at }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-calendar-check me-2"></i>Updated At</label>
                            <input type="text" class="form-control" value="{{ $type->updated_at }}" readonly>
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <a href="{{ route('policy_types.index') }}" class="btn btn-secondary btn-lg w-100 shadow-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
