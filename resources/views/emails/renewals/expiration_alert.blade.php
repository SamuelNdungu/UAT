<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Policy Expiration Alert — Today</title>
</head>
<body>
    <p>Dear {{ optional($policy->customer)->first_name ?? $policy->customer_name ?? 'Customer' }},</p>

    <p>Your policy (File No: {{ $policy->fileno }}, Policy No: {{ $policy->policy_no }}) expires today (<strong>{{ \Carbon\Carbon::parse($policy->end_date)->toFormattedDateString() }}</strong>).</p>

    <p>This alert is sent because the policy status still indicates it is active and no renewal/cancellation was recorded. If you have already renewed or cancelled, please disregard. Otherwise, contact us immediately to discuss next steps.</p>

    <p>Regards,<br/>{{ config('app.name') }}</p>
</body>
</html>
