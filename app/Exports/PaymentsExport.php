<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $payments = Payment::select(
            'payments.id',
            DB::raw("CONCAT(customers.first_name, ' ', customers.last_name, ' ', customers.surname) AS customer_full_name"),
            'customers.corporate_name',
            'payments.payment_date',
            'payments.payment_amount',
            DB::raw("COALESCE(SUM(allocations.allocation_amount), 0) AS allocated_amount"),
            DB::raw("COALESCE(SUM(receipts.remaining_amount), payments.payment_amount) AS remaining_amount")
        )
        ->join('customers', 'payments.customer_code', '=', 'customers.customer_code')
        ->leftJoin('receipts', 'payments.id', '=', 'receipts.payment_id')
        ->leftJoin('allocations', 'payments.id', '=', 'allocations.payment_id')
        ->groupBy('payments.id', 'customer_full_name', 'customers.corporate_name', 'payments.payment_date', 'payments.payment_amount')
        ->get();

        // Map the data to include the necessary columns
        return $payments->map(function ($payment) {
            return [
                'ID' => $payment->id,
                'Receipt No.' => $payment->receipts->first()->receipt_number ?? 'N/A',
                'Customer' => $payment->corporate_name ?: $payment->customer_full_name,
                'Payment Date' => $payment->payment_date,
                'Payment Amount' => number_format($payment->payment_amount, 2),
                'Allocated Amount' => number_format($payment->allocated_amount, 2),
                'Remaining Amount' => number_format($payment->remaining_amount, 2),
                'Actions' => 'N/A', // Actions are not typically exported to Excel
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Receipt No.',
            'Customer',
            'Payment Date',
            'Payment Amount',
            'Allocated Amount',
            'Remaining Amount',
            'Actions',
        ];
    }
}
