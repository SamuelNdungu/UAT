<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statement of Account</title>
    <style>
        /* Keep styles email-friendly and simple */
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; margin-bottom: 12px; }
        .customer-info { background:#f8f9fa; padding:8px; margin-bottom:12px; }
        table { width:100%; border-collapse: collapse; }
        th { background:#007bff; color:#fff; padding:6px; font-size:12px; }
        td { border:1px solid #eee; padding:6px; font-size:12px; }
        .text-right { text-align:right; }
        .fw-bold { font-weight:bold; }
        .total { margin-top:12px; text-align:right; font-weight:bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Statement of Account</h2>
    </div>

    <div class="customer-info">
        <div><strong>Customer:</strong> {{ $customer->customer_type === 'Corporate' ? ($customer->corporate_name ?? '') : trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')) }}</div>
        <div><strong>Code:</strong> {{ $customer->customer_code ?? '' }} | <strong>Email:</strong> {{ $customer->email ?? '' }}</div>
        <div><strong>Period:</strong> {{ $startDate ?? 'N/A' }} to {{ $endDate ?? 'N/A' }}</div>
    </div>

    {{-- Use same table markup as PDF view but keep styles email-friendly --}}
    <table>
        <thead>
            <tr>
                <th style="width:80px;">Date</th>
                <th>Description</th>
                <th style="width:100px;">Policy No.</th>
                <th style="width:100px;" class="text-right">Debit</th>
                <th style="width:100px;" class="text-right">Credit</th>
                <th style="width:120px;" class="text-right">Outstanding Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
                <tr>
                    <td>{{ $t->date_formatted }}</td>
                    <td>{{ $t->description }}</td>
                    <td>{{ $t->policy_no }}</td>
                    <td class="text-right">{{ $t->debit ? number_format($t->debit,2) : '' }}</td>
                    <td class="text-right">{{ $t->credit ? number_format($t->credit,2) : '' }}</td>
                    <td class="text-right fw-bold">{{ number_format($t->running,2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">TOTAL OUTSTANDING BALANCE: {{ number_format($transactions->last()->running ?? 0, 2) }}</div>

    <div style="margin-top:12px;font-size:11px;color:#666">Document Generated: {{ $generatedAt }}</div>
</body>
</html>
