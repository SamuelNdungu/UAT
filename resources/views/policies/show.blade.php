@extends('layouts.appPages')

@section('content')
    <div class="container">
    <h3 class="my-4 text-center">View Details</h3>

        <!-- Client Details Section -->
        <div class="group-heading bg-primary text-white p-2 mb-4">Client Details</div>
        <div class="row mb-4">
            <div class="col-md-4 form-group">
                <label>File No:</label>
                <input type="text" class="form-control" value="{{ $policy->fileno }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Customer Code</label>
                <input type="text" class="form-control" value="{{ $policy->customer_code }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Customer Name</label>
                <input type="text" class="form-control" value="{{ $policy->customer_name }}" readonly>
            </div>
        </div>

        <!-- Policy Details Section -->
        <div class="group-heading bg-primary text-white p-2 mb-4">Policy Details</div>
        <div class="row mb-4">
            <div class="col-md-4 form-group">
                <label>Policy No</label>
                <input type="text" class="form-control" value="{{ $policy->policy_no }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Policy Type</label>
                <input type="text" class="form-control" value="{{ $policy->policy_type_name }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Coverage</label>
                <input type="text" class="form-control" value="{{ $policy->coverage }}" readonly>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4 form-group">
                <label>Insurer</label>
                <input type="text" class="form-control" value="{{ $policy->insurer_name }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Start Date</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Days</label>
                <input type="text" class="form-control" value="{{ $policy->days }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>End Date</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}" readonly>
            </div>
        </div>

        <!-- Vehicle Details Section -->
        <div class="group-heading bg-primary text-white p-2 mb-4">Vehicle Details</div>
        <div class="row mb-4">
            <div class="col-md-4 form-group">
                <label>Make</label>
                <input type="text" class="form-control" value="{{ $policy->make }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Model</label>
                <input type="text" class="form-control" value="{{ $policy->model }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>Y.O.M</label>
                <input type="text" class="form-control" value="{{ $policy->yom }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>CC</label>
                <input type="text" class="form-control" value="{{ $policy->cc }}" readonly>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4 form-group">
                <label>Body Type</label>
                <input type="text" class="form-control" value="{{ $policy->body_type }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Chassis No</label>
                <input type="text" class="form-control" value="{{ $policy->chassisno }}" readonly>
            </div>
            <div class="col-md-4 form-group">
                <label>Engine No</label>
                <input type="text" class="form-control" value="{{ $policy->engine_no }}" readonly>
            </div>
        </div>

        <!-- Financial Details Section -->
        <div class="group-heading bg-primary text-white p-2 mb-4">Financial Details</div>
        <div class="row mb-4">
            <div class="col-md-3 form-group">
                <label>Sum Insured</label>
                <input type="text" class="form-control" value="{{ $policy->sum_insured }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>Rate</label>
                <input type="text" class="form-control" value="{{ $policy->rate }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Premium</label>
                <input type="text" class="form-control" value="{{ $policy->premium }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>C. Rate</label>
                <input type="text" class="form-control" value="{{ $policy->c_rate }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>Commission</label>
                <input type="text" class="form-control" value="{{ $policy->commission }}" readonly>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-2 form-group">
                <label>WHT</label>
                <input type="text" class="form-control" value="{{ $policy->wht }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>S. Duty</label>
                <input type="text" class="form-control" value="{{ $policy->s_duty }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>T. Levy</label>
                <input type="text" class="form-control" value="{{ $policy->t_levy }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>PCF Levy</label>
                <input type="text" class="form-control" value="{{ $policy->pcf_levy }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>Policy Charge</label>
                <input type="text" class="form-control" value="{{ $policy->policy_charge }}" readonly>
            </div>
            <div class="col-md-2 form-group">
                <label>Other Charges</label>
                <input type="text" class="form-control" value="{{ $policy->other_charges }}" readonly>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-3 form-group">
                <label>Gross Premium</label>
                <input type="text" class="form-control" value="{{ $policy->gross_premium }}" readonly>
            </div>
            <div class="col-md-3 form-group">
                <label>Net Premium</label>
                <input type="text" class="form-control" value="{{ $policy->net_premium }}" readonly>
            </div>
        </div>

        <!-- Cover Details Section -->
        <div class="group-heading bg-primary text-white p-2 mb-4">Cover Details</div>
        <div class="form-group mb-4">
            <label>Features</label>
            <textarea class="form-control" readonly>{{ $policy->cover_details }}</textarea>
        </div>
        <div class="form-group mb-4">
            <label>Documents</label>
            <table class="table table-bordered">
    <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>{{ $policy->document_description ?? 'No description provided' }}</td>
            <td> 
                @if ($policy->documents)
                    <a href="{{ asset('storage/uploads/' . $policy->documents) }}" download="{{ $policy->documents }}">
                        {{ basename($policy->documents) }}
                    </a>
                @else
                    <p>No document uploaded.</p>
                @endif
            </td>
        </tr>
    </tbody>
</table>

        </div>

         <!-- Action Buttons -->
         <div class="row">
            <div class="col-md-6">
                <a href="{{ route('policies.index') }}" class="btn btn-primary">Back to Policies</a>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-warning">Edit Policy</a>
            </div>
        </div>
    </div>
@endsection
