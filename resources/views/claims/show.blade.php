@extends('layouts.appPages')

@section('content')
<style>
    .form-label.required::after {
        content: " *";
        color: red;
    }

    .group-heading {
        margin-top: 20px;
        padding-bottom: 5px;
        border-bottom: 2px solid #007bff;
        color: #007bff;
        font-size: 1.25rem;
    }

    .event-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: bold;
        color: #333;
    }

    .form-control-plaintext {
        background-color: #f8f9fa;
        padding: 8px 10px;
        border-radius: 4px;
        font-size: 1rem;
        color: #495057;
        border: 1px solid #ced4da;
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Claim Details</h4>
        </div>
        <div class="card-body ">
            <!-- Policy Details Section -->
            <div class="group-heading">Policy Details</div>
            <div class="row mt-3">
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Claim No</label>
                        <p class="form-control-plaintext">{{ $claim->claim_number }}</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Cust Code</label>
                        <p class="form-control-plaintext">{{ $claim->customer_code }}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <p class="form-control-plaintext">{{ $claim->policy->customer_name }}</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Policy Type</label>
                        <p class="form-control-plaintext">
                            {{ $claim->policy->policyType->type_name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">File No</label>
                        <p class="form-control-plaintext">{{ $claim->fileno }}</p>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Reg No</label>
                        <p class="form-control-plaintext">{{ $claim->policy->reg_no }}</p>
                    </div>
                </div>
            </div>

            <!-- Claim Details Section -->
            <div class="group-heading">Claims Details</div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label ">Reported Date</label>
                        <p class="form-control-plaintext">{{ \Illuminate\Support\Carbon::parse($claim->reported_date)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label ">Type of Loss</label>
                        <p class="form-control-plaintext">{{ $claim->type_of_loss }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label ">Loss Date</label>
                        <p class="form-control-plaintext">{{ \Illuminate\Support\Carbon::parse($claim->loss_date)->format('Y-m-d') }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Follow-up Date</label>
                        <p class="form-control-plaintext">{{ $claim->followup_date ? \Illuminate\Support\Carbon::parse($claim->followup_date)->format('Y-m-d') : 'N/A' }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label ">Claimant Name</label>
                        <p class="form-control-plaintext">{{ $claim->claimant_name }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label ">Amount Claimed</label>
                        <p class="form-control-plaintext">{{ number_format($claim->amount_claimed, 2) }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <p class="form-control-plaintext">{{ number_format($claim->amount_paid, 2) }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label ">Status</label>
                        <p class="form-control-plaintext">{{ $claim->status }}</p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label ">Loss Details</label>
                <p class="form-control-plaintext">{{ $claim->loss_details }}</p>
            </div>
            
           <!-- Uploaded Files Section -->
            <div class="group-heading">Uploaded Files</div>
            <div class="row"> 
                                
                                @if($claim->upload_file)
                                    <a href="{{ asset('storage/' . $claim->upload_file) }}" target="_blank">View Document</a>
                                @else
                                    N/A
                                @endif
 
            </div>

 
            <!-- Event Section -->
            <div class="group-heading">Events</div>
            <div id="events">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claim->events as $event)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($event->event_date)->format('Y-m-d') }}</td>
                            <td>{{ $event->event_type }}</td>
                            <td>{{ $event->description }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('claims.index') }}" class="btn btn-primary mt-3">Back to Claims</a>
        </div>
    </div>
</div>
@endsection
