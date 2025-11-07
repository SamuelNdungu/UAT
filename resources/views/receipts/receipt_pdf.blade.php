@extends('layouts.pdf')

@section('content')
<style>
    body { 
        font-family: 'DejaVu Sans', Arial, sans-serif; 
        color: #02025a; 
        font-size: 11px;
        line-height: 1.3;
    }
    
    .receipt-section {
        margin-bottom: 4px;
        padding: 8px;
        border: 1px solid #b6bbff;
        border-radius: 4px;
        background-color: #ffffff;
    }
    
    .receipt-section p {
        margin: 0;
        font-size: 11px;
        color: #121958;
        line-height: 1.3;
    }
    
    .receipt-section p strong {
        color: #121958;
    }
    
    .footer {
        text-align: center;
        margin-top: 15px;
        font-size: 10px;
        color: #6c757d;
    }
    
    .text-center {
        text-align: center;
    }
    
    /* Table styles for better A4 fit */
    .table-borderless {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table-borderless td {
        padding: 6px;
        vertical-align: top;
    }
    
    .table-bordered {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }
    
    .table-bordered th,
    .table-bordered td {
        padding: 4px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        text-align: left;
    }
    
    .table-bordered th {
        background-color: rgba(2, 2, 90, 0.05);
        color: rgb(2, 2, 90);
        font-weight: bold;
    }
    
    .compact-table {
        font-size: 9px;
    }
    
    .compact-table th,
    .compact-table td {
        padding: 3px 4px;
    }
</style>

<div class="receipt">
    <!-- Title Row with Faded Borders -->
    <div class="text-center py-2 mb-3" style="border-top: 2px solid rgba(2, 2, 90, 0.3); border-bottom: 2px solid rgba(2, 2, 90, 0.3);">
        <h2 style="margin: 5px 0; text-align: center; font-size: 18px; color: #02025a;">Receipt</h2>
    </div>
    
    <!-- Two-column layout for Customer Details and Receipt Info -->
    <table class="table-borderless mb-2">
        <tr style="border: 1px solid rgba(0, 0, 0, 0.1);">
            <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                <!-- Customer Details -->
                <div class="receipt-section"> 
                    <p style="margin-bottom: 8px;"><strong>Code:</strong> {{ $payment->customer ? $payment->customer->customer_code : 'N/A' }}</p>
                    <p><strong>Name:</strong> {{ $payment->customer ? $payment->customer->customer_name : 'N/A' }}</p>
                </div>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 10px;">
                <!-- Receipt Info -->
                <div class="receipt-section">
                    <p style="margin-bottom: 8px;"><strong>Receipt No:</strong> {{ $payment->receipts->first()->receipt_number }}</p>
                    <p class="mb-0"><strong>Date Issued:</strong> {{ \Carbon\Carbon::parse($payment->receipt_date)->format('d-m-Y') }}</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Payment Details and Amount Section -->
    <div class="receipt-section"> 
        <table class="table-borderless mb-0" style="width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 10px; border-radius: 3px;">
                        <p class="mb-1" style="text-align: left; margin-bottom: 8px; color: #000080;"><strong>Payment Mode:</strong> {{ $payment->payment_method }}</p>
                        <p class="mb-1" style="text-align: left; margin-bottom: 8px; color: #000080;"><strong>Payment Reference:</strong> {{ $payment->payment_reference ?? 'N/A' }}</p>
                        <p class="mb-0" style="text-align: left; margin-bottom: 8px; color: #000080;"><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') }}</p>
                    </td>
                    <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 10px; border-radius: 3px;">
                        <p class="mb-1" style="text-align: left; margin-bottom: 8px; color: #000080;"><strong>Amount:</strong></p> 
                        <p class="mb-0" style="text-align: left; color: #000080; font-size: 14px; font-weight: bold;"><strong>KES {{ number_format($payment->payment_amount, 2) }}</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Allocation Details -->
    <div class="receipt-section">
        <h5 style="font-size: 12px; margin-bottom: 8px;">Allocation Details</h5>
        <div style="overflow: hidden;">
            <table class="table-bordered compact-table">
                <thead>
                    <tr>  
                        <th style="width: 16%;">File No.</th>
                        <th style="width: 16%;">Policy No.</th>
                        <th style="width: 16%;">Policy</th>
                        <th style="width: 16%;">Reg No</th>
                        <th style="width: 16%;">Receipt No.</th>
                        <th style="width: 16%;">Amount KES</th> 
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment->allocations as $allocation)
                        @if($allocation->allocation_amount > 0)
                            <tr>
                                <td>{{ $allocation->policy->fileno }}</td>
                                <td>{{ $allocation->policy->policy_no }}</td>
                                <td>{{ $allocation->policy->policy_type_name }}</td>
                                <td>{{ $allocation->policy->reg_no ?? 'N/A' }}</td>
                                <td>{{ $payment->receipts->first()->receipt_number }}</td>
                                <td>{{ number_format($allocation->allocation_amount, 2) }}</td> 
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Additional Notes -->
    <div class="receipt-section"> 
        <p><strong>Remarks:</strong> {{ $payment->payment_note }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your payment.</p>
        <p>Please retain this receipt for your records.</p>
    </div>
</div>
@endsection