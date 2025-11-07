@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <!-- Debtors Aging Cards -->
        <div class="row mb-3">
            <!-- Total Policies Card -->
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
            <!-- Motor Policies Card -->
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
            <!-- Non Motor Policies Card -->
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
            <!-- Claims Card -->
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-red card-clickable" style="border-radius: 5px;" onclick="window.location='{{ route('policies.index', ['filter' => 'claims']) }}'">
                    <div class="inner">
                        <h3>0</h3>
                        <p>Claims</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-danger mt-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Policies</h4>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto; max-width: 100%;">
                <div class="col-md-6 text-md-end text-start mb-2">
                    <a href="{{ route('policies.create') }}" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-plus" style="font-size: 0.65rem;"></i> Add
                    </a>
                    <a href="{{ route('export.pdf') }}" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-pdf" style="font-size: 0.65rem;"></i> Export PDF
                    </a>
                    <a href="{{ route('export.excel') }}" class="btn btn-success" style="padding: 5px 10px; font-size: 0.9rem;">
                        <i class="fas fa-file-excel" style="font-size: 0.65rem;"></i> Export Excel
                    </a>
                </div>
                <table id="myTable" class="table table-striped rounded-top" style="width: auto; font-size: 12px;">
                    <thead style="white-space: nowrap;">
                        <tr>
                            <th>Agent</th>
                            <th>File No.</th>
                            <th>Entry Date</th>
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
                             <!-- Added Agent column -->
                            <th style="position: sticky; right: 0; background: #fff; z-index: 2; box-shadow: -2px 0 5px -2px #ccc;">Actions</th>
                        </tr>
                    </thead>
                    <tbody style="white-space: nowrap;">
                        @foreach($policies as $policy)
                        <tr>
                            <td>
                                {{-- Ensure agent relationship is loaded and show name/code --}}
                                @if(isset($policy->agent) && $policy->agent)
                                    {{ $policy->agent->name }} ({{ $policy->agent->agent_code }})
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $policy->fileno }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->updated_at)->format('d-m-Y') }}</td>
                            <td>{{ $policy->customer_code }}</td>
                            <td>{{ $policy->customer_name }}</td>
                            <td>{{ $policy->policy_type_name }}</td>
                            <td>{{ $policy->coverage }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-m-Y') }}</td>
                            <td>{{ $policy->days }}</td>
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</td>
                            <td>{{ $policy->insurer_name }}</td>
                            <td>
                                {{-- Policy Number --}}
                                {{ $policy->policy_no }}
                                {{-- Cancelled badge --}}
                                @if(method_exists($policy, 'isCancelled') && $policy->isCancelled())
                                    <span class="badge bg-danger ms-2" title="Policy canceled" style="background-color:#dc3545;color:#fff;padding:.25em .4em;border-radius:.25rem;font-weight:700;font-size:75%;">Canceled</span>
                                @endif
                                {{-- Renewal badge --}}
                                @php
                                    $renewalRecord = $policy->renewalsAsRenewed()->with('originalPolicy')->first();
                                @endphp
                                @if($renewalRecord)
                                    <span class="badge bg-info" title="Renewal of policy #{{ $renewalRecord->originalPolicy->policy_no ?? $renewalRecord->original_policy_id }}">
                                        Renewal
                                    </span>
                                @elseif($policy->renewalsAsOriginal()->exists())
                                    <span class="badge bg-success" title="Has renewals">
                                        Renewed
                                    </span>
                                @endif
                            </td>
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
                            <td>{{ number_format($policy->net_premium ?? 0, 2) }}</td>
                            <td>{{ number_format($policy->paid_amount ?? 0, 2) }}</td>
                            <td>{{ number_format($policy->balance ?? 0, 2) }}</td>

                            <td class="actions-cell" style="position: sticky; right: 0; background: #fff; z-index: 2; box-shadow: -2px 0 5px -2px #ccc;">
                                {{-- Quick status badge for UX: Canceled / Renewal / Renewed --}}
                                @php
                                    $statusBadgeHtml = null;
                                    try {
                                        if (method_exists($policy, 'isCancelled') && $policy->isCancelled()) {
                                            $statusBadgeHtml = '<span class="badge bg-danger" title="Policy canceled" style="background-color:#dc3545;color:#fff;padding:.25em .45em;border-radius:.25rem;font-weight:700;font-size:75%;display:inline-block;margin-bottom:6px;">Canceled</span>';
                                        } else {
                                            $__renew = null;
                                            try { $__renew = $policy->renewalsAsRenewed()->with('originalPolicy')->first(); } catch (\Throwable $__e) { $__renew = null; }
                                            if ($__renew) {
                                                $statusBadgeHtml = '<span class="badge bg-info" title="Renewal" style="background-color:#17a2b8;color:#fff;padding:.25em .45em;border-radius:.25rem;font-weight:700;font-size:75%;display:inline-block;margin-bottom:6px;">Renewal</span>';
                                            } elseif (method_exists($policy, 'renewalsAsOriginal') && $policy->renewalsAsOriginal()->exists()) {
                                                $statusBadgeHtml = '<span class="badge bg-success" title="Has renewals" style="background-color:#28a745;color:#fff;padding:.25em .45em;border-radius:.25rem;font-weight:700;font-size:75%;display:inline-block;margin-bottom:6px;">Renewed</span>';
                                            }
                                        }
                                    } catch (\Throwable $e) {
                                        $statusBadgeHtml = null;
                                    }
                                @endphp

                                @if($statusBadgeHtml)
                                    {!! $statusBadgeHtml !!}
                                @endif

                                {{-- The 'View' button is always available --}}
                                <a href="{{ route('policies.show', $policy->id) }}" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye" style="font-size: 0.7em;"></i></a>

                                {{-- If the policy is cancelled or has already been renewed, only show the view button --}}
                                @if (method_exists($policy, 'isCancelled') && $policy->isCancelled() || (method_exists($policy, 'isRenewed') && $policy->isRenewed()))
                                    {{-- nothing else --}}
                                @else
                                    {{-- Endorsement button (nested resource) --}}
                                    <a href="{{ route('policies.endorsements.create', $policy->id) }}" class="btn btn-sm btn-primary" title="Create Endorsement">
                                        <i class="fas fa-plus" style="font-size: 0.7em;"></i>
                                    </a>

                                    {{-- Edit button --}}
                                    <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit" style="font-size: 0.7em;"></i></a>

                                    {{-- Renew (icon only) --}}
                                    <a href="{{ route('renewals.renew', $policy->id) }}" class="btn btn-sm btn-success" title="Renew Policy"><i class="fas fa-redo" style="font-size: 0.7em;"></i></a>

                                    <form action="{{ route('policies.destroy', $policy->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirmDelete()" title="Delete"><i class="fas fa-trash" style="font-size: 0.7em;"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                             <th>Agent</th> <!-- Added Agent column -->
                            <th>File No.</th>
                            <th>Entry Date</th>
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
function confirmDelete() {
    return confirm('Are you sure you want to delete this record?');
}

</script>

<style>
.card-clickable {
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
    height: 120px;
    overflow: hidden;
}

.card-clickable:hover {
    transform: scale(1.03);
}

.card-box {
    position: relative;
    padding: 15px;
}

.card-box .inner {
    padding: 10px 0;
}

.card-box .icon {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    opacity: 0.3;
}

.card-box-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 5px 10px;
    background: rgba(0,0,0,0.1);
    text-align: center;
    text-decoration: none;
    color: inherit;
}

