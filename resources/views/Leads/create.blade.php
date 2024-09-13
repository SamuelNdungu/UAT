@extends('layouts.appPages')

@section('content')

<div class="container">
    <div class="card card-primary">
        <div class="card-header">
            <h4 class="card-title">Add New Lead</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('leads.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="lead_type">Lead Type</label>
                    <select id="lead_type" name="lead_type" class="form-control @error('lead_type') is-invalid @enderror">
                        <option value="">Select Lead Type</option>
                        <option value="Corporate" {{ old('lead_type') === 'Corporate' ? 'selected' : '' }}>Corporate</option>
                        <option value="Individual" {{ old('lead_type') === 'Individual' ? 'selected' : '' }}>Individual</option>
                    </select>
                    @error('lead_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="corporate_fields" class="d-none">
                    <div class="form-group">
                        <label for="corporate_name">Corporate Name</label>
                        <input type="text" id="corporate_name" name="corporate_name" class="form-control @error('corporate_name') is-invalid @enderror" value="{{ old('corporate_name') }}">
                        @error('corporate_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div id="individual_fields" class="d-none">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
                        @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile</label>
                    <input type="text" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}">
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="policy_type">Policy Type</label>
                    <input type="text" id="policy_type" name="policy_type" class="form-control @error('policy_type') is-invalid @enderror" value="{{ old('policy_type') }}">
                    @error('policy_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="estimated_premium">Estimated Premium</label>
                    <input type="number" id="estimated_premium" name="estimated_premium" class="form-control @error('estimated_premium') is-invalid @enderror" value="{{ old('estimated_premium') }}" step="0.01">
                    @error('estimated_premium')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="follow_up_date">Follow-up Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                    @error('follow_up_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="lead_source">Lead Source</label>
                    <input type="text" id="lead_source" name="lead_source" class="form-control @error('lead_source') is-invalid @enderror" value="{{ old('lead_source') }}">
                    @error('lead_source')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="upload">Upload</label>
                    <input type="file" id="upload" name="upload[]" class="form-control @error('upload') is-invalid @enderror" multiple>
                    @error('upload.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save Lead</button>
                    <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
 

<script>
    document.getElementById('lead_type').addEventListener('change', function() {
        const corporateFields = document.getElementById('corporate_fields');
        const individualFields = document.getElementById('individual_fields');
        
        if (this.value === 'Corporate') {
            corporateFields.classList.remove('d-none');
            individualFields.classList.add('d-none');
        } else if (this.value === 'Individual') {
            corporateFields.classList.add('d-none');
            individualFields.classList.remove('d-none');
        } else {
            corporateFields.classList.add('d-none');
            individualFields.classList.add('d-none');
        }
    });
</script>

@endsection
