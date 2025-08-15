<!DOCTYPE html>
<html>
<head>
    <title>Debtors List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Base font size */
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px; /* Space between header and table */
        }
        .header img {
            width: 100px; /* Adjust the width of your logo */
        }
        h1 {
            text-align: center;
            font-size: 16px; /* Larger font size for the title */
            margin: 0; /* Remove default margin */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px; /* Smaller font size for the table */
        }
        th, td {
            border: 1px solid #000;
            padding: 4px; /* Reduced padding for table cells */
            text-align: left;
            white-space: nowrap; /* Prevent text from wrapping */
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold; /* Bold font for headers */
        }
    </style>
</head>
<body>
<div class="header">
    <img src="{{ public_path('img/logo.png') }}" alt="Logo"> <!-- Corrected the path -->
    <h1>Debtors List</h1>
</div>

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
            </tr>
        </thead>
        <tbody>
            @foreach($filteredPolicies as $policy)
            <tr>
                <td>{{ $policy->fileno }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->buss_date)->format('Y-m-d') }}</td>
                <td>{{ $policy->customer_name }}</td>
                <td>{{ $policy->policy_type_name ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d') }}</td>
                <td>{{ \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d') }}</td>
                <td>{{ $policy->reg_no }}</td>
                <td>{{ number_format($policy->gross_premium, 2) }}</td>
                <td>{{ number_format($policy->paid_amount, 2) }}</td>
                <td>{{ number_format($policy->gross_premium - $policy->paid_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align: right;"><strong>Totals:</strong></td>
                <td><strong>{{ number_format($totals['gross_premium'], 2) }}</strong></td>
                <td><strong>{{ number_format($totals['paid_amount'], 2) }}</strong></td>
                <td><strong>{{ number_format($totals['due_amount'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
