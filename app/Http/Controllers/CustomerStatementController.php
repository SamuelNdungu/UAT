<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Policy;
use App\Models\Payment;
use App\Models\CompanyData;  
use PDF;

class CustomerStatementController extends Controller
{
    // Define precision for financial calculations (2 decimal places)
    const PRECISION = 2;

    public function generate($id, Request $request)
    {
        // Set BCMath scale for all subsequent calculations to ensure consistency
        bcscale(self::PRECISION); 
        
        $customer = Customer::findOrFail($id);

        // Load company data - ADD THIS
        $company = CompanyData::first();

        // Load policies
        $policies = Policy::with('policyType')->where(function($q) use ($customer) {
                $q->where('customer_code', $customer->customer_code)
                  ->orWhere('lead_id', $customer->id)
                  ->orWhere('user_id', $customer->id);
            })
            ->get(['id','fileno','policy_no','policy_type_id','start_date','end_date','gross_premium','status']);

        // Flexible lookups for payments
        $payments = collect();

        if (class_exists(\App\Models\Payment::class)) {
            $paymentModel = new Payment();
            $paymentsTable = $paymentModel->getTable();

            $paymentsQuery = Payment::query();
            $paymentsQuery->where('customer_code', $customer->customer_code);

            if (Schema::hasColumn($paymentsTable, 'customer_id')) {
                // Ensure correct query grouping if orWhere is used
                $paymentsQuery->where(function ($q) use ($customer) {
                    $q->where('customer_code', $customer->customer_code)
                      ->orWhere('customer_id', $customer->id);
                });
            }

            $payments = $paymentsQuery->get();
        } else {
            // Fallback: try payments table (fetch all columns) with schema-safe checks
            $table = 'payments';
            $qb = DB::table($table)->where('customer_code', $customer->customer_code);

            if (Schema::hasColumn($table, 'customer_id')) {
                $qb->orWhere('customer_id', $customer->id);
            }

            $payments = collect($qb->get());
        }

        // Build transactions list
        $transactions = new Collection();
        
        // Use bcadd/bcmul/bcmul to ensure precise debits and credits are created as strings
        foreach ($policies as $p) {
            $date = $p->start_date ? Carbon::parse($p->start_date) : (isset($p->created_at) ? Carbon::parse($p->created_at) : Carbon::now());
            // Ensure gross_premium is treated as a string for high precision, then formatted to 2 decimal places
            $debitAmount = number_format((float)($p->gross_premium ?? 0), self::PRECISION, '.', ''); 
            
            $transactions->push((object)[
                'date' => $date,
                'type' => 'Policy',
                'description' => 'Policy - ' . ($p->policyType->type_name ?? $p->policy_type_id),
                'policy_no' => $p->policy_no,
                'debit' => $debitAmount,
                'credit' => '0.00',
            ]);

            // Add Endorsements for this policy
            if (method_exists($p, 'endorsements')) {
                foreach ($p->endorsements as $endorsement) {
                    $endorsementDate = $endorsement->effective_date ? Carbon::parse($endorsement->effective_date) : ($endorsement->created_at ?? Carbon::now());
                    $amount = number_format((float)($endorsement->premium_impact ?? 0), self::PRECISION, '.', '');
                    $transactions->push((object)[
                        'date' => $endorsementDate,
                        'type' => 'Endorsement',
                        'description' => 'Endorsement - ' . ($endorsement->endorsement_type ?? '') . ($endorsement->description ? (': ' . $endorsement->description) : ''),
                        'policy_no' => $p->policy_no,
                        'debit' => $amount > 0 ? $amount : '0.00',
                        'credit' => $amount < 0 ? abs($amount) : '0.00',
                    ]);
                }
            }
        }

        foreach ($payments as $pay) {
            $receiptIdentifier = $pay->receipt_no ?? $pay->receipt ?? $pay->receipt_number ?? $pay->id ?? null;
            $paymentAmount = number_format((float) ($pay->payment_amount ?? $pay->amount ?? $pay->paid_amount ?? 0), self::PRECISION, '.', '');

            if (isset($pay->payment_date) && $pay->payment_date) {
                $date = Carbon::parse($pay->payment_date);
            } elseif (isset($pay->date) && $pay->date) {
                $date = Carbon::parse($pay->date);
            } elseif (isset($pay->created_at) && $pay->created_at) {
                $date = Carbon::parse($pay->created_at);
            } else {
                $date = Carbon::now();
            }

            $description = $pay->description ?? $pay->notes ?? ('Receipt: ' . ($receiptIdentifier ?? ($pay->id ?? '')));

            $transactions->push((object)[
                'date' => $date,
                'type' => 'Payment',
                'description' => $description,
                'policy_no' => '',
                'debit' => '0.00',
                'credit' => $paymentAmount,
            ]);
        }

        // Sort by date ascending, then by transaction type/id for consistent ordering
        $transactions = $transactions->sortBy(function($t) {
            // Sort by date timestamp
            return $t->date->timestamp;
        })
        ->values() // Re-index the collection
        ->sortBy(function($t) {
            // Secondary sort: Policies (Debit) before Payments (Credit) for transactions on the same second
            return $t->type == 'Policy' ? 0 : 1; 
        })
        ->values();

        // ----------------------------------------------------
        // FIX: Calculate running balance using BCMath (precise strings)
        // ----------------------------------------------------
        $running = '0.00';
        $transactions = $transactions->map(function($t) use (&$running) {
            // Running = Running + Debit - Credit
            $running = bcsub(bcadd($running, $t->debit ?? '0.00'), $t->credit ?? '0.00');
            
            // Format the final running balance string to the required precision (e.g., "1,234.56")
            $t->running = $running; 
            
            // format dates as d-m-Y for display
            $t->date_formatted = $t->date->format('d-m-Y');
            return $t;
        });

        // Determine statement date range
        $startDate = $transactions->first() ? $transactions->first()->date->format('d-m-Y') : null;
        $endDate = $transactions->last() ? $transactions->last()->date->format('d-m-Y') : null;

        // Prepare data for view - ADD COMPANY TO THE DATA ARRAY
        $data = [
            'customer' => $customer,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now()->format('d-m-Y H:i:s'),
            'company' => $company, // ADD THIS LINE
        ];

        // Render PDF view and force download
        $filename = 'CustomerStatement_' . ($customer->customer_code ?? $customer->id) . '.pdf';

        $pdf = PDF::loadView('customers.statement', $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}