@extends('layouts.appPages')

@section('content')
<div class="container py-4">

    <style>
        /* Modern Design Palette & Utility Classes */
        :root {
            --primary-blue: #007bff; /* Standard professional blue */
            --light-bg: #f8f9fa;     /* Very light grey background */
            --text-dark: #343a40;     /* Dark text */
            --text-muted: #6c757d;    /* Muted text */
            --shadow-subtle: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        /* --- Global Structure and Cards --- */
        .card-modern {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-subtle);
            margin-bottom: 1.5rem;
            transition: all 0.2s;
        }

        /* --- Summary Cards (Top Row) --- */
        .summary-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .summary-card {
            flex: 1 1 200px;
            padding: 1.25rem;
            box-shadow: var(--shadow-md);
            border: none;
        }
        .summary-title {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* --- Section Styling (Main Content) --- */
        .section-header-modern {
            padding: 0.75rem 1.25rem;
            background-color: var(--light-bg);
            border-bottom: 1px solid #e9ecef;
            border-radius: 0.5rem 0.5rem 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-blue);
        }

        /* --- Key-Value List --- */
        .detail-list {
            padding: 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem 2rem;
        }
        .detail-item {
            display: flex;
        }
        .detail-label {
            min-width: 140px;
            color: var(--text-muted);
            font-weight: 500;
            flex-shrink: 0;
        }
        .detail-value {
            color: var(--text-dark);
            font-weight: 600;
        }

        /* --- Financial Section Mini-Card Styles --- */
        .mini-card {
            background: var(--light-bg);
            border-radius: 0.35rem;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-blue); /* Primary accent */
            height: calc(100% - 1rem); /* Fill column height */
        }
        .mini-card-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        .mini-card-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .mini-card.accent-success { border-left-color: #28a745; }
        .mini-card.accent-warning { border-left-color: #ffc107; }

        /* Gross Premium Total Card */
        .total-card {
            background-color: var(--primary-blue);
            color: #fff;
            padding: 1.0rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-md);
        }
        .total-card .summary-title {
            color: rgba(255, 255, 255, 0.75);
            font-size: 1rem;
        }
        .total-card .summary-value {
            color: #fff;
            font-size: 2rem;
            line-height: 1.2;
        }

        /* --- Badges and Text --- */
        .badge-pill-modern {
            padding: 0.35rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1;
        }
        .badge-active { background-color: #28a745; color: #fff; } /* Green */
        .badge-inactive { background-color: #dc3545; color: #fff; } /* Red */
        .preserve-formatting { 
            white-space: pre-wrap; 
            padding: 1.25rem;
            color: var(--text-dark);
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .summary-row { gap: 1rem; }
            .summary-card { flex: 1 1 100%; }
            .detail-list { grid-template-columns: 1fr; }
            .detail-label { min-width: 120px; }
        }
    </style>

    <h2 class="mb-5 text-center text-dark">Policy Details: {{ $policy->fileno }}</h2>
    @if($policy->isCancelled())
        <div class="alert alert-warning mb-4">This policy is <strong>canceled</strong>. All actions are disabled.</div>
    @endif
            <div style="justify-self:end; display:flex; align-items:center; gap:0.5rem;">
                @if(isset($policy->status))
                    @if($policy->isCancelled())
                        <span class="badge-pill-modern badge-inactive" style="background-color:#dc3545; color:#fff;">Canceled</span>
                    @elseif($policy->status)
                        <span class="badge-pill-modern badge-active">Active</span>
                    @else
                        <span class="badge-pill-modern badge-inactive">Expired</span>
                    @endif
                @endif
            </div>
    {{-- REPLACED: COMBINED POLICY & CLIENT SUMMARY -> POLICY SNAPSHOT --}}
    <div class="card-modern" style="margin-bottom:1.5rem;">
        <div class="section-header-modern">
            <div class="section-title">Customer Details</div>
             
        </div>
        <div class="detail-list" style="align-items:center;">
            <div class="detail-item">
                <div class="detail-label">File No:</div>
                <div class="detail-value">{{ $policy->fileno }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer Code:</div>
                <div class="detail-value">{{ $policy->customer_code }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Customer Name:</div>
                <div class="detail-value">{{ $policy->customer_name }}</div>
            </div>

                        <div class="detail-item">
                <div class="detail-label">Phone:</div>
                <div class="detail-value">{{ $policy->customer->phone ?? $policy->phone ?? '-' }}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email:</div>
                <div class="detail-value">{{ $policy->customer->email ?? $policy->email ?? '-' }}</div>
            </div>



        </div>
    </div>

    {{-- MAIN CONTENT STACKED CARDS --}}
    
    {{-- Policy Details Section (Full-width) --}}
    <div class="card-modern">
        <div class="section-header-modern">
            <div class="section-title">Policy & Coverage</div>
             
        </div>
        <div class="detail-list">
<div class="detail-item">
                <div class="detail-label">Policy No</div>
                <div class="detail-value">{{ $policy->policy_no }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Policy Period</div>
                <div class="detail-value">
                    {{ \Carbon\Carbon::parse($policy->start_date)->format('d M Y') }} — {{ \Carbon\Carbon::parse($policy->end_date)->format('d M Y') }}
                </div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Insurer</div>
                <div class="detail-value">{{ $policy->insurer_name }}</div>
            </div>


            <div class="detail-item"><div class="detail-label">Coverage:</div><div class="detail-value">{{ $policy->coverage }}</div></div>
            <div class="detail-item"><div class="detail-label">Sum Insured:</div><div class="detail-value">KES {{ number_format($policy->sum_insured, 2) }}</div></div>
            <div class="detail-item"><div class="detail-label">Rate:</div><div class="detail-value">{{ number_format($policy->rate, 2) }}%</div></div>
        </div>
    </div>

    {{-- Vehicle / Description (Conditional Full-width) --}}
    @php
        // Check if any vehicle data exists to display the card
        $hasVehicleData = $policy->reg_no || $policy->make || $policy->model;
    @endphp

    <div id="vehicle-card" class="card-modern" style="display:{{ $hasVehicleData ? 'block' : 'none' }}">
        <div class="section-header-modern"><div class="section-title">Vehicle Details</div></div>
        <div class="detail-list">
            <div class="detail-item"><div class="detail-label">Registration No:</div><div class="detail-value">{{ $policy->reg_no }}</div></div>
            <div class="detail-item"><div class="detail-label">Make / Model:</div><div class="detail-value">{{ $policy->make }} / {{ $policy->model }}</div></div>
            <div class="detail-item"><div class="detail-label">Y.O.M / CC:</div><div class="detail-value">{{ $policy->yom }} / {{ $policy->cc }}</div></div>
            <div class="detail-item"><div class="detail-label">Chassis / Engine:</div><div class="detail-value">{{ $policy->chassisno }} / {{ $policy->engine_no }}</div></div>
        </div>
    </div>

    <div id="description-card" class="card-modern" style="display:{{ $hasVehicleData ? 'none' : 'block' }}">
        <div class="section-header-modern"><div class="section-title">Description</div></div>
        <div class="preserve-formatting">{{ $policy->description }}</div>
    </div>

    {{-- FINANCIAL SECTION - IMPROVED LAYOUT --}}
    <div class="card-modern">
        <div class="section-header-modern"><div class="section-title">Financial Details (KES)</div></div>
        
        <div class="p-4">
            {{-- Gross Premium Total highlighted in a separate card for maximum impact --}}
            <div class="total-card">
                <div class="summary-title">TOTAL GROSS PREMIUM</div>
                <div class="summary-value">KES {{ number_format($policy->gross_premium, 2) }}</div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Sum Insured</div>
                        <div class="mini-card-value">{{ number_format($policy->sum_insured ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Rate (%)</div>
                        <div class="mini-card-value">{{ number_format($policy->rate ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Commission Rate (%)</div>
                        <div class="mini-card-value">{{ number_format($policy->c_rate ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">WHT</div>
                        <div class="mini-card-value">{{ number_format($policy->wht ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">AA Charges</div>
                        <div class="mini-card-value">{{ number_format($policy->aa_charges ?? 0, 2) }}</div>
                    </div>
                </div>
                {{-- Metrics in smaller, distinct mini-cards (4 columns on md, 2 on sm) --}}
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Premium</div>
                        <div class="mini-card-value">{{ number_format($policy->premium, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card accent-success">
                        <div class="mini-card-label">Commission</div>
                        <div class="mini-card-value text-success">{{ number_format($policy->commission, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Net Premium</div>
                        <div class="mini-card-value">{{ number_format($policy->net_premium, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">PVT</div>
                        <div class="mini-card-value">{{ number_format($policy->pvt ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">PPL</div>
                        <div class="mini-card-value">{{ number_format($policy->ppl ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Excess</div>
                        <div class="mini-card-value">{{ number_format($policy->excess ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Courtesy Car</div>
                        <div class="mini-card-value">{{ number_format($policy->courtesy_car ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">S. Duty</div>
                        <div class="mini-card-value">{{ number_format($policy->s_duty ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">T. Levy</div>
                        <div class="mini-card-value">{{ number_format($policy->t_levy ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">PCF Levy</div>
                        <div class="mini-card-value">{{ number_format($policy->pcf_levy ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Policy Charge</div>
                        <div class="mini-card-value">{{ number_format($policy->policy_charge ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Other Charges</div>
                        <div class="mini-card-value">{{ number_format($policy->other_charges ?? 0, 2) }}</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="mini-card">
                        <div class="mini-card-label">Road Rescue</div>
                        <div class="mini-card-value">{{ number_format($policy->road_rescue ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card accent-info">
                        <div class="mini-card-label">Paid Amount</div>
                        <div class="mini-card-value">{{ number_format($policy->paid_amount ?? 0, 2) }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="mini-card accent-danger">
                        <div class="mini-card-label">Balance</div>
                        <div class="mini-card-value">{{ number_format($policy->balance ?? 0, 2) }}</div>
                    </div>
                </div>
             </div>
        </div>
    </div>
    {{-- END FINANCIAL SECTION --}}

    {{-- Cover Details (Full-width) --}}
    <div class="card-modern">
        <div class="section-header-modern"><div class="section-title">Cover Details</div></div>
        <div class="preserve-formatting">{{ $policy->cover_details }}</div>
    </div>

    {{-- Documents Section (Full-width) --}}
    <div class="card-modern">
        <div class="section-header-modern"><div class="section-title">Documents</div></div>
        <div class="p-3">
            <table class="table table-sm table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Description</th>
                        <th style="width: 30%;">File</th>
                    </tr>
                </thead>
                <tbody>
                    @if(is_array($policy->documents) || $policy->documents instanceof \Illuminate\Support\Collection)
                        @foreach($policy->documents as $i => $doc)
                            @php
                                $path = $doc['file'] ?? ($doc['path'] ?? null);
                                $name = $doc['original_name'] ?? ($doc['name'] ?? basename($path ?? 'file'));
                            @endphp
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $doc['description'] ?? '—' }}</td>
                                <td>
                                    @if($path)
                                        <a href="{{ asset('storage/' . $path) }}" download class="text-primary">{{ $name }}</a>
                                    @else
                                        <span class="text-muted small">No file uploaded</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="3" class="text-center text-muted">No documents found for this policy.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Actions (Full-width, right-aligned) --}}
    <div class="d-flex justify-content-end gap-3 mb-5">
        @php
            $isReadOnly = $policy->isCancelled() || $policy->isRenewed();
        @endphp

        <a href="{{ route('policies.index') }}" class="btn btn-outline-primary d-flex align-items-center" title="Back to list" aria-label="Back to list">
            <i class="fas fa-arrow-left me-2"></i> Go Back
        </a>

        @if(! $isReadOnly)
            {{-- Endorsement create (nested resource) --}}
            <a href="{{ route('policies.endorsements.create', $policy->id) }}" class="btn btn-primary d-flex align-items-center" title="Create Endorsement" aria-label="Create Endorsement">
                <i class="fas fa-plus me-2"></i> Endorse
            </a>

            {{-- Edit --}}
            <a href="{{ route('policies.edit', $policy->id) }}" class="btn btn-warning d-flex align-items-center" title="Edit policy" aria-label="Edit policy">
                <i class="fas fa-edit me-2"></i> Edit
            </a>

            {{-- Renew (icon-only) --}}
            <a href="{{ route('renewals.renew', $policy->id) }}" class="btn btn-outline-warning d-flex align-items-center" title="Create renewal" aria-label="Create renewal">
                <i class="fas fa-sync-alt"></i>
            </a>
        @endif

        {{-- Print and history always available --}}
        <a href="{{ route('policies.printDebitNote', $policy->id) }}" class="btn btn-success d-flex align-items-center" target="_blank" title="Print debit note" aria-label="Print debit note">
            <i class="fas fa-file-invoice me-2"></i> Print Debit Note
        </a>

        <a href="{{ route('policies.history', $policy->id) }}" class="btn btn-secondary d-flex align-items-center" title="View renewal history" aria-label="View renewal history">
            <i class="fas fa-history me-2"></i> View History
        </a>
    </div>

    @if($policy->isCancelled())
        <div class="alert alert-warning">This policy is <strong>canceled</strong>. All actions are disabled.</div>
    @endif

    {{-- New Sections: Endorsement Summary & History --}}
    <div class="card-modern">
        <div class="section-header-modern">
            <div class="section-title">Endorsement Summary</div>
        </div>
        <div class="detail-list">
            <div class="detail-item">
                <div class="detail-label">Running Balance:</div>
                <div class="detail-value" style="color: {{ $policy->balance >= 0 ? 'green' : 'red' }};">
                    {{ number_format($policy->balance, 2) }}
                </div>
            </div>
        </div>
    </div>

    <div class="card-modern">
        <div class="section-header-modern">
            <div class="section-title">Endorsement History</div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Effective Date</th>
                    <th>Premium Impact</th>
                </tr>
            </thead>
            <tbody>
                @foreach($policy->endorsements as $endorsement)
                <tr>
                    <td>{{ $endorsement->endorsement_type }}</td>
                    <td>{{ $endorsement->reason ?? 'N/A' }}</td>
                    <td>{{ $endorsement->effective_date }}</td>
                    <td style="color: {{ $endorsement->premium_impact >= 0 ? 'green' : 'red' }};">
                        {{ number_format($endorsement->premium_impact, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // NOTE: Ensure there is a hidden input with the policy type ID somewhere on the page, 
        // e.g., <input type="hidden" id="policy_type_id" value="{{ $policy->policy_type_id }}">
        
        const policyTypeIdElement = document.getElementById('policy_type_id');
        let policyTypeId = policyTypeIdElement ? policyTypeIdElement.value : null;

        const vehicleCard = document.getElementById('vehicle-card');
        const descriptionCard = document.getElementById('description-card');

        // Policy types that display vehicle details (Motor-related IDs: 35, 36, 37)
        const motorPolicyIds = ['35', '36', '37'];

        if (policyTypeId && vehicleCard && descriptionCard) {
            if (motorPolicyIds.includes(String(policyTypeId))) {
                vehicleCard.style.display = 'block';
                descriptionCard.style.display = 'none';
            } else {
                vehicleCard.style.display = 'none';
                descriptionCard.style.display = 'block';
            }
        }
    });
</script>
@endsection