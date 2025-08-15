<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color:rgb(2, 2, 90); /* Dark blue color */
        }

        .logo img {
            width: 250px; /* Adjust the width of your logo */
        }

        .receipt-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .receipt {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background-color: #ffffff;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .receipt-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-header table tr {
            width: 100%;
        }

        .receipt-header table td {
            padding: 0;
            vertical-align: middle;
        }

        .receipt-header table .logo {
            text-align: left;
        }

        .receipt-header table .title {
            text-align: center;
        }

        .receipt-header table .receipt-info {
            text-align: right;
        }

        .receipt-section {
            margin-bottom: 5px;
            padding: 10px;
            border: 1px rgb(182, 187, 255);
            border-radius: 5px;
            background-color:rgb(255, 255, 255);
        }

        .receipt-section p {
            margin: 0;
            font-size: 1rem;
            color:rgb(18, 25, 88);
        }

        .receipt-section p strong {
            color:rgb(18, 25, 88);
        }

        

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <!-- Logo Row -->
            <div class="text-center mb-3">
                <img src="{{ public_path('img/logo.png') }}" alt="Logo" style="width: 200px;">
            </div>
            
            <!-- Title Row with Faded Borders -->
            <div class="text-center py-3 mb-4" style="border-top: 2px solid rgba(2, 2, 90, 0.3); border-bottom: 2px solid rgba(2, 2, 90, 0.3);">
                <h2 style="margin: 8px 0; text-align: center;">Receipt</h2>
            </div>
            
            <!-- Two-column layout for Customer Details and Receipt Info -->
            <table class="table table-borderless mb-3" style="width: 100%;">
                <tr style="border: 1px solid rgba(0, 0, 0, 0.1); ">
                    <td class="pe-4" style="width: 50%; vertical-align: top;">
                        <!-- Customer Details -->
                        <div class="receipt-section"> 
                        <p style="margin-bottom: 12px;" ><strong>Code:</strong> {{ $payment->customer ? $payment->customer->customer_code : 'N/A' }}</p>
                      
                            <p ><strong>Name:</strong> {{ $payment->customer ? $payment->customer->customer_name : 'N/A' }}</p>
                           </div>
                    </td>
                    <td class="ps-4" style="width: 50%; vertical-align: top;">
                        <!-- Receipt Info -->
                        <div class="receipt-section">
                            <p style="margin-bottom: 12px;"><strong>Receipt No:</strong> {{ $payment->receipts->first()->receipt_number }}</p>
                            <p class="mb-0"><strong>Date Issued:</strong> {{ \Carbon\Carbon::parse($payment->receipt_date)->format('d-m-Y') }}</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment Details and Amount Section -->
        <div class="receipt-section"> 
            <table class="table table-borderless mb-0" style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 15px; border-radius: 4px;">
                            <p class="mb-1" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Payment Mode:</strong> {{ $payment->payment_method }}</p>
                            <p class="mb-1" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Payment Reference:</strong> {{ $payment->payment_reference ?? 'N/A' }}</p>
                            <p class="mb-0" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Payment Date:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->format('d-m-Y') }}</p>
                        </td>
                        <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 15px; border-radius: 4px;">
                            <p class="mb-1" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Amount:</strong></p> 
                            <p class="mb-0" style="text-align: left; color: #000080; font-size: 1.3rem;"><strong>KES {{ number_format($payment->payment_amount, 2) }}</strong></p>
                        </td>
  
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Allocation Details -->
        <div class="receipt-section">
            <h5 style="font-size: 1.1rem;">Allocation Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped"  style="width: 100%; font-size: 0.8rem; border: 1px solid rgba(0, 0, 0, 0.1); ">
                    <thead  style="background-color: rgba(2, 2, 90, 0.05);">
                        <tr>  
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">File No.</th>
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">Policy No.</th>
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">Policy </th>
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">Reg No</th>
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">Receipt No.</th>
                            <th style="padding: 6px 6px; width: 16.66%; color: rgb(2, 2, 90);">Amount KES</th> 
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->allocations as $allocation)
                        @if($allocation->allocation_amount > 0 && $payment->receipts->first()->receipt_number === $payment->receipts->first()->receipt_number)
                        <tr>
                            <td style="padding: 6px 6px;">{{ $allocation->policy->fileno }}</td>
                            <td style="padding: 6px 6px;">{{ $allocation->policy->policy_no }}</td>
                            <td style="padding: 6px 6px;">{{ $allocation->policy->policy_type_name }}</td>
                            <td style="padding: 6px 6px;">{{ $allocation->policy->reg_no ?? 'N/A' }}</td>
                            <td style="padding: 6px 6px;">{{ $payment->receipts->first()->receipt_number }}</td>
                            <td style="padding: 6px 6px;"> {{ number_format($allocation->allocation_amount, 2) }}</td> 
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
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>