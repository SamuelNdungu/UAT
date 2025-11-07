<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $policy->invoice_no ?? 'N/A' }}</title>
    <style>
        /* Base styles optimized for dompdf/PDF rendering */
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            color: #222; 
            font-size: 10pt;
            line-height: 1.4;
            padding: 5px;
        }
        .container { margin: 0 auto; width: 100%; }
        
        /* Header - using table for reliable two-column layout in PDF */
        .header-table { width: 100%; margin-bottom: 20px; border: none; }
        .header-table td { border: none; padding: 0; vertical-align: top; }

        .company-info h2 { margin: 0; font-size: 14pt; color: #0056b3; }
        .meta-info { text-align: right; }
        .invoice-no-box { 
            font-size: 12pt; 
            font-weight: 500; 
            color: #fff; 
            background-color: #007bff; 
            padding: 5px 10px; 
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
        
        /* Section Separator */
        .section-heading { 
            font-size: 11pt; 
            font-weight: bold; 
            color: #333; 
            margin: 10px 0 5px 0; 
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }

        /* General Table Styling */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 5px 7px; border: 1px solid #ddd; font-size: 10pt; vertical-align: top; }
        th { background: #f0f0f0; text-align: left; font-weight: bold; }
        .text-right { text-align: right; }

        /* Breakdown Table Specifics */
        .breakdown th, .breakdown td { border: none; border-bottom: 1px solid #eee; }
        .breakdown th { background: none; }
        .total-row td { 
            background-color: #e6f7ff; 
            font-weight: bold; 
            border-top: 2px solid #007bff;
            font-size: 10pt;
        }
        .data-label { font-weight: bold; width: 30%; background-color: #f7f7f7; }
        .data-value { width: 70%; }
        .clear { clear: both; }
        .notes { margin-top: 20px; font-size: 9pt; color: #555; }

    </style>
</head>
<body>
    <div class="container">

        <table class="header-table">
            <tr>
                <td style="width: 50%;">
                    <div class="company-info">
                        <h2>{{ $company->company_name ?? 'Your Company Name' }}</h2>
                        <div style="font-size: 9pt;">
                            @if(!empty($company->address))
                                <div>{!! nl2br(e($company->address)) !!}</div>
                            @endif
                            <div>Phone: {{ $company->phone ?? 'N/A' }}</div>
                            <div>Email: {{ $company->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </td>
                <td style="width: 50%;" class="meta-info">
                    <h1>INVOICE</h1>
                    <div class="invoice-no-box">INV. NO: {{ $policy->invoice_no ?? '-' }}</div>
                    <div style="margin-top: 10px; font-size: 10pt;">
                        **Date Issued:** {{ \Carbon\Carbon::now()->format('d-M-Y') }}<br>
                        **File No:** {{ $policy->fileno ?? '-' }}
                    </div>
                </td>
            </tr>
        </table>
        
        <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;">
            <div class="section-heading" style="margin-top: 0; border: none;">BILL TO</div>
            <div style="font-weight: bold; font-size: 11pt; color: #007bff; margin-bottom: 5px;">
                {{ $policy->customer?->name ?? $policy->customer_name ?? 'N/A' }}
            </div>
            <div style="font-size: 9pt;">
                @if(!empty($policy->customer?->address))
                    {!! nl2br(e($policy->customer->address)) !!}<br>
                @endif
                Phone: {{ $policy->customer?->phone ?? 'N/A' }} | Email: {{ $policy->customer?->email ?? 'N/A' }}
            </div>
        </div>

        <div class="section-heading">POLICY DETAILS</div>
        <table>
            <tbody>
                <tr>
                    <td class="data-label">Policy Type</td>
                    <td class="data-value">{{ $policy->policyType->type_name ?? 'N/A' }}</td>
                    <td class="data-label">Insurer</td>
                    <td class="data-value">{{ $policy->insurer->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="data-label">Policy No</td>
                    <td class="data-value">{{ $policy->policy_no ?? 'TBA' }}</td>
                    <td class="data-label">Period</td>
                    <td class="data-value">
                        {{ \Carbon\Carbon::parse($policy->start_date)->format('d-M-Y') }} 
                        to 
                        {{ \Carbon\Carbon::parse($policy->end_date)->format('d-M-Y') }}
                    </td>
                </tr>
            </tbody>
        </table>

        @if ($policy->policy_type_id == '35' || $policy->policy_type_id == '36' || $policy->policy_type_id == '37')
            <div class="section-heading">VEHICLE DETAILS</div>
            <table>
                <tbody>
                    <tr>
                        <td class="data-label">Reg. No.</td>
                        <td class="data-value">{{ $policy->reg_no }}</td>
                        <td class="data-label">Make/Model</td>
                        <td class="data-value">{{ $policy->make }} / {{ $policy->model }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Chassis No</td>
                        <td class="data-value">{{ $policy->chassisno }}</td>
                        <td class="data-label">Engine No</td>
                        <td class="data-value">{{ $policy->engine_no }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Y.O.M / CC</td>
                        <td class="data-value">{{ $policy->yom }} / {{ $policy->cc }}</td>
                        <td class="data-label">Body Type</td>
                        <td class="data-value">{{ $policy->body_type }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        

        <div class="section-heading">FINANCIAL DETAILS</div>
        <table class="breakdown">
            <thead>
                <tr>
                    <th style="width: 70%;">Description</th>
                    <th class="text-right" style="width: 30%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Sum Insured (Treated separately from Premium)</td>
                    <td class="text-right">{{ number_format($policy->sum_insured ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Basic Premium (before statutory charges)</td>
                    <td class="text-right">{{ number_format($policy->premium ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Stamp Duty (S. Duty)</td>
                    <td class="text-right">{{ number_format($policy->s_duty ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Training Levy (T. Levy)</td>
                    <td class="text-right">{{ number_format($policy->t_levy ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>PCF Levy</td>
                    <td class="text-right">{{ number_format($policy->pcf_levy ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Policy Charges, AA, and Other Fees ({{ number_format($policy->policy_charge ?? 0, 2) }} + {{ number_format($policy->aa_charges ?? 0, 2) }} + {{ number_format($policy->other_charges ?? 0, 2) }})</td>
                    <td class="text-right">{{ number_format(($policy->policy_charge ?? 0) + ($policy->aa_charges ?? 0) + ($policy->other_charges ?? 0), 2) }}</td>
                </tr>
                @if($policy->pvt > 0 || $policy->excess > 0 || $policy->courtesy_car > 0 || $policy->ppl > 0 || $policy->road_rescue > 0)
                <tr>
                    <td colspan="2" style="font-weight: bold; background-color: #f0f0f0;">Optional Motor Add-ons (Included in Premium)</td>
                </tr>
                <tr>
                    <td>PVT, Excess, Courtesy Car, PPL, Road Rescue Charges</td>
                    <td class="text-right">{{ number_format(($policy->pvt ?? 0) + ($policy->excess ?? 0) + ($policy->courtesy_car ?? 0) + ($policy->ppl ?? 0) + ($policy->road_rescue ?? 0), 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>**TOTAL AMOUNT DUE**</td>
                    <td class="text-right">{{ number_format($policy->gross_premium ?? 0, 2) }}</td>
                </tr>
               
            </tbody>
        </table>
<div class="section-heading">COVER DETAILS</div>
        <div style="border: 1px solid #eee; padding: 10px;">
            {!! nl2br(e($policy->description ?? 'No specific cover description provided.')) !!}
        </div>
        <div class="notes">
            **Notes:**<br>
            {!! nl2br(e($policy->notes ?? '')) !!}
        </div>

        <div style="margin-top: 50px; text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #ccc; padding-top: 10px;">
            Thank you for your business. This is a computer-generated invoice and may not require a signature.
        </div>
    </div>

</body>
</html>