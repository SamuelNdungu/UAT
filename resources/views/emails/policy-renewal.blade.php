<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #2d3748; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 20px; }
        .details { background: #f7fafc; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .button { display: inline-block; padding: 10px 20px; background: #3182ce; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #718096; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Policy Renewal Reminder</h2>
    </div>

    <p>Dear {{ $policy->customer_name }},</p>
    
    <p>This is a reminder that your policy is due for renewal.</p>

    <div class="details">
        <h3>Policy Details:</h3>
        <p><strong>File No:</strong> {{ $policy->fileno ?? 'N/A' }}</p>
        <p><strong>Customer Name:</strong> {{ $policy->customer_name }}</p>
        <p><strong>Policy No:</strong> {{ $policy->policy_no ?? 'N/A' }}</p>
        <p><strong>Policy Type:</strong> {{ $policy->policyType->type_name ?? ($policy->bus_type ?? 'N/A') }}</p>
        <p><strong>Description:</strong> {{ $policy->description ?? 'N/A' }}</p>
        <p><strong>Expiry Date:</strong> {{ $policy->end_date ? \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') : 'N/A' }}</p>
        <p><strong>Days Until Expiry:</strong> {{ $days }}</p>
    </div>

    <p>Please contact us to renew your policy.</p>
    
    

    <div class="footer">
        <p>Thank you,<br>{{ config('app.name') }}</p>
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>
</html>
