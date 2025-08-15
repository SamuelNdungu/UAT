<!DOCTYPE html>
<html>
<head>
    <title>Leads Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; color: #333; }
        .header { margin-bottom: 20px; }
        .date { text-align: right; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Leads Report</h2>
        <div class="date">Generated on: {{ date('Y-m-d H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Lead Type</th>
                <th>Name/Company</th>
                <th>Contact</th>
                <th>Policy Type</th>
                <th>Deal Size</th>
                <th>Probability</th>
                <th>Deal Status</th>
                <th>Next Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $lead)
            <tr>
                <td>{{ $lead->lead_type }}</td>
                <td>
                    @if($lead->lead_type === 'Corporate')
                        {{ $lead->corporate_name }}
                    @else
                        {{ $lead->first_name }} {{ $lead->last_name }}
                    @endif
                </td>
                <td>
                    {{ $lead->email }}<br>
                    {{ $lead->mobile }}
                </td>
                <td>{{ $lead->policy_type }}</td>
                <td>{{ number_format($lead->deal_size, 2) }}</td>
                <td>{{ $lead->probability }}%</td>
                <td>{{ $lead->deal_status }}</td>
                <td>{{ $lead->next_action }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>