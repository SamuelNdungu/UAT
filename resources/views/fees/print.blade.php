<!DOCTYPE html>
<html>
<head>
    <title>Invoice</title>
    @include('partials.company_logo')
    @if(!empty($companyLogoUrl))
        <link rel="icon" href="{{ $companyLogoUrl }}" type="image/png">
    @else
        <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">
    @endif
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color:rgb(2, 2, 90);
        }

        .logo img {
            width: 250px;
            padding: 10px;
        }

        .invoice-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }

        .invoice {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background-color: #ffffff;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .invoice-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-header table tr {
            width: 100%;
        }

        .invoice-header table td {
            padding: 0;
            vertical-align: middle;
        }

        .invoice-header table .logo {
            text-align: left;
        }

        .invoice-header table .title {
            text-align: center;
        }

        .invoice-header table .invoice-info {
            text-align: right;
        }

        .invoice-section {
            margin-bottom: 5px;
            padding: 10px;
            border: 1px rgb(182, 187, 255);
            border-radius: 5px;
            background-color:rgb(255, 255, 255);
        }

        .invoice-section p {
            margin: 0;
            font-size: 1rem;
            color:rgb(18, 25, 88);
        }

        .invoice-section p strong {
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

<div class="invoice-container">
    <div class="invoice">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <!-- Logo Row -->
            <div class="text-center mb-5" style="margin-bottom: 1rem !important; margin-top: 1rem !important;">
                <img src="{{ public_path('img/logo.png') }}" alt="Logo" style="width: 250px;">
            </div>
            
            <!-- Title Row with Faded Borders -->
            <div class="text-center py-3 mb-4" style="border-top: 2px solid rgba(2, 2, 90, 0.3); border-bottom: 2px solid rgba(2, 2, 90, 0.3);">
                <h2 style="margin: 8px 0; text-align: center;">INVOICE</h2>
            </div>
            
            <!-- Two-column layout for Customer Details and Invoice Info -->
            <table class="table table-borderless mb-3" style="width: 100%;">
                <tr style="border: 1px solid rgba(0, 0, 0, 0.1); ">
                    <td class="pe-4" style="width: 50%; vertical-align: top;">
                        <!-- Customer Details -->
                        <div class="invoice-section"> 
                            <p style="margin-bottom: 12px;" ><strong>Code:</strong> {{ $fee->customer->customer_code }}</p>
                            <p ><strong>Name:</strong> {{ $fee->customer->customer_name }}</p>
                        </div>
                    </td>
                    <td class="ps-4" style="width: 50%; vertical-align: top;">
                        <!-- Invoice Info -->
                        <div class="invoice-section">
                        <p class="mb-1" style="margin-bottom: 12px;"><strong>Status:</strong> 
                                                   
                        <span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; 
                                    @if($fee->status == 'paid')
                                        background-color: #d4edda; color: #155724;
                                    @elseif($fee->status == 'overdue')
                                        background-color: #f8d7da; color: #721c24;
                                    @else
                                        background-color: #fff3cd; color: #856404;
                                    @endif">
                                    {{ ucfirst($fee->status) }}
                                </span>
                            <p style="margin-bottom: 12px;"><strong>Invoice No:</strong> {{ $fee->invoice_number }}</p>
                            <p class="mb-2" style="margin-bottom: 12px;"><strong>Date:</strong> {{ \Carbon\Carbon::parse($fee->date)->format('d-m-Y') }}</p>

                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Invoice Details Section -->
        <div class="invoice-section"> 
            <table class="table table-borderless mb-0" style="width: 100%;">
                <tbody>
                    <tr>
                        <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 15px; border-radius: 4px;">
                            <p class="mb-1" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Description:</strong></p>
                            <p class="mb-0" style="text-align: left; margin-bottom: 12px; color: #000080;">{{ $fee->description }}</p>
                        </td>
                        <td style="width: 50%; border: 1px solid rgba(0, 0, 0, 0.1); text-align: left; padding: 15px; border-radius: 4px;">
                            <p class="mb-1" style="text-align: left; margin-bottom: 12px; color: #000080;"><strong>Amount:</strong></p> 
                            <p class="mb-0" style="text-align: left; color: #000080; font-size: 1.3rem;"><strong>KES {{ number_format($fee->amount, 2) }}</strong></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business.</p>
            <p>This is a computer-generated invoice. No signature is required.</p>
        </div>
    </div>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>