@extends('layouts.appPages')

@section('content')
<div class="container">
    <h3 class="my-4 text-center">Edit Lead</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('leads.update', $lead->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Lead Type and Basic Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Basic Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Lead Type</label>
                        <select name="lead_type" class="form-control" disabled >
                            <option value="Individual" {{ $lead->lead_type === 'Individual' ? 'selected' : '' }}>Individual</option>
                            <option value="Corporate" {{ $lead->lead_type === 'Corporate' ? 'selected' : '' }}>Corporate</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group corporate-fields {{ $lead->lead_type === 'Corporate' ? '' : 'd-none' }}">
                        <label>Corporate Name</label>
                        <input type="text" name="corporate_name" class="form-control" value="{{ $lead->corporate_name }}">
                    </div>
                    <div class="col-md-4 form-group individual-fields {{ $lead->lead_type === 'Individual' ? '' : 'd-none' }}">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ $lead->first_name }}">
                    </div>
                    <div class="col-md-4 form-group individual-fields {{ $lead->lead_type === 'Individual' ? '' : 'd-none' }}">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ $lead->last_name }}">
                    </div>
                    <div class="col-md-4 form-group">
                        
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Mobile</label>
                        <input type="text" name="mobile" class="form-control" value="{{ $lead->mobile }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $lead->email }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy Details -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Policy Details</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Policy Type</label>
                        <input type="text" name="policy_type" class="form-control" value="{{ $lead->policy_type }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Lead Source</label>
                        <input type="text" name="lead_source" class="form-control" value="{{ $lead->lead_source }}" >
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Upload Documents</label>
                        <input type="file" name="upload[]" class="form-control" multiple disabled>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deal Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Deal Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Deal Size</label>
                        <input type="number" step="0.01" name="deal_size" class="form-control" value="{{ $lead->deal_size }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Probability (%)</label>
                        <input type="number" name="probability" class="form-control" value="{{ $lead->probability }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Weighted Revenue Forecast</label>
                        <input type="number" step="0.01" name="weighted_revenue_forecast" class="form-control" value="{{ $lead->weighted_revenue_forecast }}" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Deal Stage</label>
                        <select name="deal_stage" class="form-control" required>
                        <option value="">Select Deal Stage</option>
                        <option value="Qualification" {{ $lead->deal_stage === 'Qualification' ? 'selected' : '' }}>Qualification</option>
                        <option value="Proposal" {{ $lead->deal_stage === 'Proposal' ? 'selected' : '' }}>Proposal</option>
                        <option value="Negotiating" {{ $lead->deal_stage === 'Negotiating' ? 'selected' : '' }}>Negotiating</option>
                        <option value="Closed-Won" {{ $lead->deal_stage === 'Closed-Won' ? 'selected' : '' }}>Closed-Won</option>
                        <option value="Closed-Lost" {{ $lead->deal_stage === 'Closed-Lost' ? 'selected' : '' }}>Closed-Lost</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Deal Status</label>
                        <select name="deal_status" class="form-control" required>
                              <option value="">Select Deal Status</option>
                        <option value="Open" {{ $lead->deal_status === 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Won" {{ $lead->deal_status === 'Won' ? 'selected' : '' }}>Won</option>
                        <option value="Lost" {{ $lead->deal_status === 'Lost' ? 'selected' : '' }}>Lost</option>
                        <option value="On Hold" {{ $lead->deal_status === 'On Hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Date Initiated</label>
                        <input type="date" name="date_initiated" class="form-control" value="{{ $lead->date_initiated ? $lead->date_initiated->format('Y-m-d') : '' }}" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Closing Date</label>
                        <input type="date" name="closing_date" class="form-control" value="{{ $lead->closing_date ? $lead->closing_date->format('Y-m-d') : '' }}" placeholder="dd/mm/yyyy">
                    </div>
                </div>
            </div>
        </div>

        <!-- Follow-up Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Follow-up Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control" value="{{ $lead->follow_up_date ? $lead->follow_up_date->format('Y-m-d') : '' }}" placeholder="dd/mm/yyyy">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Next Action</label>
                        <input type="text" name="next_action" class="form-control" value="{{ $lead->next_action }}">
                    </div>
                    <div class="col-md-12 form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ $lead->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-md-2">
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Update Lead</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.querySelector('select[name="lead_type"]').addEventListener('change', function() {
        const corporateFields = document.querySelectorAll('.corporate-fields');
        const individualFields = document.querySelectorAll('.individual-fields');
        
        if (this.value === 'Corporate') {
            corporateFields.forEach(field => field.classList.remove('d-none'));
            individualFields.forEach(field => field.classList.add('d-none'));
        } else {
            corporateFields.forEach(field => field.classList.add('d-none'));
            individualFields.forEach(field => field.classList.remove('d-none'));
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Function to format dates
        function formatDateToDDMMYYYY(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const month = ('0' + (date.getMonth() + 1)).slice(-2);
            const day = ('0' + date.getDate()).slice(-2);
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        // Get all date input fields
        const dateInputs = document.querySelectorAll('input[type="date"]');

        // Store original value and display formatted date
        dateInputs.forEach(input => {
            const originalValue = input.value;
            if (originalValue) {
                const formattedDate = formatDateToDDMMYYYY(originalValue);
                const displayField = document.createElement('input');
                displayField.type = 'text';
                displayField.value = formattedDate;
                displayField.className = input.className;
                displayField.readOnly = true;
                
                // Hide original date input but keep it for form submission
                input.type = 'hidden';
                input.parentNode.insertBefore(displayField, input.nextSibling);
            }
        });
    });
</script>
    

@endpush

@endsection
