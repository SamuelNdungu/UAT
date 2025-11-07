@extends('layouts.email')

@section('content')
    <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto;">
        <div style="background-color: #003366; padding: 20px; text-align: center; color: white;">
            <h1>Bima Connect {{ ucfirst($frequency) }} Policy Report</h1>
            <p>Report Period: {{ $startDate->format('F j, Y') }} to {{ $endDate->format('F j, Y') }}</p>
            <p>Generated on: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>

        <div style="margin: 30px 0;">
            <h2 style="color: #003366; border-bottom: 2px solid #003366; padding-bottom: 5px;">Policy Summary</h2>
            
            <div style="display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap;">
                <div style="background: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 10px; flex: 1; min-width: 200px;">
                    <h3 style="margin-top: 0; color: #0056b3;">Total Policies</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">{{ $policies->count() }}</p>
                </div>
                
                <div style="background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 10px; flex: 1; min-width: 200px;">
                    <h3 style="margin-top: 0; color: #218838;">Total Premium</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">KSH {{ number_format($policies->sum('gross_premium'), 2) }}</p>
                </div>
                
                <div style="background: #f8f9fa; border-left: 4px solid #6c757d; padding: 15px; margin: 10px; flex: 1; min-width: 200px;">
                    <h3 style="margin-top: 0; color: #5a6268;">Total Commission</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">KSH {{ number_format($policies->sum('commission'), 2) }}</p>
                </div>
            </div>

            <h3>Policy Details</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">#</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Agent</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Policy No</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Customer Name</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Policy Type</th>
                            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Premium</th>
                            <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Commission</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Start Date</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">End Date</th>
                            <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; border: 1px solid #ddd;">{{ $loop->iteration }}</td>
                                <td style="padding: 10px; border: 1px solid #ddd;">
                                    @if($policy->agent)
                                        {{ $policy->agent->name }} ({{ $policy->agent->agent_code ?? 'N/A' }})
                                    @else
                                        -
                                    @endif
                                </td>
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
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 3px; 
                                        background-color: {{ $policy->status === 'active' ? '#d4edda' : 
                                            ($policy->status === 'expired' ? '#f8d7da' : '#fff3cd') }}; 
                                        color: {{ $policy->status === 'active' ? '#155724' : 
                                            ($policy->status === 'expired' ? '#721c24' : '#856404') }};">
                                        {{ ucfirst($policy->status ?? 'N/A') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="padding: 15px; text-align: center; border: 1px solid #ddd;">
                                    No policies found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($policies->hasPages())
                <div style="margin-top: 20px; text-align: center;">
                    @if($policies->currentPage() > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $policies->currentPage() - 1]) }}" 
                           style="display: inline-block; padding: 8px 16px; margin: 0 5px; background: #f8f9fa; border: 1px solid #ddd; text-decoration: none; color: #333;">
                            &laquo; Previous
                        </a>
                    @endif
                    
                    @foreach(range(1, $policies->lastPage()) as $page)
                        @if($page == $policies->currentPage())
                            <span style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #007bff; color: white; border: 1px solid #007bff;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" 
                               style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: #f8f9fa; border: 1px solid #ddd; text-decoration: none; color: #333;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                    
                    @if($policies->hasMorePages())
                        <a href="{{ request()->fullUrlWithQuery(['page' => $policies->currentPage() + 1]) }}" 
                           style="display: inline-block; padding: 8px 16px; margin: 0 5px; background: #f8f9fa; border: 1px solid #ddd; text-decoration: none; color: #333;">
                            Next &raquo;
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div style="margin: 40px 0;">
            <h2 style="color: #003366; border-bottom: 2px solid #003366; padding-bottom: 5px;">Agent Performance Summary</h2>
            
            <div style="display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap;">
                <div style="background: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 10px; flex: 1; min-width: 200px;">
                    <h3 style="margin-top: 0; color: #0056b3;">Total Policies</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">{{ $agentReport['total_policies'] }}</p>
                </div>
                
                <div style="background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 10px; flex: 1; min-width: 200px;">
                    <h3 style="margin-top: 0; color: #218838;">Total Premium</h3>
                    <p style="font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">KES {{ number_format($agentReport['total_premium'], 2) }}</p>
                </div>
            </div>

            <h3>Top Performing Agents</h3>
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Agent</th>
                        <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Policies Sold</th>
                        <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Total Premium</th>
                        <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Renewal Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentReport['top_agents'] as $agent)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">{{ $agent['name'] }}</td>
                            <td style="padding: 12px; text-align: right; border: 1px solid #ddd;">{{ $agent['policies_sold'] }}</td>
                            <td style="padding: 12px; text-align: right; border: 1px solid #ddd;">KES {{ number_format($agent['total_premium'], 2) }}</td>
                            <td style="padding: 12px; text-align: right; border: 1px solid #ddd;">{{ $agent['renewal_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding: 12px; text-align: center; border: 1px solid #ddd;">No agent data available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 40px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #6c757d;">
            <p>This is an automated report generated by Bima Connect. To adjust your report preferences, please contact your system administrator.</p>
            <p>© {{ date('Y') }} Bima Connect. All rights reserved.</p>
        </div>
    </div>
@endsection
