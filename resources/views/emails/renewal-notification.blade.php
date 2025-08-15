<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Policy Renewal Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .policy-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .policy-item {
            margin-bottom: 10px;
        }
        .highlight {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #666;
        }
        .cta-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Policy Renewal Notification</h2>
    </div>

    <p>Dear {{ $policy->customer_name }},</p>

    <p>We hope this email finds you well. This is a friendly reminder that your insurance policy is due for renewal.</p>

    <div class="policy-details">
        <h3>Policy Details:</h3>
        <div class="policy-item">
            <strong>Policy Number:</strong> {{ $policy->policy_no }}
        </div>
        <div class="policy-item">
            <strong>Policy Type:</strong> {{ $policy->policy_type_name }}
        </div>
        <div class="policy-item">
            <strong>Coverage:</strong> {{ $policy->coverage }}
        </div>
        <div class="policy-item">
            <strong>Current End Date:</strong> {{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}
        </div>
        <div class="policy-item">
            <strong>Sum Insured:</strong> {{ number_format($policy->sum_insured, 2) }}
        </div>
        @if($policy->reg_no)
        <div class="policy-item">
            <strong>Registration Number:</strong> {{ $policy->reg_no }}
        </div>
        @endif
    </div>

    <p>To ensure continuous coverage and protection, please arrange for the renewal of your policy before the expiry date. Our team is ready to assist you with the renewal process and answer any questions you may have.</p>

    <p class="highlight">Please note: Your policy will expire on {{ \Carbon\Carbon::parse($policy->end_date)->format('d-m-Y') }}.</p>

    <p>Benefits of timely renewal:</p>
    <ul>
        <li>Uninterrupted insurance coverage</li>
        <li>Protection against potential gaps in coverage</li>
        <li>Maintenance of any accumulated benefits</li>
    </ul>

    <p>For any queries or assistance with the renewal process, please don't hesitate to contact us:</p>
    <p>
        Phone: [Your Company Phone]<br>
        Email: [Your Company Email]<br>
        Working Hours: [Your Working Hours]
    </p>

    <a href="{{ route('policies.show', $policy->id) }}" class="cta-button">View Policy Details</a>

    <div class="footer">
        <p>Best regards,<br>The Insurance Team</p>
        <p><small>This is an automated message. Please do not reply to this email.</small></p>
    </div>
</body>
</html>