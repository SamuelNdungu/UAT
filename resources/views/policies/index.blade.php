@extends('layouts.app')

@section('content')

<div class="container"> 
<div class="row mb-3">
<div class="col-lg-3 col-sm-6">
    <div class="card-box bg-cyan card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('policies.index', ['filter' => 'total']) }}'">
        <div class="inner">
            <h3>{{ $metrics['totalPolicies'] }}</h3>
            <p>Total Policies</p>
        </div>
        <div class="icon">
            <i class="fa fa-chart-line" aria-hidden="true"></i>
        </div>
        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
    </div>
</div>

<div class="col-lg-3 col-sm-6">
    <div class="card-box bg-green card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('policies.index', ['filter' => 'motor']) }}'">
        <div class="inner">
            <h3>{{ $metrics['motorPolicies'] }}</h3>
            <p>Motor Policies</p>
        </div>
        <div class="icon">
            <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
        </div>
        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
    </div>
</div>

<div class="col-lg-3 col-sm-6">
    <div class="card-box bg-orange card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('policies.index', ['filter' => 'nonMotor']) }}'">
        <div class="inner">
            <h3>{{ $metrics['nonMotorPolicies'] }}</h3>
            <p>Non Motor Policies</p>
        </div>
        <div class="icon">
            <i class="fa fa-file-alt" aria-hidden="true"></i>
        </div>
        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
    </div>
</div>

<div class="col-lg-3 col-sm-6">
    <div class="card-box bg-red card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('policies.index', ['filter' => 'claims']) }}'">
        <div class="inner">
            <h3>{{ $metrics['policiesWithClaims'] }}</h3>
            <p>Claims</p>
        </div>
        <div class="icon">
            <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        </div>
        <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
    </div>
