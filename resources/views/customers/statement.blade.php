@extends('layouts.pdf')

@section('content')
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
    .text-primary { color: #007bff; }

    /* --- Title and Header helpers --- */
    .title-section {
        padding: 5px 0 10px 0;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 18px;
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
    .info-row { display: block; }
    .info-label { font-weight: bold; display: inline-block; width: 100px; }

    /* --- Transaction Table --- */
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    thead tr th {
        background-color: #007bff;
        color: #ffffff;
        border: none;
        padding: 8px 6px;
        text-transform: uppercase;
        font-size: 10px;
    }
    tbody tr:nth-child(even) { background-color: #f8f9fa; }
    td { border: 1px solid #eee; padding: 6px; text-align: left; font-size: 11px; }

    .total-balance-box { text-align: right; margin-top: 15px; padding: 10px 15px; border-top: 3px solid #007bff; font-size: 14px; font-weight: bold; color: #1a1a1a; }

    .footer { text-align: center; font-size: 9px; color: #6c757d; margin-top: 10px; }
</style>

<div class="title-section text-center">Customer Statement</div>
    {{-- Customer and Period Information --}}
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

@endsection