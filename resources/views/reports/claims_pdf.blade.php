@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html>
<head>
    <title>Claims Report</title>
    <style>
        body {
            font-size: 9px; /* Adjust the base font size for the entire document */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px; /* Adjust font size specifically for the table */
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        h1 {
            text-align: center;
            font-size: 16px; /* Adjust the font size for the header */
        }
    </style>
</head>
<body>
    <h1>Claims Report</h1>
    <table>
        <thead>
            <tr>
                <th>Claim ID</th>
                <th>Customer Name</th>
                <th>File No</th>
                <th>Policy Number</th>
                <th>Claim Amount</th>
                <th>Status</th>
                <th>Date Filed</th>
                <th>Date of Loss</th>
                <th>Type of Loss</th>
                <th>Policy Type</th>
                <th>Customer Code</th>
            </tr>
        </thead>
        <tbody>
            @foreach($claims as $claim)
                <tr>
                    <td>{{ $claim->claim_number }}</td>
                    <td>
                        @if($claim->customer->customer_type === 'Individual')
                            {{ $claim->customer->first_name }} {{ $claim->customer->last_name }} {{ $claim->customer->surname }}
                        @elseif($claim->customer->customer_type === 'Corporate')
                            {{ $claim->customer->corporate_name }}
                        @else
                            {{ __('Unknown Customer Type') }}
                        @endif
                    </td>
                    <td>{{ $claim->fileno }}</td>
                    <td>{{ $claim->policy_no }}</td>
                    <td>{{ $claim->amount }}</td>
                    <td>{{ $claim->status }}</td>
                    <td>{{ $claim->created_at->format('Y-m-d') }}</td>
                    <td>{{ $claim->loss_date ? Carbon::parse($claim->loss_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $claim->type_of_loss }}</td>
                    <td>{{ $claim->policy->type_of_policy ?? 'N/A' }}</td>
                    <td>{{ $claim->customer_code }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
