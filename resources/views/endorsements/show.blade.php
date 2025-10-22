@extends('layouts.appPages')

@section('content')
<div class="container mt-4" style="max-width: 700px;">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Endorsement Details</h4>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-4">Policy File No</dt>
                <dd class="col-sm-8">{{ $policy->fileno }}</dd>

                <dt class="col-sm-4">Policy No</dt>
                <dd class="col-sm-8">{{ $policy->policy_no }}</dd>

                <dt class="col-sm-4">Endorsement Type</dt>
                <dd class="col-sm-8">{{ $endorsement->endorsement_type }}</dd>

                <dt class="col-sm-4">Effective Date</dt>
                <dd class="col-sm-8">{{ $endorsement->effective_date }}</dd>

                <dt class="col-sm-4">Premium Impact</dt>
                <dd class="col-sm-8">{{ number_format($endorsement->premium_impact, 2) }}</dd>

                <dt class="col-sm-4">Description</dt>
                <dd class="col-sm-8">{{ $endorsement->description }}</dd>

                <dt class="col-sm-4">Document</dt>
                <dd class="col-sm-8">
                    @if($endorsement->document_path)
                        <a href="{{ asset('storage/' . $endorsement->document_path) }}" target="_blank">View PDF</a>
                    @else
                        N/A
                    @endif
                </dd>
            </dl>

            <hr>
            <h5 class="mt-4 mb-3">Financial Details (KES)</h5>
            <dl class="row">
                <dt class="col-sm-5">Sum Insured</dt>
                <dd class="col-sm-7">{{ number_format($policy->sum_insured ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Rate (%)</dt>
                <dd class="col-sm-7">{{ number_format($policy->rate ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Premium</dt>
                <dd class="col-sm-7">{{ number_format($policy->premium ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Commission Rate (%)</dt>
                <dd class="col-sm-7">{{ number_format($policy->c_rate ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Commission</dt>
                <dd class="col-sm-7">{{ number_format($policy->commission ?? 0, 2) }}</dd>
                <dt class="col-sm-5">WHT</dt>
                <dd class="col-sm-7">{{ number_format($policy->wht ?? 0, 2) }}</dd>
                <dt class="col-sm-5">S. Duty</dt>
                <dd class="col-sm-7">{{ number_format($policy->s_duty ?? 0, 2) }}</dd>
                <dt class="col-sm-5">T. Levy</dt>
                <dd class="col-sm-7">{{ number_format($policy->t_levy ?? 0, 2) }}</dd>
                <dt class="col-sm-5">PCF Levy</dt>
                <dd class="col-sm-7">{{ number_format($policy->pcf_levy ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Policy Charge</dt>
                <dd class="col-sm-7">{{ number_format($policy->policy_charge ?? 0, 2) }}</dd>
                <dt class="col-sm-5">AA Charges</dt>
                <dd class="col-sm-7">{{ number_format($policy->aa_charges ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Other Charges</dt>
                <dd class="col-sm-7">{{ number_format($policy->other_charges ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Gross Premium</dt>
                <dd class="col-sm-7">{{ number_format($policy->gross_premium ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Net Premium</dt>
                <dd class="col-sm-7">{{ number_format($policy->net_premium ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Excess</dt>
                <dd class="col-sm-7">{{ number_format($policy->excess ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Courtesy Car</dt>
                <dd class="col-sm-7">{{ number_format($policy->courtesy_car ?? 0, 2) }}</dd>
                <dt class="col-sm-5">PPL</dt>
                <dd class="col-sm-7">{{ number_format($policy->ppl ?? 0, 2) }}</dd>
                <dt class="col-sm-5">Road Rescue</dt>
                <dd class="col-sm-7">{{ number_format($policy->road_rescue ?? 0, 2) }}</dd>
            </dl>

            <a href="{{ route('policies.endorsements.index', $policy->id) }}" class="btn btn-secondary">Back to Endorsements</a>
        </div>
    </div>
</div>
@endsection
