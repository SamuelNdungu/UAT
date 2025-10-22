@extends('layouts.pdf')

@section('content')
<style>
    body { font-family: Arial, sans-serif; }
    .report-title { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
    .report-title h1 { margin:0; font-size:18px; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #000; padding:6px; text-align:left; font-size:10px; }
    th { background:#f2f2f2; }
</style>

    @php
        $__companyName = null;
        try {
            if (class_exists('\App\\Models\\CompanyData')) {
                $__cd = \App\Models\CompanyData::first();
                if ($__cd && !empty($__cd->company_name)) $__companyName = $__cd->company_name;
            }
        } catch (\Throwable $__e) { $__companyName = null; }
    @endphp
    <div class="report-title">
        <h1>Payments Report</h1>
        <div class="app-name">{{ $__companyName ?? config('app.name') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Receipt No.</th>
                <th>Customer Code</th>
                <th>Customer Name</th>
                <th>Payment Date</th>
                <th>Payment Amount</th>
                <th>Allocated Amount</th>
                <th>Remaining Amount</th>
                <th>Allocated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ optional($payment->receipts->first())->receipt_number ?? 'N/A' }}</td>
                    <td>{{ $payment->customer_code }}</td>
                    <td>{{ optional($payment->customer)->customer_name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                    <td>KES {{ number_format($payment->payment_amount, 2) }}</td>
                    <td>KES {{ number_format(optional($payment->receipts->first())->allocated_amount ?? 0, 2) }}</td>
                    <td>KES {{ number_format(optional($payment->receipts->first())->remaining_amount ?? $payment->payment_amount, 2) }}</td>
                    <td>{{ optional($payment->receipts->first())->allocated_amount > 0 ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection
