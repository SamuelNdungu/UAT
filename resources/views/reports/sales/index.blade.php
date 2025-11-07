@extends('layouts.appPages')

@section('sidebar_menu')
    <div class="sb-sidenav-menu-heading">Reports</div>
    <a class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}" href="{{ route('reports.sales') }}">
        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
        Sales & Production
    </a>
    {{-- Add other report links here if they exist, following a similar pattern --}}
    {{-- Example for another report type:
    <a class="nav-link {{ request()->routeIs('reports.commissions') ? 'active' : '' }}" href="{{ route('reports.commissions') }}">
        <div class="sb-nav-link-icon"><i class="fas fa-hand-holding-usd"></i></div>
        Commissions
    </a>
    --}}
@endsection

@section('content')
@php
    use Illuminate\Support\Carbon;
@endphp

<div class="container-fluid">
    <h1 class="mt-4">Sales & Production Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Reports</li>
        <li class="breadcrumb-item active">Sales & Production</li>
    </ol>

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
            <form action="{{ route('reports.sales') }}" method="GET" class="row g-3 align-items-end">
                
                {{-- Date Filters --}}
                <div class="col-md-3 col-lg-2">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="startDate" name="start_date" 
                           value="{{ request('start_date', now()->startOfYear()->toDateString()) }}" required>
                </div>
                <div class="col-md-3 col-lg-2">
                    <label for="endDate" class="form-label">End Date</label>
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
                    <a href="{{ route('reports.sales.export', request()->query()) }}" class="btn btn-outline-success me-2 {{ $policies->isEmpty() ? 'disabled' : '' }}">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                    {{-- Placeholder for PDF export --}}
                  <a href="{{ route('reports.sales.export.pdf', request()->query()) }}" class="btn btn-danger">
    <i class="fas fa-file-pdf"></i> Export PDF
</a>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Summary Metrics -->
    <h2 class="h5 mb-3">Report Summary</h2>
    <div class="row">
        
        <!-- Total Premium Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-primary text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Total Gross Premium
                            </div>
                            {{-- Use $totalPremium passed from the controller --}}
                            <div class="h5 mb-0 fw-bold">KSH {{ number_format((float)$totalPremium, 2) }}</div>
                        </div>
                        <div class="col-auto ms-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Commission Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-success text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Total Commission Earned
                            </div>
                            {{-- Use $totalCommission passed from the controller --}}
                            <div class="h5 mb-0 fw-bold">KSH {{ number_format((float)$totalCommission, 2) }}</div>
                        </div>
                        <div class="col-auto ms-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Policies Count Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-info text-white shadow h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Policies Written
                            </div>
                            {{-- Use $totalPolicies passed from the controller --}}
                            <div class="h5 mb-0 fw-bold">{{ number_format((int)$totalPolicies) }}</div>
                        </div>
                        <div class="col-auto ms-auto">
                            <i class="fas fa-file-contract fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. Detailed Data Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <i class="fas fa-table me-1"></i>
            Detailed Policy Data
        </div>
        <div class="card-body">
            @if($policies->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                 <th>Agent</th>
                                <th>Policy No</th>
                                <th>Customer Name</th>
                                <th>Policy Type</th>
                                <th>Insurer</th>
                                <th>Premium</th>
                                <th>Commission</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                                <tr>
                                    {{-- Correctly calculating the iteration number for pagination --}}
                                    <td>{{ $loop->iteration + ($policies->currentPage() - 1) * $policies->perPage() }}</td>
                                    <td>
                                {{-- Ensure agent relationship is loaded and show name/code --}}
                                @if(isset($policy->agent) && $policy->agent)
                                    {{ $policy->agent->name }} ({{ $policy->agent->agent_code }})
                                @else
                                    -
                                @endif
                            </td>
                                    <td>{{ $policy->policy_no }}</td>
                                    {{-- Use the Customer model's name accessor if available, or fall back to provided fields --}}
                                    <td>{{ $policy->customer?->name ?? $policy->customer_name ?? '-' }}</td>
                                    <td>{{ $policy->policyType->type_name ?? '-' }}</td>
                                    <td>{{ $policy->insurer->name ?? '-' }}</td>
                                    <td>KSH {{ number_format((float)$policy->gross_premium, 2) }}</td> {{-- Assuming premium field is gross_premium or similar --}}
                                    <td>KSH {{ number_format((float)$policy->commission, 2) }}</td>
                                    <td>{{ Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                                    <td>{{ Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                                    <td>{{ $policy->status ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Pagination Links --}}
                <div class="mt-3">
                    {{ $policies->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                    <p>No policies found for the selected period and filters. Please adjust your dates or filter options.</p>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
