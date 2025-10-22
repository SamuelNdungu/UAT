<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Policy Renewal Notice</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;color:#333}
        .header{background:#007bff;color:#fff;padding:10px;text-align:center}
        .content{padding:12px}
        .btn{display:inline-block;padding:8px 12px;background:#28a745;color:#fff;text-decoration:none;border-radius:4px}
        .small{font-size:12px;color:#666}
        table{width:100%;border-collapse:collapse;margin-top:8px}
        th,td{border:1px solid #eee;padding:6px;text-align:left}
    </style>
</head>
<body>
    <div class="header"><h3>Policy Renewal Notice</h3></div>
    <div class="content">
        <p>Dear {{ $customer->corporate_name ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '') . ' ' . ($customer->surname ?? '')) }},</p>

        <p>This is a reminder that your policy is due for renewal.</p>

        @foreach($policies as $p)
            <div style="margin-bottom:12px;padding:8px;border:1px solid #eee;background:#fbfbfb">
                <p><strong>Policy Details:</strong></p>
                <p>File No: {{ $p['fileno'] ?? ($p['file_no'] ?? '') }}</p>
                <p>Customer Name: {{ $customer->corporate_name ?? trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '') . ' ' . ($customer->surname ?? '')) }}</p>
                <p>Policy No: {{ $p['policy_no'] ?? '' }}</p>
                <p>Policy Type: {{ $p['coverage'] ?? ($p['policy_type'] ?? '') }}</p>
                <p>Description: {{ $p['description'] ?? ($p['location'] ?? '') }}</p>
                <p>Expiry Date: {{ isset($p['end_date']) ? \Carbon\Carbon::parse($p['end_date'])->toDateString() : '' }}</p>
            </div>
        @endforeach

        <p>Please contact us to renew your policy.</p>

        <p>Thank you,</p>
        <p>{{ config('app.name') }}</p>

        <p class="small">This is an automated reminder generated on {{ $generatedAt }}.</p>
    </div>
</body>
</html>
