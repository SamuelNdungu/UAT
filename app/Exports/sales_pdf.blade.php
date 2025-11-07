<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportTitle ?? 'Sales Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #333;
        }
        .header p {
            font-size: 11px;
            margin: 2px 0;
            color: #555;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        .summary-table td {
            padding: 4px;
        }
        .summary-table .label {
            font-weight: bold;
            width: 120px;
            background-color: #f1f1f1;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        .data-table th {
            background-color: #e0f7fa; /* Light cyan background */
            color: #01579b; /* Darker blue text */
            text-transform: uppercase;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row td {
            font-weight: bold;
            background-color: #e0f7fa;
            border-top: 2px solid #01579b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $reportTitle ?? 'Sales Performance Report' }}</h1>
        <p>Report Period: {{ $startDate }} to {{ $endDate }}</p>
        <p>Generated On: {{ \Carbon\Carbon::now()->format('d-M-Y H:i:s') }}</p>
    </div>

    <!-- Summary Filters -->
    <table class="summary-table">
        <tr>
            <td class="label">Insurer Filter:</td>
            <td>{{ $filters['Insurer'] }}</td>
            <td class="label">Agent Filter:</td>
            <td>{{ $filters['Agent'] }}</td>
            <td class="label">Policy Type Filter:</td>
            <td>{{ $filters['Policy Type'] }}</td>
        </tr>
    </table>

    <!-- Totals Summary -->
    <table class="summary-table" style="margin-bottom: 20px;">
        <tr>
            <td class="label">Total Policies:</td>
            <td>{{ number_format($totalPolicies) }}</td>
            <td class="label">Total Gross Premium:</td>
            <td style="font-weight: bold;">KES {{ number_format($totalPremium, 2) }}</td>
            <td class="label">Total Commission:</td>
            <td style="font-weight: bold;">KES {{ number_format($totalCommission, 2) }}</td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th>Policy No</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Insurer</th>
                <th>Agent</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th style="text-align: right;">Premium (KES)</th>
                <th style="text-align: right;">Commission (KES)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($policies as $policy)
            <tr>
                <td>{{ $policy->policy_number }}</td>
                <td>{{ $policy->customer->customer_code }} - {{ $policy->customer->first_name ?? $policy->customer->corporate_name }}</td>
                <td>{{ $policy->policyType->type_name }}</td>
                <td>{{ $policy->insurer->name }}</td>
                <td>{{ $policy->agent->name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-M-Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-M-Y') }}</td>
                <td style="text-align: right;">{{ number_format($policy->gross_premium, 2) }}</td>
                <td style="text-align: right;">{{ number_format($policy->commission, 2) }}</td>
            </tr>
            @endforeach
            <!-- Final Total Row -->
            <tr class="total-row">
                <td colspan="7">GRAND TOTALS</td>
                <td style="text-align: right;">KES {{ number_format($totalPremium, 2) }}</td>
                <td style="text-align: right;">KES {{ number_format($totalCommission, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
