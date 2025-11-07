<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Renewal Notice</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { font-size: 24px; font-weight: bold; color: #0056b3; margin-bottom: 20px; }
        .content p { margin: 0 0 10px; }
        .policy-details { border-collapse: collapse; width: 100%; margin: 20px 0; }
        .policy-details th, .policy-details td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .policy-details th { background-color: #f2f2f2; }
        .footer { margin-top: 20px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Policy Renewal Notice
        </div>
        <div class="content">
            <p>Dear {{ $customer->customer_name ?? ($customer->first_name . ' ' . $customer->last_name) }},</p>
            <p>This is a friendly reminder that your policy is due for renewal soon. Please review the details below and contact us to proceed with the renewal.</p>

            <table class="policy-details">
                <tr>
                    <th>Policy Number</th>
                    <td>{{ $policy->policy_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Insured Item</th>
                    <td>{{ $policy->insured ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Expiry Date</th>
                    <td>{{ $policy->end_date ? $policy->end_date->format('F j, Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Gross Premium</th>
                    <td>{{ number_format($policy->gross_premium ?? 0, 2) }}</td>
                </tr>
            </table>

            <p>To renew your policy or if you have any questions, please do not hesitate to contact us.</p>
            <p>Thank you for your continued trust in us.</p>
        </div>
        <div class="footer">
            <p>Sincerely,<br>{{ config('app.name', 'Your Insurance Company') }}</p>
        </div>
    </div>
</body>
</html>
