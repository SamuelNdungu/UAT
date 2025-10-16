<!DOCTYPE html>
<html>
<head>
    <title>Policy Renewal Notification</title>
</head>
<body>
    <p>Dear {{ $policy->customer->customer_name ?? $policy->customer_name ?? '' }},</p>
    <p>This is a reminder that your policy is due for renewal.</p>
    
    <h2>Policy Details:</h2>
    <ul>
        <li><strong>File No:</strong> {{ $policy->fileno }}</li>
        <li><strong>Customer Name:</strong> {{ $policy->customer->customer_name ?? $policy->customer_name ?? '' }}</li>
        <li><strong>Policy No:</strong> {{ $policy->policy_no }}</li>
        <li><strong>Policy Type:</strong> {{ $policy->policyType->type_name ?? ($policy->policy_type_id ?? '') }}</li>
        @if(in_array($policy->policy_type_id, [35, 36, 37]))
            <li><strong>Vehicle Registration:</strong> {{ $policy->reg_no }}</li>
            <li><strong>Make:</strong> {{ $policy->make }}</li>
            <li><strong>Model:</strong> {{ $policy->model }}</li>
        @else
            <li><strong>Description:</strong> {{ $policy->description }}</li>
        @endif
        <li><strong>Expiry Date:</strong> {{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}</li>
    </ul>

    <p>Please contact us to renew your policy.</p>

    <p>Thank you,</p>
    <p>Your Insurance Company</p>
</body>
</html>
