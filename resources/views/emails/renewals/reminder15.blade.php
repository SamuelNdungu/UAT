<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Policy Renewal Reminder — 15 days</title>
</head>
<body>
    <p>Dear {{ optional($policy->customer)->first_name ?? $policy->customer_name ?? 'Customer' }},</p>

    <p>This is a reminder that your policy (File No: {{ $policy->fileno }}, Policy No: {{ $policy->policy_no }}) expires on <strong>{{ \Carbon\Carbon::parse($policy->end_date)->toFormattedDateString() }}</strong> (15 days remaining).</p>

    <p>Our records show the policy is still active. Please contact us to complete renewal if required.</p>

    <p>Regards,<br/>{{ config('app.name') }}</p>
</body>
</html>
