<!DOCTYPE html>
<html>
<head>
    <title>Payments Report</title>
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .app-name {
            margin: 0;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            white-space: nowrap; /* Prevent text from wrapping */
            font-size: 10px; /* Reduce font size */
        }
        th {
            background-color: #f2f2f2; /* Optional: Add background color to headers */
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payments Report</h1>
        <div class="app-name">{{ config('app.name') }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Receipt No.</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Payment Date</th>
                <th>Payment Amount</th>
                <th>Allocated Amount</th>
                <th>Remaining Amount</th>
                <th>Allocated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ optional($payment->receipts->first())->receipt_number ?? 'N/A' }}</td>
                    <td>{{ $payment->customer_code }}</td>
                    <td>{{ optional($payment->customer)->customer_name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                    <td>KES {{ number_format($payment->payment_amount, 2) }}</td>
                    <td>KES {{ number_format(optional($payment->receipts->first())->allocated_amount ?? 0, 2) }}</td>
                    <td>KES {{ number_format(optional($payment->receipts->first())->remaining_amount ?? $payment->payment_amount, 2) }}</td>
                    <td>{{ optional($payment->receipts->first())->allocated_amount > 0 ? 'Yes' : 'No' }}</td> <!-- Indicating allocated status -->
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
