<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Balance Information</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        p {
            color: #555;
            text-align: center;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007BFF; /* Bootstrap Primary color */
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2; /* Light gray for even rows */
        }
        tr:hover {
            background-color: #e2e6ea; /* Light gray on hover */
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        .footer a {
            color: #007BFF;  
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="header">
            <div class="logo">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo.png'))) }}" alt="Logo">
            </div>
            <div class="print-date">
                Print Date: {{ \Carbon\Carbon::now()->format('d-m-Y') }}
            </div>
        </div>
        <h1>Dear, {{ $customerName }}!</h1>
        <p>Here are your policy balances:</p>

        <table>
            <thead>
                <tr>
                    <th>File No.</th>
                    <th>Policy Type</th>                    
                    <th>Gross Premium</th>
                    <th>Paid Amount</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balances as $balance)
                <tr> 
                    <td>{{ $balance['fileno'] }}</td> <!-- Displaying File Number -->
                    <td>{{ $balance['type'] }}</td> <!-- Displaying Policy Type Name -->
                    <td>KES {{ number_format($balance['gross_premium'], 2) }}</td> <!-- Displaying Gross Premium -->
                    <td>KES {{ number_format($balance['paid_amount'], 2) }}</td> <!-- Displaying Paid Amount -->
                    <td>KES {{ number_format($balance['balance'], 2) }}</td> <!-- Displaying Balance -->
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footer">Thank you for being with us!</p>
        <p class="footer">If you have any questions, feel free to <a href="mailto:support@example.com">contact our support team</a>.</p>
    </div>
</body>
</html>
