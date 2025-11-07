chinyerusem

<!DOCTYPE html>
<html>
<head>
    <title>Debtors List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Debtors List</h1>
    <table>
        <thead>
            <tr>
                <th>File No.</th>
                <th>Entry Date</th>
                <th>Name</th>
                <th>Policy Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reg.No</th>
                <th>Gross Premium</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Aging Band</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filteredPolicies as $policy)
            <tr>
                <td>{{ $policy->fileno }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->buss_date)->format('Y-m-d') }}</td>
                <td>{{ $policy->customer_name }}</td>
                <td>{{ $policy->policy_type_name }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                <td>{{ $policy->reg_no }}</td>
                <td>{{ number_format($policy->gross_premium, 2) }}</td>
                <td>{{ number_format($policy->paid_amount, 2) }}</td>
                <td>{{ number_format($policy->balance, 2) }}</td>
                <td>{{ $policy->aging_band }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
