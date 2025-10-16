@extends('layouts.appPages')

@section('content')
<div class="container py-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 fw-bold">Renewal History — File: {{ $policy->fileno }}</h4>
            <p class="mb-0 text-muted">
                <strong>Customer:</strong>
                {{ $policy->customer_name ?? ($policy->customer->customer_name ?? 'N/A') }}
            </p>
        </div>
        <div>
            <a href="{{ route('policies.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Policies
            </a>
        </div>
    </div>

    {{-- Renewal Table --}}
    @if($chain->isNotEmpty())
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-striped table-hover table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>#</th>
                        <th>Policy Type</th>
                        <th>Policy No</th>
                        <th>Reg No</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Premium (KES)</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chain as $index => $p)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $p->policyType?->type_name ?? 'N/A' }}</td>
                            <td>{{ $p->policy_no ?? '—' }}</td>
                            <td>{{ $p->reg_no ?? 'N/A' }}</td>
                            <td>{{ optional($p->start_date)->format('d-m-Y') }}</td>
                            <td>{{ optional($p->end_date)->format('d-m-Y') }}</td>
                            <td>
                                <span class="badge 
                                    @if($p->status === 'Active') bg-success 
                                    @elseif(str_contains(strtolower($p->status), 'renew')) bg-warning text-dark
                                    @else bg-secondary 
                                    @endif">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format($p->gross_premium ?? 0, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('policies.show', $p->id) }}" class="btn btn-sm btn-info me-1">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($p->id != $policy->id)
                                    <a href="{{ route('renewals.renew', $p->id) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-arrow-repeat"></i> Renew
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> No renewal records found for this file.
        </div>
    @endif
</div>
@endsection
