@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row mb-3">
        <!-- Card for 10 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-orange card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('renewals.index', ['filter' => '10Days']) }}'">
                <div class="inner text-center">
                    <h2>{{ $metrics['10Days'] }}</h2>
                    <p>10 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for 30 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-cyan card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('renewals.index', ['filter' => '30Days']) }}'">
                <div class="inner text-center">
                    <h2>{{ $metrics['30Days'] }}</h2>
                    <p>30 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for 60 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-green card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('renewals.index', ['filter' => '60Days']) }}'">
                <div class="inner text-center">
                    <h2>{{ $metrics['60Days'] }}</h2>
                    <p>60 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-alt" aria-hidden="true"></i>
                </div>
                <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for Expired -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-red card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('renewals.index', ['filter' => 'expired']) }}'">
                <div class="inner text-center">
                    <h2>{{ $metrics['expiredPolicies'] }}</h2>
                    <p>Unrenewed</p>
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
                    <h4 class="card-title">Renewal List</h4>
                </div> 
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 970px;">
                <table id="myTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>File No.</th>
                            <th>Entry Date</th>
                            <th>Cust Code</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Phone</th>
                            <th>Policy Type</th> 
                            <th>Start Date</th> 
                            <th>End Date</th>
                            <th>Insurer</th>
                            <th>Policy No</th>
                            <th>Reg.No</th>
                            <th>Make</th>
                            <th>Model</th> 
                            <th>Sum Insured</th>                           
                            <th>Gross Premium</th> 
                            <th>Status</th>
                            <th>Notice Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @foreach($policies as $policy)
                        <tr>
                            <td>{{ $policy->fileno }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->created_at)->format('d-m-Y') }}</td> 
                            <td>{{ $policy->customer_code }}</td>
                            <td>{{ $policy->customer_name }}</td>
                            <td>{{ $policy->mobile ?? $policy->mobile_number ?? '-' }}</td>
                            <td>{{ $policy->phone ?? $policy->telephone ?? '-' }}</td>
                            <td>{{ $policy->policy_type_name }}</td> 
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-m-Y') }}</td> 
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</td>
                            <td>{{ $policy->insurer_name }}</td>
                            <td>
                                {{ $policy->policy_no }}
                                @if(method_exists($policy, 'isCancelled') && $policy->isCancelled())
                                    <span class="badge bg-danger ms-2" title="Policy canceled" style="background-color:#dc3545;color:#fff;padding:.25em .4em;border-radius:.25rem;font-weight:700;font-size:75%;">Canceled</span>
                                @endif
                                @php
                                    $renewalRecord = $policy->renewalsAsRenewed()->with('originalPolicy')->first();
                                @endphp
                                @if($renewalRecord)
                                    <span class="badge bg-info ms-2" title="Renewal of policy #{{ $renewalRecord->originalPolicy->policy_no ?? $renewalRecord->original_policy_id }}" style="background-color:#17a2b8;color:#fff;padding:.25em .4em;border-radius:.25rem;font-weight:700;font-size:75%;">Renewal</span>
                                @elseif($policy->renewalsAsOriginal()->exists())
                                    <span class="badge bg-success ms-2" title="Has renewals" style="background-color:#28a745;color:#fff;padding:.25em .4em;border-radius:.25rem;font-weight:700;font-size:75%;">Renewed</span>
                                @endif
                            </td>
                            <td>{{ $policy->reg_no }}</td>
                            <td>{{ $policy->make }}</td>
                            <td>{{ $policy->model }}</td> 
                            <td>{{ $policy->sum_insured }}</td>                         
                            <td>{{ number_format($policy->gross_premium, 2) }}</td> 
                            <td>{{ $policy->status }}</td>
                            <td>
                                @php
                                    $note = null;
                                    if (!empty($notices) && isset($notices[$policy->fileno])) {
                                        $note = $notices[$policy->fileno];
                                    }
                                @endphp
                                @if($note)
                                    Sent ({{ strtoupper($note['channel'] ?? 'email') }}) — {{ \Carbon\Carbon::parse($note['sent_at'])->format('d-m-Y H:i') }}
                                @else
                                    Not Sent
                                @endif
                            </td>
                            <td style="white-space: nowrap; position: sticky; right: 0; background-color: white; z-index: 100; padding: 2px; border-left: 1px solid #ddd;">
    {{-- If cancelled or already renewed, only show view --}}
    @if($policy->isCancelled() || $policy->isRenewed())
        <a href="{{ route('policies.show', $policy->id) }}" aria-label="View" title="View"
           style="color: #17a2b8; margin-right: 8px; font-size: 0.9rem;">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </a>
    @else
        {{-- View (icon only) --}}
        <a href="{{ route('policies.show', $policy->id) }}" aria-label="View" title="View"
           style="color: #17a2b8; margin-right: 8px; font-size: 0.9rem;">
            <i class="fas fa-eye" aria-hidden="true"></i>
        </a>

        {{-- Renew (icon only) --}}
        <a href="{{ route('renewals.renew', $policy->id) }}" aria-label="Renew" title="Renew"
           style="color: #ffc107; margin-right: 8px; font-size: 0.9rem;">
            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
        </a>
    @endif

	{{-- Send renewal email (icon only) --}}
    @php $isCancelled = $policy->isCancelled(); @endphp
    @if($isCancelled)
        <span title="Policy cancelled" style="color: #9aa0a6; margin-right: 8px; font-size: 0.9rem; cursor: not-allowed;">
            <i class="fas fa-envelope" aria-hidden="true"></i>
        </span>
    @else
        <a href="{{ route('customers.sendRenewalEmail', $policy->id) }}"
           title="Send renewal email"
           onclick="return confirm('Send renewal email to {{ addslashes($policy->customer_name) }}?');"
           style="color: #007bff; margin-right: 8px; font-size: 0.9rem;">
            <i class="fas fa-envelope" aria-hidden="true"></i>
        </a>
    @endif

	{{-- Send renewal SMS (icon only) --}}
    @if($isCancelled)
        <span title="Policy cancelled" style="color: #9aa0a6; font-size: 0.9rem; cursor: not-allowed;">
            <i class="fas fa-sms" aria-hidden="true"></i>
        </span>
    @else
        <a href="{{ route('customers.sendRenewalSms', $policy->id) }}"
           title="Send renewal SMS"
           onclick="return confirm('Send renewal SMS to {{ addslashes($policy->customer_name) }}?');"
           style="color: #28a745; font-size: 0.9rem;">
            <i class="fas fa-sms" aria-hidden="true"></i>
        </a>
    @endif
</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>File No.</th>
                            <th>Entry Date</th>
                            <th>Cust Code</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Phone</th>
                            <th>Policy Type</th> 
                            <th>Start Date</th> 
                            <th>End Date</th>
                            <th>Insurer</th>
                            <th>Policy No</th>
                            <th>Reg.No</th>
                            <th>Make</th>
                            <th>Model</th> 
                            <th>Sum Insured</th>                           
                            <th>Gross Premium</th>
                            <th>Status</th>
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