</div>

    </div>



    <div class="card card-danger">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Policies List</h4>
                </div>
                <div class="col-md-6 text-md-end text-start">
                    <a href="{{ route('policies.create') }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 4px 8px;">
                        <i class="fas fa-plus" style="font-size: 0.75rem;"></i> Add
                    </a>
                </div>

            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                <table id="myTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>File No.</th>
                            <th>Buss Date</th>
                            <th>Cust Code</th>
                            <th>Name</th>
                            <th>Policy Type</th>
                            <th>Coverage</th>
                            <th>Start Date</th>
                            <th>Days</th>
                            <th>End Date</th>
                            <th>Insurer</th>
                            <th>Policy No</th>
                            <th>Reg.No</th>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Insured</th>
                            <th>Sum Insured</th>
                            <th>P. Rate (%)</th>
                            <th>Premium</th>
                            <th>C. Rate (%)</th>
                            <th>Comm.</th>
                            <th>WHT</th>
                            <th>Stamp Duty</th>
                            <th>T.Levy</th>
                            <th>PCF Levy</th>
                            <th>Policy Charge</th>
                            <th>AA Charges</th>
                            <th>Other Charges</th>
                            <th>Gross Premium</th>
                            <th>Net Premium</th>
                            <th>Paid Amount</th>
                            <th>Balance</th>                            
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @foreach($policies as $policy)
                        <tr>
                            <td>{{ $policy->fileno }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->created_at)->format('Y-m-d') }}</td> 
                            <td>{{ $policy->customer_code }}</td>
                            <td>{{ $policy->customer_name }}</td>
                            <td>{{ $policy->policy_type_name }}</td>
                            <td>{{ $policy->coverage }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                            <td>{{ $policy->days }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                            <td>{{ $policy->insurer_name }}</td>
                            <td>{{ $policy->policy_no }}</td>
                            <td>{{ $policy->reg_no }}</td>
                            <td>{{ $policy->make }}</td>
                            <td>{{ $policy->model }}</td>
                            <td>{{ $policy->insured }}</td>
                            <td>{{ $policy->sum_insured }}</td>
                            <td>{{ $policy->rate }}</td>
                            <td>{{ number_format($policy->premium, 2) }}</td>
                            <td>{{ $policy->c_rate }}</td>
                            <td>{{ number_format($policy->commission, 2) }}</td>
                            <td>{{ number_format($policy->wht, 2) }}</td>
                            <td>{{ number_format($policy->s_duty, 2) }}</td>
                            <td>{{ number_format($policy->t_levy, 2) }}</td>
                            <td>{{ number_format($policy->pcf_levy, 2) }}</td>
                            <td>{{ number_format($policy->policy_charge, 2) }}</td>
                            <td>{{ number_format($policy->aa_charges, 2) }}</td>
                            <td>{{ number_format($policy->other_charges, 2) }}</td>
                            <td>{{ number_format($policy->gross_premium, 2) }}</td>
                            <td>{{ number_format($policy->net_premium, 2) }}</td>
                            <td>{{ number_format($policy->paid_amount, 2) }}</td>
                            <td>{{ number_format($policy->balance, 2) }}</td>
                            
                            
                            
                            
                            <td>
                                @if($policy->documents)
                                    @php
                                        $filePath = public_path('storage/uploads/' . basename($policy->documents));
                                    @endphp
                                    @if(file_exists($filePath))
                                        @php
                                            $fileName = basename($policy->documents);
                                        @endphp
                                        <a href="{{ asset('storage/uploads/' . $fileName) }}" download>{{ $fileName }}</a>
                                    @else
                                        File not found
                                    @endif
                                @endif
                                <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 2px; border-left: 1px solid #ddd;">
    <a href="{{ route('policies.show', $policy->id) }}" class="btn btn-info btn-xs" aria-label="View" title="View" style="font-size: 0.5rem; padding: 2px 5px;">
        <i class="fas fa-eye" aria-hidden="true" style="font-size: 0.5rem;"></i>
    </a>
    <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-warning btn-xs" aria-label="Edit" title="Edit" style="font-size: 0.5rem; padding: 2px 5px;">
        <i class="fas fa-pencil-alt" aria-hidden="true" style="font-size: 0.5rem;"></i>
    </a>
    <form action="{{ route('policies.destroy', $policy->id) }}" method="POST" style="display:inline;" onsubmit="return confirmDelete()">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-xs" aria-label="Delete" title="Delete" style="font-size: 0.5rem; padding: 2px 5px;">
            <i class="fas fa-trash" aria-hidden="true" style="font-size: 0.5rem;"></i>
        </button>
    </form>
</td>

                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>File No.</th>
                            <th>Buss Date</th>
                            <th>Cust Code</th>
                            <th>Name</th>
                            <th>Policy Type</th>
                            <th>Coverage</th>
                            <th>Start Date</th>
                            <th>Days</th>
                            <th>End Date</th>
                            <th>Insurer</th>
                            <th>Policy No</th>
                            <th>Reg.No</th>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Insured</th>
                            <th>Sum Insured</th>
                            <th>P. Rate (%)</th>
                            <th>Premium</th>
                            <th>C. Rate (%)</th>
                            <th>Comm.</th>
                            <th>WHT</th>
                            <th>Stamp Duty</th>
                            <th>T.Levy</th>
                            <th>PCF Levy</th>
                            <th>Policy Charge</th>
                            <th>AA Charges</th>
                            <th>Other Charges</th>
                            <th>Gross Premium</th>
                            <th>Net Premium</th>
                            <th>Paid Amount</th>
                            <th>Balance</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div> 

<link rel="stylesheet" href="https://cdn.datatables.net/2.1.4/css/dataTables.dataTables.css" />
<script src="https://cdn.datatables.net/2.1.4/js/dataTables.js"></script>

<script>
$(document).ready(function() {
    // Add hover effect for cards
    $('.card-clickable').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.05)',
                'transition': 'transform 0.3s ease-in-out'
            });
        },
        function() {
            $(this).css('transform', 'scale(1)');
        }
    );
});

function confirmDelete() {
    return confirm('Are you sure you want to delete this record?');
}
</script>

<style>

.card-clickable {
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
}

.card-clickable:hover {
    transform: scale(1.05);
}

</style>


@endsection
