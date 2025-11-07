@extends('layouts.email')

@section('content')
    <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #003366; margin-bottom: 5px;">Your {{ ucfirst($frequency) }} Agent Report</h1>
            <h2 style="color: #2c5282; margin: 10px 0 5px 0;">{{ $agent->name }}</h2>
            <p style="color: #666; margin-top: 5px;">{{ $startDate->format('F j, Y') }} to {{ $endDate->format('F j, Y') }}</p>
            <p>Generated on: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Agent Summary -->
        <div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h2 style="color: #003366; margin-top: 0;">Hello, {{ $agent->name }} ({{ $agent->agent_code }})</h2>
            <p>Here's your {{ $frequency }} performance summary:</p>
            
            <div style="display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap;">
                <div style="background: white; border-left: 4px solid #007bff; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #0056b3; font-size: 16px;">Total Policies</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ number_format($totalPolicies) }}</p>
                </div>
                
                <div style="background: white; border-left: 4px solid #28a745; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #218838; font-size: 16px;">Total Premium</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">KSH {{ number_format($totalPremium, 2) }}</p>
                </div>
                
                <div style="background: white; border-left: 4px solid #6c757d; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #5a6268; font-size: 16px;">Total Commission</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">KSH {{ number_format($totalCommission, 2) }}</p>
                </div>
                
                @if(isset($agentReport['renewal_rate']))
                <div style="background: white; border-left: 4px solid #17a2b8; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #138496; font-size: 16px;">Renewal Rate</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ $agentReport['renewal_rate'] }}%</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Claims Summary -->
        @if(isset($claimsReport) && $claimsReport['total_claims'] > 0)
        <div style="margin: 30px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h2 style="color: #003366; margin-top: 0; border-bottom: 2px solid #003366; padding-bottom: 5px;">Claims Summary</h2>
            
            <div style="display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap;">
                <div style="background: white; border-left: 4px solid #007bff; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #0056b3; font-size: 16px;">Total Claims</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ $claimsReport['total_claims'] }}</p>
                </div>
                
                <div style="background: white; border-left: 4px solid #28a745; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #218838; font-size: 16px;">Approved</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ $claimsReport['approved_claims'] }}</p>
                </div>
                
                <div style="background: white; border-left: 4px solid #ffc107; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #e0a800; font-size: 16px;">Pending</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ $claimsReport['pending_claims'] }}</p>
                </div>
                
                <div style="background: white; border-left: 4px solid #dc3545; padding: 15px; margin: 10px; flex: 1; min-width: 200px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; color: #c82333; font-size: 16px;">Rejected</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0; color: #333;">{{ $claimsReport['rejected_claims'] }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Policy Details -->
        <div style="margin: 30px 0;">
            <h2 style="color: #003366; border-bottom: 2px solid #003366; padding-bottom: 5px;">Your Policies ({{ $policies->count() }})</h2>
            
            @if($policies->isNotEmpty())
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px;">
                        <thead>
                            <tr style="background-color: #f2f2f2;">
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">#</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Policy No</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Customer</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Type</th>
                                <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Premium</th>
                                <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Commission</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Start Date</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">End Date</th>
                                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $index => $policy)
                                <tr style="border-bottom: 1px solid #ddd;">
                                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $index + 1 }}</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">{{ $policy->policy_no ?? 'N/A' }}</td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        {{ $policy->customer?->name ?? $policy->customer_name ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        {{ $policy->policyType->type_name ?? 'N/A' }}
                                    </td>
                                    <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">
                                        KSH {{ number_format((float)($policy->gross_premium ?? 0), 2) }}
                                    </td>
                                    <td style="padding: 10px; text-align: right; border: 1px solid #ddd;">
                                        KSH {{ number_format((float)($policy->commission ?? 0), 2) }}
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        {{ $policy->start_date ? \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') : 'N/A' }}
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        {{ $policy->end_date ? \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') : 'N/A' }}
                                    </td>
                                    <td style="padding: 10px; border: 1px solid #ddd;">
                                        @php
                                            $statusBg = 'bg-secondary';
                                            $statusText = 'text-white';
                                            
                                            switch(strtolower($policy->status)) {
                                                case 'active':
                                                    $statusBg = 'bg-success';
                                                    $statusText = 'text-white';
                                                    break;
                                                case 'expired':
                                                case 'cancelled':
                                                    $statusBg = 'bg-danger';
                                                    $statusText = 'text-white';
                                                    break;
                                                case 'pending':
                                                    $statusBg = 'bg-warning';
                                                    $statusText = 'text-dark';
                                                    break;
                                            }
                                        @endphp
                                        <span class="badge {{ $statusBg }} {{ $statusText }} px-2 py-1 rounded" style="font-size: 12px;">
                                            {{ ucfirst($policy->status ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 20px; text-align: center; background: #f8f9fa; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; color: #6c757d;">No policies found for this period.</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center; font-size: 14px; color: #6c757d;">
            <p style="margin: 0 0 10px 0;">This is an automated report. Please do not reply to this email.</p>
            <p style="margin: 0;">&copy; {{ date('Y') }} Bima Connect. All rights reserved.</p>
        </div>
    </div>
@endsection