/* Card background colors */
.bg-cyan { background: linear-gradient(45deg, #17a2b8, #138496); color: white; }
.bg-green { background: linear-gradient(45deg, #28a745, #1e7e34); color: white; }
.bg-orange { background: linear-gradient(45deg, #fd7e14, #e55a00); color: white; }
.bg-red { background: linear-gradient(45deg, #dc3545, #a71e2a); color: white; }

/* Table styles */
.table thead th {
    vertical-align: middle;
    text-align: center;
}

.btn-group-vertical .btn {
    margin-bottom: 2px;
}

/* Responsive behavior */
@media (max-width: 768px) {
    .card-box {
        margin-bottom: 15px;
    }
    
    .table-responsive {
        font-size: 11px;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>



<script>function sendEmail(customerName, balance, customerCode) {
    if (confirm(`Send email to ${customerName} showing balance: KES ${balance}?`)) {
        // Make an AJAX request to send the email
        fetch(`/send-email/${customerCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ balance: balance })
        }).then(response => {
            if (response.ok) {
                alert('Email sent successfully!');
            } else {
                alert('Failed to send email.');
            }
        });
    }
}

function sendSMS(customerName, balance, customerCode) {
    if (confirm(`Send SMS to ${customerName} showing balance: KES ${balance}?`)) {
        // Make an AJAX request to send the SMS
        fetch(`/send-sms/${customerCode}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ balance: balance })
        }).then(response => {
            if (response.ok) {
                alert('SMS sent successfully!');
            } else {
                alert('Failed to send SMS.');
            }
        });
    }
}

</script>

@endsection
