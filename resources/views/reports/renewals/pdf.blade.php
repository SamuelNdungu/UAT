<!DOCTYPE html>
<html>
<head>
    <title>Renewal Notices Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .filters { margin-bottom: 15px; }
        .filters p { margin: 2px 0; }
        .filters strong { display: inline-block; width: 100px; }
        .summary { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Renewal Notices Report</h1>
        <p>Generated on {{ \Carbon\Carbon::now()->format('d-M-Y H:i') }}</p>
    </div>

    <div class="filters">
        <h3>Filters Applied:</h3>
        <p><strong>Start Date:</strong> {{ $filters['Start Date'] ?? 'All' }}</p>
        <p><strong>End Date:</strong> {{ $filters['End Date'] ?? 'All' }}</p>
        <p><strong>Insurer:</strong> {{ $filters['Insurer'] ?? 'All' }}</p>
        <p><strong>Agent:</strong> {{ $filters['Agent'] ?? 'All' }}</p>
        <p><strong>Policy Type:</strong> {{ $filters['Policy Type'] ?? 'All' }}</p>
    </div>

    <div class="summary">
        <p><strong>Total Policies:</strong> {{ $totalPolicies }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>File No.</th>
                <th>Entry Date</th>
                <th>Cust Code</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Policy Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Insurer</th>
                <th>Policy No</th>
                <th>Reg.No</th>
                <th>Gross Premium</th>
                <th>Status</th>
                <th>Notice Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($policies as $policy)
                <tr>
                    <td>{{ $policy->fileno }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $policy->customer_code }}</td>
                    <td>{{ $policy->customer_name }}</td>
                    <td>{{ $policy->mobile ?? $policy->mobile_number ?? '-' }}</td>
                    <td>{{ $policy->policyType->type_name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('d-m-Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</td>
                    <td>{{ $policy->insurer->name ?? '-' }}</td>
                    <td>{{ $policy->policy_no }}</td>
                    <td>{{ $policy->reg_no }}</td>
                    <td>{{ number_format($policy->gross_premium, 2) }}</td>
                    <td>{{ $policy->status }}</td>
                    <td>
                        @php
                            $noticeStatus = 'Not Sent';
                            $note = $notices->get($policy->id); // Changed from 'policy->fileno' to 'policy->id'

                            if ($note) {
                                $status = strtolower($note['status'] ?? $note->status ?? '');
                                $channel = strtoupper($note['channel'] ?? $note['notice_type'] ?? 'EMAIL');
                                
                                if ($status === 'sent') {
                                    $noticeStatus = 'Sent (' . $channel . ')';
                                } elseif ($status === 'skipped') {
                                    $noticeStatus = 'Skipped';
                                } elseif ($status === 'failed') {
                                    $noticeStatus = 'Failed';
                                } else {
                                    $noticeStatus = ucfirst($status ?: 'Unknown') . ' (' . $channel . ')';
                                }
                            }
                        @endphp
                        {{ $noticeStatus }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
