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
                    <th>Endorsements</th> {{-- NEW --}}
                    <th>Adjusted Due</th>  {{-- NEW --}}
                    <th>Paid Amount</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($balances as $balance)
                @php
                    $gross = (float)($balance['gross_premium'] ?? 0);
                    $paid  = (float)($balance['paid_amount'] ?? 0);

                    // Helper to extract a numeric amount from various shapes, now including delta_net_premium and delta_gross_premium
                    $extractAmount = function($item) {
                        if (is_numeric($item)) {
                            return (float)$item;
                        }
                        if (is_array($item)) {
                            if (isset($item['delta_net_premium'])) return (float)$item['delta_net_premium'];
                            if (isset($item['delta_gross_premium'])) return (float)$item['delta_gross_premium'];
                            if (isset($item['premium_impact'])) return (float)$item['premium_impact'];
                            if (isset($item['amount'])) return (float)$item['amount'];
                            if (isset($item['value'])) return (float)$item['value'];
                        }
                        if (is_object($item)) {
                            // Eloquent model with accessor net_impact
                            if (isset($item->net_impact)) return (float)$item->net_impact;
                            if (isset($item->delta_net_premium)) return (float)$item->delta_net_premium;
                            if (isset($item->delta_gross_premium)) return (float)$item->delta_gross_premium;
                            if (isset($item->premium_impact)) return (float)$item->premium_impact;
                            if (isset($item->amount)) return (float)$item->amount;
                            if (isset($item->value)) return (float)$item->value;
                        }
                        return 0.0;
                    };

                    // Determine endorsement adjustment:
                    $endorsementSum = 0.0;

                    if (isset($balance['endorsement_sum'])) {
                        // Controller pre-computed sum (preferred)
                        $endorsementSum = (float)$balance['endorsement_sum'];
                    } else {
                        // 1) If a generic endorsements collection is provided, sum their net impact
                        if (isset($balance['endorsements']) && is_iterable($balance['endorsements'])) {
                            foreach ($balance['endorsements'] as $e) {
                                $endorsementSum += $extractAmount($e);
                            }
                        }

                        // 2) Otherwise, handle explicit additions (positive) and deletions (negative)
                        if (isset($balance['additions']) && is_iterable($balance['additions'])) {
                            foreach ($balance['additions'] as $a) {
                                $amt = $extractAmount($a);
                                $endorsementSum += abs($amt);
                            }
                        }

                        if (isset($balance['deletions']) && is_iterable($balance['deletions'])) {
                            foreach ($balance['deletions'] as $d) {
                                $amt = $extractAmount($d);
                                $endorsementSum -= abs($amt);
                            }
                        }
                    }

                    // Adjusted amount due = original gross + endorsements (endorsements may be negative for credits)
                    $adjustedDue = $gross + $endorsementSum;
                    $balanceAmount = $adjustedDue - $paid;
                @endphp
                <tr>
                    <td>{{ $balance['fileno'] }}</td>
                    <td>{{ $balance['type'] }}</td>
                    <td>KES {{ number_format($gross, 2) }}</td>
                    <td>
                        @if($endorsementSum == 0)
                            KES 0.00
                        @else
                            <span style="color: {{ $endorsementSum >= 0 ? '#28a745' : '#dc3545' }};">
                                KES {{ number_format($endorsementSum, 2) }}
                            </span>
                        @endif
                    </td>
                    <td>KES {{ number_format($adjustedDue, 2) }}</td>
                    <td>KES {{ number_format($paid, 2) }}</td>
                    <td><strong>KES {{ number_format($balanceAmount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p class="footer">Thank you for being with us!</p>
        <p class="footer">If you have any questions, feel free to <a href="mailto:support@example.com">contact our support team</a>.</p>
    </div>
</body>
</html>
