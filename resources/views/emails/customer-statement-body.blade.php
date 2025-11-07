<!doctype html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <p>Dear {{ $customer->first_name ?? $customer->corporate_name ?? 'Customer' }},</p>

    <p>Please find attached your customer statement. If you have any questions, reply to this email or contact our support team.</p>

    <p>Regards,<br>{{ config('app.name') }}</p>
</body>
</html>
