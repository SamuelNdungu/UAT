<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Policy Renewal Notice — 30 days</title>
</head>
<body>
    <p>Dear {{ optional($policy->customer)->first_name ?? $policy->customer_name ?? 'Customer' }},</p>

    <p>This is a friendly reminder that your policy (File No: {{ $policy->fileno }}, Policy No: {{ $policy->policy_no }}) will expire on <strong>{{ \Carbon\Carbon::parse($policy->end_date)->toFormattedDateString() }}</strong>, which is 30 days from today.</p>

    <p>Please contact us if you wish to renew or discuss options. If you have already started renewal, please ignore this message.</p>

    <p>Regards,<br/>{{ config('app.name') }}</p>
</body>
</html>
