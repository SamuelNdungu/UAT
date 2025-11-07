<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MpesaTransaction;
use App\Models\Receipt;
use App\Models\Allocation;

class MpesaTransactionController extends Controller
{
    // list transactions
    public function index()
    {
        $transactions = MpesaTransaction::orderBy('created_at', 'desc')->paginate(25);
        return view('mpesa.index', compact('transactions'));
    }

    // show raw payload and details
    public function show($id)
    {
        $tx = MpesaTransaction::findOrFail($id);
        return view('mpesa.show', compact('tx'));
    }

    // Apply allocation for a matched transaction: create allocation(s) to policies
    public function applyAllocation(Request $request, $id)
    {
        $tx = MpesaTransaction::findOrFail($id);

        if (!$tx->receipt_id || !$tx->payment_id) {
            return redirect()->back()->with('error', 'Transaction is not linked to a receipt/payment.');
        }

        DB::beginTransaction();
        try {
            // Lock receipt and related payment
            $receipt = Receipt::where('id', $tx->receipt_id)->lockForUpdate()->first();
            if (!$receipt) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Receipt not found.');
            }

            $available = floatval($receipt->remaining_amount);
            $amount = floatval($tx->amount ?? 0);
            if ($amount <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Invalid transaction amount.');
            }
            if ($amount > $available) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Transaction amount exceeds receipt remaining amount.');
            }

            // Naive allocation: apply single allocation against the payment's outstanding policies in order
            $payment = $receipt->payment;
            $customerCode = $payment->customer_code;

            $policies = \App\Models\Policy::where('customer_code', $customerCode)
                ->where('balance', '>', 0)
                ->orderBy('start_date')
                ->lockForUpdate()
                ->get();

            $toAllocate = $amount;
            foreach ($policies as $policy) {
                if ($toAllocate <= 0) break;
                $due = max(0, $policy->balance);
                if ($due <= 0) continue;
                $alloc = min($due, $toAllocate);

                Allocation::create([
                    'payment_id' => $payment->id,
                    'policy_id' => $policy->id,
                    'allocation_amount' => $alloc,
                    'allocation_date' => now(),
                    'user_id' => optional(auth()->user())->id,
                ]);

                $policy->paid_amount += $alloc;
                $policy->outstanding_amount = max(0, $policy->outstanding_amount - $alloc);
                $policy->balance = $policy->gross_premium - $policy->paid_amount;
                $policy->save();

                $toAllocate -= $alloc;
            }

            // Update receipt
            $receipt->allocated_amount += ($amount - $toAllocate);
            $receipt->remaining_amount = max(0, $receipt->remaining_amount - ($amount - $toAllocate));
            $receipt->save();

            // Mark transaction processed
            $tx->status = 'applied';
            $tx->processed_at = now();
            $tx->save();

            DB::commit();
            return redirect()->route('mpesa.transactions.show', $tx->id)->with('success', 'Allocation applied successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mpesa applyAllocation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to apply allocation.');
        }
    }
}
