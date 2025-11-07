@extends('layouts.appPages') 

@section('content')
<div class="container">
    <h1 class="my-4">Sales Report Generation</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Filter Report</div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}" class="form-row">
                
                <div class="form-group col-md-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ $filters['start_date'] ?? date('Y-m-01') }}" required>
                </div>
                <div class="form-group col-md-3">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ $filters['end_date'] ?? date('Y-m-d') }}" required>
                </div>
                
                <div class="form-group col-md-3">
                    <label for="insurer_id">Insurer</label>
                    <select name="insurer_id" id="insurer_id" class="form-control">
                        <option value="">-- All Insurers --</option>
                        @foreach($insurers as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['insurer_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label for="agent_id">Agent / Broker</label>
                    <select name="agent_id" id="agent_id" class="form-control">
                        <option value="">-- All Agents --</option>
                        @foreach($agents as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['agent_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label for="policy_type_id">Policy Type</label>
                    <select name="policy_type_id" id="policy_type_id" class="form-control">
                        <option value="">-- All Types --</option>
                        @foreach($policyTypes as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['policy_type_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    @if ($data->isNotEmpty())
                        <a href="{{ route('reports.sales.export', $filters) }}" class="btn btn-success ml-2">Export Excel</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if ($data->isNotEmpty())
        <div class="card">
            <div class="card-header">Report Results ({{ $data->count() }} Policies Found)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date Issued</th>
                                <th>Policy No</th>
                                <th>Customer</th>
                                <th>Insurer</th>
                                <th>Type</th>
                                <th>Gross Premium</th>
                                <th>Commission</th>
                                <th>Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $policy)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $policy->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $policy->policy_no ?? $policy->fileno }}</td>
                                    <td>{{ $policy->customer->customer_name ?? 'N/A' }}</td>
                                    <td>{{ $policy->insurer->name ?? 'N/A' }}</td>
                                    <td>{{ $policy->policyType->type_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($policy->gross_premium, 2) }}</td>
                                    <td>{{ number_format($policy->commission, 2) }}</td>
                                    <td>{{ $policy->agent->name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                             <tr>
                                <th colspan="6">TOTALS</th>
                                <th>{{ number_format($data->sum('gross_premium'), 2) }}</th>
                                <th>{{ number_format($data->sum('commission'), 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @elseif ($request->filled('start_date'))
        <div class="alert alert-info">No sales policies found for the selected criteria.</div>
    @else 
        <div class="alert alert-warning">Select your filters and click 'Generate Report' to view results.</div>
    @endif
</div>
@endsection