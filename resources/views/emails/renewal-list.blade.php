@extends('layouts.email')

@section('content')
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 30px; background: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h1 style="color: #003366; margin: 0 0 10px 0; font-size: 28px;">Monthly Policy Renewal List</h1>
        <h2 style="color: #2c5282; margin: 0 0 15px 0; font-size: 22px; font-weight: 500;">{{ $month }}</h2>
        <div style="background: white; display: inline-block; padding: 8px 20px; border-radius: 20px; margin: 5px 0;">
            <p style="color: #4a5568; margin: 0; font-size: 14px; font-weight: 500;">
                Generated on: {{ now()->format('F j, Y \a\t g:i A') }}
            </p>
        </div>
    </div>

    <div style="margin: 30px 0; padding: 20px; background: #f0f4f8; border-radius: 5px; border-left: 4px solid #2c5282;">
        <h2 style="color: #2c5282; margin-top: 0;">Hello, {{ $agent->name }}</h2>
        <p>Please find attached the list of policies expiring in <strong>{{ $month }}</strong> that are assigned to you.</p>
        
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 5px; border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0; color: #2c5282;">Summary</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                <div style="flex: 1; min-width: 200px; background: #f7fafc; padding: 15px; border-radius: 5px; border-left: 4px solid #4299e1;">
                    <div style="font-size: 14px; color: #4a5568; margin-bottom: 5px;">Total Policies</div>
                    <div style="font-size: 24px; font-weight: bold; color: #2b6cb0;">{{ $policies->count() }}</div>
                </div>
                <div style="flex: 1; min-width: 200px; background: #f7fafc; padding: 15px; border-radius: 5px; border-left: 4px solid #48bb78;">
                    <div style="font-size: 14px; color: #4a5568; margin-bottom: 5px;">Total Premium</div>
                    <div style="font-size: 24px; font-weight: bold; color: #2f855a;">KSH {{ number_format($policies->sum('gross_premium'), 2) }}</div>
                </div>
            </div>
        </div>

        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <p>Attached to this email, you'll find an Excel file containing the complete list of policies. The file includes all the necessary details for follow-up.</p>
            <p>Please review the list and take the necessary actions to ensure timely renewals. The "Notice Status" and "Actions" columns are included for your tracking purposes.</p>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; text-align: center; font-size: 14px; color: #718096;">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you have any questions or need assistance, please contact the support team.</p>
    </div>
</div>
@endsection
