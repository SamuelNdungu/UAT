@extends('layouts.appPages')

@section('content')
<div class="container">
    <h3 class="my-4 text-center">Lead Details</h3>

    <!-- Lead Type and Basic Information -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Basic Information</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Lead Type</label>
            <input type="text" class="form-control" value="{{ $lead->lead_type }}" readonly>
        </div>
        @if($lead->lead_type === 'Corporate')
            <div class="col-md-4 form-group">
                <label>Corporate Name</label>
                <input type="text" class="form-control" value="{{ $lead->corporate_name }}" readonly>
            </div>
        @else
            <div class="col-md-4 form-group">
                <label>First Name</label>
                <input type="text" class="form-control" value="{{ $lead->first_name }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Last Name</label>
                <input type="text" class="form-control" value="{{ $lead->last_name }}" readonly>
            </div>
        @endif
        <div class="col-md-4 form-group">
            <label>Email</label>
            <input type="text" class="form-control" value="{{ $lead->email }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Mobile</label>
            <input type="text" class="form-control" value="{{ $lead->mobile }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Lead Source</label>
            <input type="text" class="form-control" value="{{ $lead->lead_source ?: 'N/A' }}" readonly>
        </div>
    </div>

    <!-- Policy Details -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Policy Details</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Policy Type</label>
            <input type="text" class="form-control" value="{{ $lead->policy_type }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Estimated Premium</label>
            <input type="text" class="form-control" value="{{ number_format($lead->estimated_premium, 2) }}" readonly>
        </div>
        @if($lead->upload)
            <div class="col-md-4 form-group">
                <label>Uploaded Documents</label>
                <div class="form-control" style="height: auto;">
                    @foreach(json_decode($lead->upload) as $document)
                        <div><a href="{{ asset('storage/' . $document) }}" target="_blank">View Document</a></div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Deal Information -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Deal Information</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Deal Size</label>
            <input type="text" class="form-control" value="{{ number_format($lead->deal_size, 2) }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Probability</label>
            <input type="text" class="form-control" value="{{ $lead->probability }}%" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Weighted Revenue Forecast</label>
            <input type="text" class="form-control" value="{{ number_format($lead->weighted_revenue_forecast, 2) }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Deal Stage</label>
            <input type="text" class="form-control" value="{{ $lead->deal_stage }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Deal Status</label>
            <input type="text" class="form-control" value="{{ $lead->deal_status }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Date Initiated</label>
            <input type="text" class="form-control" value="{{ $lead->date_initiated ? date('M d, Y', strtotime($lead->date_initiated)) : 'N/A' }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Closing Date</label>
            <input type="text" class="form-control" value="{{ $lead->closing_date ? date('M d, Y', strtotime($lead->closing_date)) : 'N/A' }}" readonly>
        </div>
    </div>

    <!-- Follow-up Information -->
    <div class="group-heading bg-primary text-white p-2 mb-4">Follow-up Information</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Follow-up Date</label>
            <input type="text" class="form-control" value="{{ $lead->follow_up_date ? date('M d, Y', strtotime($lead->follow_up_date)) : 'N/A' }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Next Action</label>
            <input type="text" class="form-control" value="{{ $lead->next_action ?: 'N/A' }}" readonly>
        </div>
        <div class="col-md-12 form-group">
            <label>Notes</label>
            <textarea class="form-control" readonly>{{ $lead->notes ?: 'No notes available' }}</textarea>
        </div>
    </div>

    <!-- System Information -->
    <div class="group-heading bg-primary text-white p-2 mb-4">System Information</div>
    <div class="row mb-4">
        <div class="col-md-4 form-group">
            <label>Created At</label>
            <input type="text" class="form-control" value="{{ date('M d, Y H:i:s', strtotime($lead->created_at)) }}" readonly>
        </div>
        <div class="col-md-4 form-group">
            <label>Last Updated</label>
            <input type="text" class="form-control" value="{{ date('M d, Y H:i:s', strtotime($lead->updated_at)) }}" readonly>
        </div>
    </div>

    <!-- Action Buttons -->
    @php
        $actionButtons = [
            ['url' => route('leads.index'), 'label' => 'Go Back', 'icon' => 'fas fa-arrow-left', 'variant' => 'primary', 'attrs' => ['title' => 'Back to list', 'aria-label' => 'Back to list']],
            ['url' => route('leads.edit', $lead->id), 'label' => 'Edit Lead', 'icon' => 'fas fa-edit', 'variant' => 'warning', 'attrs' => ['title' => 'Edit lead', 'aria-label' => 'Edit lead']],
        ];
    @endphp

    <div class="row">
        <div class="col-12">
            @include('shared.action-buttons', ['buttons' => $actionButtons])
        </div>
    </div>
</div>
@endsection