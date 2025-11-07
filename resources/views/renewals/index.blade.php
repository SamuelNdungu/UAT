@extends('layouts.app')

@section('sidebar_menu')
    <div class="sb-sidenav-menu-heading">Renewals</div>
    <a class="nav-link {{ request()->routeIs('renewals.index') ? 'active' : '' }}" href="{{ route('renewals.index') }}">
        <div class="sb-nav-link-icon"><i class="fas fa-sync-alt"></i></div>
        Renewal Notices
    </a>
    {{-- Add other renewal-related links here if they exist --}}
@endsection

@section('content')
@php
    use Illuminate\Support\Carbon;
@endphp

<div class="container-fluid">
    <h1 class="mt-4">Renewals</h1> 
<div class="row mb-3">
        <!-- Card for 10 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-orange" style="border-radius: 5px;"> {{-- Removed card-clickable and onclick --}}
                <div class="inner text-center">
                    <h2>{{ $metrics['10Days'] }}</h2>
                    <p>Due in 10 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hourglass-half" aria-hidden="true"></i>
                </div>
                <a href="{{ route('renewals.index', array_merge(request()->query(), ['filter' => '10Days'])) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for 30 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-cyan" style="border-radius: 5px;"> {{-- Removed card-clickable and onclick --}}
                <div class="inner text-center">
                    <h2>{{ $metrics['30Days'] }}</h2>
                    <p>Due in 30 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
                </div>
                <a href="{{ route('renewals.index', array_merge(request()->query(), ['filter' => '30Days'])) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for 60 Days -->
        <div class="col-lg-3 col-md-6 col-sm-12">
            <div class="card-box bg-green" style="border-radius: 5px;"> {{-- Removed card-clickable and onclick --}}
                <div class="inner text-center">
                    <h2>{{ $metrics['60Days'] }}</h2>
                    <p>Due in 60 Days</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-alt" aria-hidden="true"></i>
                </div>
                <a href="{{ route('renewals.index', array_merge(request()->query(), ['filter' => '60Days'])) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Card for Expired -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card-box bg-red" style="border-radius: 5px;"> {{-- Removed card-clickable and onclick --}}
                <div class="inner text-center">
                    <h2>{{ $metrics['expiredPolicies'] }}</h2>
                    <p>Unrenewed / Expired</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                </div>
                <a href="{{ route('renewals.index', array_merge(request()->query(), ['filter' => 'expired'])) }}" class="card-box-footer">View More <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- Display session messages (e.g., error from controller) -->
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Validation Error:</strong> Please check your dates and selections.
        </div>
    @endif

    <!-- 1. Report Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <i class="fas fa-filter me-1"></i>
            Filter Options
        </div>
        <div class="card-body">
            <!-- The form will submit back to the same route via GET -->
            <form action="{{ route('renewals.index') }}" method="GET" class="row g-3 align-items-end">
                
                {{-- Date Filters --}}
                <div class="col-md-3 col-lg-2">
                    <label for="startDate" class="form-label">From</label>
                    <input type="date" class="form-control" id="startDate" name="start_date" 
                           value="{{ request('start_date', now()->startOfYear()->toDateString()) }}" required>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="endDate" class="form-label">To </label>
                    <input type="date" class="form-control" id="endDate" name="end_date" 
                           value="{{ request('end_date', now()->endOfYear()->toDateString()) }}" required>
                </div>

                {{-- Insurer Filter --}}
                <div class="col-md-6 col-lg-2">
                    <label for="insurer_id" class="form-label">Insurer</label>
                    <select name="insurer_id" id="insurer_id" class="form-select">
                        <option value="">All Insurers</option>
                        @foreach ($insurers as $id => $name)
                            <option value="{{ $id }}" {{ (string)$id === request('insurer_id') ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Agent Filter --}}
                <div class="col-md-6 col-lg-2">
                    <label for="agent_id" class="form-label">Agent</label>
                    <select name="agent_id" id="agent_id" class="form-select">
                        <option value="">All Agents</option>
                        @foreach ($agents as $id => $name)
                            <option value="{{ $id }}" {{ (string)$id === request('agent_id') ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Policy Type Filter --}}
                <div class="col-md-6 col-lg-2">
                    <label for="policy_type_id" class="form-label">Policy Type</label>
                    <select name="policy_type_id" id="policy_type_id" class="form-select">
                        <option value="">All Types</option>
                        @foreach ($policyTypes as $id => $name)
                            <option value="{{ $id }}" {{ (string)$id === request('policy_type_id') ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submission Button --}}
                <div class="col-md-6 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync-alt me-1"></i> Generate Report
                    </button>
                </div>

                <div class="col-12 d-flex justify-content-end mt-3">
                    <!-- Export Buttons (assuming 'policies' is not empty to enable) -->
                    <a href="{{ route('renewals.export.excel', request()->query()) }}" class="btn btn-outline-success me-2 {{ $policies->isEmpty() ? 'disabled' : '' }}">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                    {{-- Placeholder for PDF export --}}
                  <a href="{{ route('renewals.export.pdf', request()->query()) }}" class="btn btn-danger {{ $policies->isEmpty() ? 'disabled' : '' }}">
                    <i class="fas fa-file-pdf"></i> Export PDF
                  </a>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Summary Metrics -->
    
    

    <div class="card card-danger">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6 d-flex align-items-center">
                    <h4 class="card-title">Renewal List</h4>
                </div> 
            </div>
        </div>
        <div class="card-body">
            @if($policies->count())
            <div class="table-responsive" style="overflow-x: auto; overflow-y: auto;"> {{-- Removed max-width: 970px --}}
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
                            <td>{{ $policy->policyType->type_name ?? '-' }}</td> 
                            <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-m-Y') }}</td> 
                            <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</td>
                            <td>{{ $policy->insurer->name ?? '-' }}</td>
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

                                {{-- Enhanced notice status rendering --}}
                                @if($note)
                                    @php
                                        // support array or object shapes
                                        $status = strtolower($note['status'] ?? $note->status ?? '');
                                        $sentAtRaw = $note['sent_at'] ?? $note->sent_at ?? null;
                                        $channel = strtoupper($note['channel'] ?? $note['notice_type'] ?? 'EMAIL');
                                        $message = $note['message'] ?? $note->message ?? null;
                                    @endphp

                                    @if($status === 'sent')
                                        <div>
                                            <span class="text-success"><i class="fas fa-check-circle"></i> Sent</span>
                                            <small class="text-muted">({{ $channel }}) — {{ $sentAtRaw ? \Carbon\Carbon::parse($sentAtRaw)->format('d-m-Y H:i') : '-' }}</small>
                                        </div>
                                    @elseif($status === 'skipped')
                                        <div>
                                            <span class="text-warning"><i class="fas fa-forward"></i> Skipped</span>
                                            <small class="text-muted">— {{ $message ?: 'No recipient / skipped by system' }}</small>
                                        </div>
                                    @elseif($status === 'failed')
                                        <div>
                                            <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Failed</span>
                                            <small class="text-muted">— {{ \Illuminate\Support\Str::limit($message ?: 'Delivery error', 80) }}</small>
                                        </div>
                                    @else
                                        <div>
                                            <span class="text-secondary"><i class="fas fa-info-circle"></i> {{ ucfirst($status ?: 'unknown') }}</span>
                                            <small class="text-muted">({{ $channel }}) {{ $sentAtRaw ? '— ' . \Carbon\Carbon::parse($sentAtRaw)->format('d-m-Y H:i') : '' }}</small>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">Not Sent</span>
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
                            <th>Notice Status</th>
                            <th>Actions</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            {{-- Pagination Links --}}
            <div class="mt-3">
                {{ $policies->links() }}
            </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <p>No renewal policies found for the selected period and filters. Please adjust your dates or filter options.</p>
                </div>
            @endif
        </div>
    </div>
</div> 

{{-- Removed DataTables CSS and JS --}}
{{-- Removed custom card-clickable styles --}}

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this record?');
}
</script>

@endsection