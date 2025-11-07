<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Sales Performance Report' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background: #eee; }
        .summary { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>{{ $reportTitle ?? 'Sales Performance Report' }}</h2>
    <div class="summary">
        <strong>Period:</strong> {{ $startDate }} to {{ $endDate }}<br>
        <strong>Insurer:</strong> {{ $filters['Insurer'] ?? 'All' }}<br>
        <strong>Agent:</strong> {{ $filters['Agent'] ?? 'All' }}<br>
        <strong>Policy Type:</strong> {{ $filters['Policy Type'] ?? 'All' }}<br>
        <strong>Total Premium:</strong> KSH {{ number_format((float)$totalPremium, 2) }}<br>
        <strong>Total Commission:</strong> KSH {{ number_format((float)$totalCommission, 2) }}<br>
        <strong>Total Policies:</strong> {{ $totalPolicies }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Policy No</th>
                <th>Customer Name</th>
                <th>Policy Type</th>
                <th>Insurer</th>
                <th>Agent</th>
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
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $policy->policy_no }}</td>
                    <td>{{ $policy->customer_name ?? ($policy->customer->name ?? '-') }}</td>
                    <td>{{ $policy->policyType->type_name ?? '-' }}</td>
                    <td>{{ $policy->insurer->name ?? '-' }}</td>
                    <td>{{ $policy->agent->name ?? '-' }}</td>
                    <td>KSH {{ number_format((float)$policy->gross_premium, 2) }}</td>
                    <td>KSH {{ number_format((float)$policy->commission, 2) }}</td>
                    <td>{{ $policy->start_date }}</td>
                    <td>{{ $policy->end_date }}</td>
                    <td>{{ $policy->status ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
