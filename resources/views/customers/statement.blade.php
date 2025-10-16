<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Statement</title>
    <style>
        /* Base Styles for DOMPDF Compatibility */
        body { 
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif; 
            font-size: 11px; 
            color: #333;
            line-height: 1.4;
        }

        /* --- Global Helpers --- */
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-primary { color: #007bff; } /* Modern Primary Color */

        /* --- Header: Logo and Contact Details --- */
        .header {
            margin-bottom: 25px;
            padding-bottom: 10px;
            /* Using a common color for a professional look */
            border-bottom: 3px solid #007bff; 
        }

        .header-grid {
            /* Simulating a grid/flex layout with table for reliable DOMPDF alignment */
            width: 100%;
            border-collapse: collapse;
        }
        .header-grid td {
            padding: 0;
            border: none;
            vertical-align: top;
        }

        /* Company Logo (Left Column) */
        .logo-container {
            width: 50%;
        }
        .logo-container img {
            max-width: 200px;
            height: auto;
        }

        /* Contact Details (Right Column) */
        .contact-details {
            width: 50%;
            font-size: 10px;
            text-align: right;
            padding-top: 5px;
        }
        .contact-details div {
            margin-bottom: 3px;
        }

        /* --- Document Title --- */
        .title-section {
            padding: 5px 0 10px 0;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
        }

        /* --- Customer/Statement Info Block --- */
        .customer-info { 
            margin-bottom: 25px; 
            padding: 10px;
            background-color: #f8f9fa; /* Light background for the info box */
            border-left: 5px solid #007bff; /* Primary color accent */
            font-size: 12px;
            line-height: 1.6;
        }
        .info-row {
            display: block; /* Ensure each info line is on a new line */
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px; /* Aligns the data points */
        }


        /* --- Transaction Table --- */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }

        /* Modern Table Headers */
        thead tr th {
            background-color: #007bff; /* Primary color background */
            color: #ffffff; /* White text */
            border: none;
            padding: 8px 6px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        /* Modern Table Body */
        tbody tr:nth-child(even) {
            background-color: #f8f9fa; /* Subtle row striping */
        }
        td { 
            border: 1px solid #eee; /* Lighter border color */
            padding: 6px; 
            text-align: left; 
            font-size: 11px;
        }
        tbody tr:hover {
            background-color: #e9ecef; /* Hover effect not visible in PDF but good practice */
        }

        /* --- Total Balance --- */
        .total-balance-box {
            text-align: right;
            margin-top: 15px;
            padding: 10px 15px;
            border-top: 3px solid #007bff;
            font-size: 14px;
            font-weight: bold;
            color: #1a1a1a;
        }

        /* --- Footer --- */
        .footer { 
            position: fixed; 
            bottom: 20px; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 9px; 
            color: #6c757d; /* Muted gray color */
        }
    </style>
</head>
<body>
    
    {{-- Company Header Section --}}
    <div class="header">
        <table class="header-grid">
            <tr>
                <td class="logo-container">
                    {{-- Note: Ensure your public path is correct based on your setup --}}
                    @php $logoPath = public_path('img/logo.png'); @endphp
                    @if(file_exists($logoPath))
                        {{-- Use the public path or inline base64 as you were doing, but ensure it works with DOMPDF --}}
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo">
                    @else
                        {{-- Fallback if logo file is not found at the expected location --}}
                        <div style="font-size: 16px; font-weight: bold; color: #007bff;">Company Name</div>
                    @endif
                </td>
                <td class="contact-details">
                    <div>P.O BOX 14657</div>
                    <div>Nairobi 00100 GPO</div>
                    <div><span class="fw-bold">Mobile:</span> +254 0722 270897</div>
                    <div><span class="fw-bold">Email:</span> info@emelyInsurance.co.ke</div> 
                </td>
            </tr>
        </table>
    </div>

    <div class="title-section text-center">
        Customer Statement
    </div>

    {{-- Customer and Period Information --}}
    <div class="customer-info">
        <span class="info-row">
            <span class="info-label">Customer:</span> 
            {{ $customer->customer_type === 'Corporate' ? $customer->corporate_name : ($customer->first_name.' '.$customer->last_name.' '.$customer->surname) }}
        </span>
        <span class="info-row">
            <span class="info-label">Code:</span> {{ $customer->customer_code ?? '' }} &nbsp;&nbsp;
            <span class="info-label">Contact:</span> {{ $customer->phone ?? '' }}
        </span>
        <span class="info-row">
            <span class="info-label">Email:</span> {{ $customer->email ?? '' }}
        </span>
        <span class="info-row">
            <span class="info-label">Address:</span> {{ $customer->address ?? '' }}
        </span>
        <span class="info-row">
            <span class="info-label">Period:</span> {{ $startDate ?? 'N/A' }} to {{ $endDate ?? 'N/A' }}
        </span>
    </div>

    {{-- Transaction Table --}}
    @if($transactions->isEmpty())
        <p>No transactions available for this customer in the selected period.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:80px;" class="text-left">Date</th>
                    <th class="text-left">Description</th>
                    <th style="width:100px;" class="text-left">Policy No.</th>
                    <th style="width:100px;" class="text-right">Debit</th>
                    <th style="width:100px;" class="text-right">Credit</th>
                    <th style="width:120px;" class="text-right">Outstanding Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $t)
                    <tr>
                        <td>{{ $t->date_formatted }}</td>
                        <td>{{ $t->description }}</td>
                        <td>{{ $t->policy_no }}</td>
                        <td class="text-right">{{ $t->debit ? number_format($t->debit,2) : '' }}</td>
                        <td class="text-right">{{ $t->credit ? number_format($t->credit,2) : '' }}</td>
                        <td class="text-right fw-bold">{{ number_format($t->running,2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-balance-box">
            <span>TOTAL OUTSTANDING BALANCE:</span> 
            <span class="text-primary">{{ number_format($transactions->last()->running ?? 0, 2) }}</span>
        </div>
    @endif

    {{-- Global Footer --}}
    <div class="footer">
        Document Generated: {{ $generatedAt }} | This statement is automatically generated and may not require a signature.
    </div>

    {{-- DOMPDF page numbering script (keep this at the bottom) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            // Adjusted position slightly for better fit with the new footer style
            $pdf->page_text(700, 20, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 9, array(0,0,0));
        }
    </script>
</body>
</html>