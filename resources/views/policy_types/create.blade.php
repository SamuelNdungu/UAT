@extends('layouts.appPages')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow-lg border-0 mt-4 mb-5">
                <div class="card-body p-4">
                    <h1 class="mb-2">Add Policy Type</h1>
                    <p class="text-muted mb-4">Fill in the details below to register a new policy type.</p>
                    <form method="POST" action="{{ route('policy_types.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-file-signature"></i></span>
                                <input type="text" class="form-control @error('type_name') is-invalid @enderror" id="type_name" name="type_name" placeholder="e.g. Motor Private" required value="{{ old('type_name') }}">
                                @error('type_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm">
                                <i class="fas fa-save"></i> Save  
                            </button>
                            <a href="{{ route('policy_types.index') }}" class="btn btn-secondary btn-lg w-100 shadow-sm mt-2">
                                <i class="fas fa-arrow-left"></i> Back  
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
