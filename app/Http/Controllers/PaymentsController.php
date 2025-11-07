<?php

namespace App\Http\Controllers;

use App\Models\MpesaTransaction;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Policy;
use App\Models\Receipt;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use App\Exports\PaymentsExport;
use PDF; // Make sure to use PDF class for PDF exports
use App\Services\MpesaService;
class PaymentsController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }
    // Display a list of payments with server-side filters and pagination
    public function index(Request $request)
    {
        // Filters
        $filter = $request->query('filter', 'unallocated');
        $perPage = (int) $request->query('per_page', 10);

        // Build base query
        $query = Payment::select(
                'payments.*',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name, ' ', customers.surname) AS customer_full_name"),
                'customers.corporate_name'
            )
            ->join('customers', 'payments.customer_code', '=', 'customers.customer_code')
            ->with('receipts', 'allocations')
            ->orderBy('payments.payment_date', 'desc');

        // Optional date range filter
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('payments.payment_date', [$request->query('from'), $request->query('to')]);
        } elseif ($request->filled('from')) {
            $query->where('payments.payment_date', '>=', $request->query('from'));
        } elseif ($request->filled('to')) {
            $query->where('payments.payment_date', '<=', $request->query('to'));
        }

        // Optional customer filter: allow searching by customer name or corporate name (partial match)
        if ($request->filled('customer')) {
            // Trim and escape user input for LIKE
            $rawSearch = trim($request->query('customer'));
            if ($rawSearch !== '') {
                // escape % and _ to avoid wildcard injection
                $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $rawSearch);
                $like = "%{$escaped}%";

                // Filter on individual name columns and corporate_name for more reliable matches
                $query->where(function ($q) use ($like) {
                    $q->where('customers.first_name', 'LIKE', $like)
                      ->orWhere('customers.last_name', 'LIKE', $like)
                      ->orWhere('customers.surname', 'LIKE', $like)
                      ->orWhere('customers.corporate_name', 'LIKE', $like);
                });
            }
        }

        // Allocated/unallocated/zero filters
        switch ($filter) {
            case 'allocated':
                $query->whereHas('receipts', function ($q) { $q->where('remaining_amount', 0); });
                break;
            case 'unallocated':
                $query->whereHas('receipts', function ($q) { $q->where('allocated_amount', 0); });
                break;
            case 'zero-payment':
                $query->where('payment_amount', 0);
                break;
            case 'both':
            default:
                // no extra filter
                break;
        }

        // Paginate and keep query string for links
        $payments = $query->paginate($perPage)->appends($request->query());

        // Calculate payment metrics (kept as global aggregates)
        $totalSales = Policy::sum('gross_premium');
        $totalPayments = Payment::sum('payment_amount');
        $totalAllocated = Receipt::sum('allocated_amount');
        $totalRemaining = Receipt::sum('remaining_amount');
        $balance = $totalSales - $totalAllocated;

        // Debtors Aging
        $currentDate = now();
        $balanceLessThan30 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->copy()->subDays(30), $currentDate])
                            ->sum('balance');

        $balance30To60 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->copy()->subDays(60), $currentDate->copy()->subDays(31)])
                            ->sum('balance');

        $balance60To90 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->copy()->subDays(90), $currentDate->copy()->subDays(61)])
                            ->sum('balance');

        $balanceMoreThan90 = Policy::where('balance', '>', 0)
                            ->where('start_date', '<', $currentDate->copy()->subDays(91))
                            ->sum('balance');

        $metrics = [
            'totalSales' => $totalSales,
            'totalPayments' => $totalPayments,
            'totalAllocated' => $totalAllocated,
            'totalRemaining' => $totalRemaining,
            'balance' => $balance,
            'balanceLessThan30' => $balanceLessThan30,
            'balance30To60' => $balance30To60,
            'balance60To90' => $balance60To90,
            'balanceMoreThan90' => $balanceMoreThan90,
        ];

        // Pass payments and metrics to the view
        return view('payments.index', compact('payments', 'metrics'));
    }
    

    

    // Show the form for creating a new payment
    public function create()
    {
        $customers = Customer::all();
        return view('payments.create', compact('customers'));
    }

    // Store a newly created payment in the database
    // Store a newly created payment in the database
public function store(Request $request)
{
    \Log::info('Store Payment Request Received:', $request->all());

    $validatedData = $request->validate([
        'customer_code' => 'required|string|max:255',
        'payment_date' => 'required|date',
        'payment_amount' => 'required|numeric',
        'payment_method' => 'nullable|string|max:255',
        'payment_reference' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ]);

    \Log::info('Validated Data:', $validatedData);

    DB::beginTransaction();

    try {
        $payment = Payment::create($validatedData);
        \Log::info('Payment Created:', $payment->toArray());

        // Create a receipt for the payment
        $receipt = Receipt::create([
            'payment_id' => $payment->id,
            'receipt_date' => $validatedData['payment_date'],
            'receipt_number' => $this->generateReceiptNumber(),
            'allocated_amount' => 0,
            'remaining_amount' => $validatedData['payment_amount'],
        ]);
        \Log::info('Receipt Created:', $receipt->toArray());

        DB::commit();
        return redirect()->route('payments.index')->with('success', 'Payment created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('An error occurred while creating the payment:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred while creating the payment.');
    }
}


    // Show the form for allocating a payment to policies
    public function allocate($id)
    {
        $payment = Payment::select(
                'payments.*',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name, ' ', customers.surname) AS customer_full_name"),
                'customers.corporate_name'
            )
            ->join('customers', 'payments.customer_code', '=', 'customers.customer_code')
            ->with('receipts', 'allocations')
            ->findOrFail($id);
    
        $policies = Policy::where('customer_code', $payment->customer_code)->get();
    
        return view('payments.allocate', compact('payment', 'policies'));
    }
    

    // Store the allocation of a payment to policies
    public function storeAllocation(Request $request, $id)
    {
        \Log::info('PaymentsController@storeAllocation: request received', ['payment_id' => $id, 'payload' => $request->all(), 'user_id' => optional(auth()->user())->id]);
        $validatedData = $request->validate([
            'allocations' => 'required|array',
            'allocations.*.policy_id' => 'required|exists:policies,id',
            'allocations.*.allocation_amount' => 'required|numeric',
        ]);
        // Server-side check: identify canceled targets but continue with other allocations
        $canceled = [];
        foreach ($validatedData['allocations'] as $allocationData) {
            $policy = Policy::find($allocationData['policy_id']);
            if ($policy && $policy->isCancelled()) {
                $canceled[] = $policy->fileno ?? $policy->id;
            }
        }

        if (!empty($canceled)) {
            \Log::warning('PaymentsController@storeAllocation: attempted allocation includes canceled policies (these will be skipped)', ['payment_id' => $id, 'canceled' => $canceled, 'user_id' => optional(auth()->user())->id]);
            // do not abort; we'll skip canceled policies and process the rest
        }

        // Pre-validate totals before starting transaction
        $requestedTotal = 0.0;
        $allocationsToProcess = [];
        foreach ($validatedData['allocations'] as $allocationData) {
            $allocAmount = floatval($allocationData['allocation_amount']);
            if ($allocAmount <= 0) {
                continue; // skip zero or negative
            }
            $policyId = $allocationData['policy_id'];
            $allocationsToProcess[$policyId] = $allocAmount;
            $requestedTotal += $allocAmount;
        }

        if (empty($allocationsToProcess)) {
            return redirect()->back()->with('info', 'No valid allocations to process.');
        }

        DB::beginTransaction();
        try {
            // Reload payment and lock receipt row for update to avoid races
            $payment = Payment::with('receipts')->findOrFail($id);
            $receipt = $payment->receipts->first(); // assume single receipt
            if (!$receipt) {
                DB::rollBack();
                return redirect()->back()->with('error', 'No receipt found for this payment.');
            }

            // Lock the receipt row
            $receipt = Receipt::where('id', $receipt->id)->lockForUpdate()->first();

            // Re-check available remaining amount
            if ($requestedTotal > floatval($receipt->remaining_amount)) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Requested allocation exceeds receipt remaining amount.');
            }

            // Eager-load affected policies and lock them for update
            $policyIds = array_keys($allocationsToProcess);
            $policies = Policy::whereIn('id', $policyIds)->lockForUpdate()->get()->keyBy('id');

            $totalAllocated = 0.0;

            foreach ($allocationsToProcess as $policyId => $allocAmount) {
                $policy = $policies->get($policyId);
                if (!$policy) {
                    \Log::warning('PaymentsController@storeAllocation: policy not found', ['policy_id' => $policyId, 'payment_id' => $id]);
                    continue;
                }
                if ($policy->isCancelled()) {
                    \Log::info('Skipping cancelled policy', ['policy_id' => $policyId]);
                    continue;
                }

                // Create allocation record
                $allocation = Allocation::create([
                    'payment_id' => $payment->id,
                    'policy_id' => $policy->id,
                    'allocation_amount' => $allocAmount,
                    'allocation_date' => now(),
                    'user_id' => optional(auth()->user())->id,
                ]);

                // Update policy amounts
                $policy->paid_amount += $allocAmount;
                $policy->outstanding_amount = max(0, $policy->outstanding_amount - $allocAmount);
                $policy->balance = $policy->gross_premium - $policy->paid_amount;
                $policy->save();

                $totalAllocated += $allocAmount;
            }

            // Update the receipt totals
            $receipt->allocated_amount += $totalAllocated;
            $receipt->remaining_amount = max(0, $receipt->remaining_amount - $totalAllocated);
            $receipt->save();

            DB::commit();
            return redirect()->route('payments.index')->with('success', 'Payment allocated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('An error occurred while allocating the payment:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while allocating the payment.');
        }
    }
    
    // Generate a unique receipt number
    protected function generateReceiptNumber()
    {
        $latestReceipt = Receipt::orderBy('id', 'desc')->first();
        if (!$latestReceipt) {
            return 'RCPT0001';
        }
        $number = intval(substr($latestReceipt->receipt_number, 4)) + 1;
        return 'RCPT' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function destroyAllocation($id)
{
    DB::beginTransaction();

    try {
        // Debugging: Log the ID being passed
        \Log::info('Attempting to delete allocation with ID:', ['id' => $id]);

        $allocation = Allocation::findOrFail($id);

        // Debugging: Log the allocation before deletion
        \Log::info('Found Allocation:', $allocation->toArray());

        $policy = $allocation->policy;
        $receipt = $allocation->payment->receipts->first(); // Assuming one receipt per payment

        // Revert the allocated amount from policy and update outstanding and balance
        $policy->paid_amount -= $allocation->allocation_amount;
        $policy->outstanding_amount += $allocation->allocation_amount;
        $policy->balance = $policy->gross_premium - $policy->paid_amount;
        $policy->save();

        // Update the receipt's allocated and remaining amounts
        $receipt->allocated_amount -= $allocation->allocation_amount;
        $receipt->remaining_amount += $allocation->allocation_amount;
        $receipt->save();

        // Delete the allocation
        $allocation->delete();

        DB::commit();
        return redirect()->back()->with('success', 'Allocation deleted successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('An error occurred while deleting the allocation:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred while deleting the allocation.');
    }
}


public function initiateMpesaPayment(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric',
        'phone_number' => 'required', // Ensure this is a valid M-PESA registered number
    ]);
    $stkPushResponse = $this->mpesaService->triggerStkPush($validated['phone_number'], $validated['amount']);

    if ($stkPushResponse && isset($stkPushResponse->ResponseCode) && $stkPushResponse->ResponseCode == '0') {
        return back()->with('success', 'Please complete the payment on your phone.');
    }

    return back()->with('error', 'Failed to initiate M-PESA payment.');
}
public function handleMpesaCallback(Request $request)
{
    $result = $this->mpesaService->handleCallback($request);
    if (!$result['valid']) {
        Log::warning('Mpesa callback validation failed', ['reason' => $result['reason']]);
        return response()->json(['status' => 'error', 'reason' => $result['reason']], 400);
    }
        $callbackData = $result['data'];

        // Persist the callback payload and return a simple acknowledgment
        try {
            $mpesa = $this->storeTransaction($callbackData);
            Log::info('Mpesa callback stored', ['id' => $mpesa->id ?? null]);
        } catch (\Throwable $e) {
            Log::error('Mpesa callback store failed: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'received']);
}

    /**
     * Persist an incoming MPESA callback payload to the database.
     * Returns the created MpesaTransaction model.
     */
    protected function storeTransaction(array $payload)
    {
        $transactionCode = data_get($payload, 'Body.stkCallback.CheckoutRequestID') ?: data_get($payload, 'Body.stkCallback.ResultCode');
        $amount = data_get($payload, 'Body.stkCallback.CallbackMetadata.Item.0.Value') ?: data_get($payload, 'Body.stkCallback.CallbackMetadata.Item.amount');
        $phone = null;

        $items = data_get($payload, 'Body.stkCallback.CallbackMetadata.Item');
        if (is_array($items)) {
            foreach ($items as $it) {
                if (isset($it['Name']) && strtolower($it['Name']) === 'mpesareceiptnumber') {
                    $transactionCode = $it['Value'];
                }
                if (isset($it['Name']) && in_array(strtolower($it['Name']), ['amount', 'transactionamount'])) {
                    $amount = $it['Value'];
                }
                if (isset($it['Name']) && in_array(strtolower($it['Name']), ['phonenumber', 'msisdn'])) {
                    $phone = $it['Value'];
                }
            }
        }

        // Create initial record
        $record = MpesaTransaction::create([
            'provider' => 'mpesa',
            'transaction_code' => $transactionCode,
            'amount' => $amount,
            'phone_number' => $phone,
            'status' => 'received',
            'raw_payload' => $payload,
            'processed_at' => null,
        ]);

        // Attempt reconciliation
        try {
            // 1) Try exact match by receipt number (often MPESA uses receipt as a reference)
            if ($transactionCode) {
                $receipt = Receipt::where('receipt_number', $transactionCode)->first();
                if ($receipt) {
                    $record->receipt_id = $receipt->id;
                    $record->payment_id = $receipt->payment_id;
                    $record->status = 'matched_receipt';
                    $record->processed_at = now();
                    $record->save();
                    // Optionally mark payment/receipt as paid or create allocations here
                    return $record;
                }
            }

            // 2) Fallback: match by phone and amount within a configurable time window
            if ($phone && $amount) {
                $days = intval(config('mpesa.matching.time_window_days', 1));
                $start = now()->subDays($days)->startOfDay();
                $end = now()->endOfDay();

                $possibleReceipts = Receipt::whereBetween('receipt_date', [$start, $end])->with(['payment.customer'])->get();

                $normTxPhone = $this->mpesaService::normalizePhone($phone);

                foreach ($possibleReceipts as $r) {
                    $payment = $r->payment;
                    if (!$payment) continue;

                    $customerPhone = data_get($payment, 'customer.phone');
                    $normCustomerPhone = $this->mpesaService::normalizePhone($customerPhone);

                    $amountMatch = $this->mpesaService::amountsAreClose($payment->payment_amount ?? $r->remaining_amount, $amount);
                    $phoneMatch = $normTxPhone && $normCustomerPhone && ($normTxPhone === $normCustomerPhone || strpos($normCustomerPhone, $normTxPhone) !== false || strpos($normTxPhone, $normCustomerPhone) !== false);

                    if ($amountMatch && $phoneMatch) {
                        $record->receipt_id = $r->id;
                        $record->payment_id = $payment->id;
                        $record->status = 'matched_fuzzy';
                        $record->processed_at = now();
                        $record->save();
                        return $record;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Mpesa reconciliation attempt failed: ' . $e->getMessage());
        }

        return $record;
    }

public function unallocateAll($id)
{
    DB::beginTransaction();

    try {
        $payment = Payment::findOrFail($id);
        \Log::info('Processing Payment for unallocation:', ['payment_id' => $id]);

        $allocations = $payment->allocations;

        if ($allocations->isEmpty()) {
            \Log::warning('No allocations found for payment ID: ' . $id);
            return redirect()->back()->with('info', 'No allocations found to revert for this payment.');
        }

        // Track total amount to revert in the payment record
        $totalRevertedAmount = 0;

        foreach ($allocations as $allocation) {
            $policy = $allocation->policy;

            if (!$policy) {
                \Log::warning('Policy not found for allocation:', $allocation->toArray());
                continue;
            }

            // Update the policy amounts
            $policy->paid_amount -= $allocation->allocation_amount;
            $policy->outstanding_amount += $allocation->allocation_amount;
            $policy->balance = $policy->gross_premium - $policy->paid_amount;
            $policy->save();

            \Log::info('Updated Policy:', $policy->toArray());

            // Accumulate the allocation amount for the payment update
            $totalRevertedAmount += $allocation->allocation_amount;
        }

        // Delete all allocations related to the payment
        $deletedRows = Allocation::where('payment_id', $id)->delete();
        \Log::info('Deleted Allocations:', ['payment_id' => $id, 'rows_deleted' => $deletedRows]);

        // Update payment status and amounts back to unallocated
        $receipt = $payment->receipts->first(); // Assuming there's at least one receipt
        $receipt->allocated_amount -= $totalRevertedAmount;
        $receipt->remaining_amount += $totalRevertedAmount;
        $receipt->save();

        // Optional: Update the payment status if there’s a status field
        // $payment->status = 'unallocated'; // Uncomment if payment has a status field
        $payment->save(); // If modifying any additional fields on Payment

        DB::commit();
        return redirect()->back()->with('success', 'All allocations reverted successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error during unallocation:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred while unallocating payments. Please try again.');
    }
}

 

public function printReceipt($id)
{
    $payment = Payment::with(['customer', 'receipts', 'allocations.policy'])->findOrFail($id);
    $company = \App\Models\CompanyData::first();
    
    $pdf = PDF::loadView('receipts.receipt_pdf', [ // Change to PDF view
        'payment' => $payment,
        'company' => $company
    ])->setPaper('a4', 'portrait')
      ->setOptions([
          'margin-top'    => 10,
          'margin-right'  => 10,
          'margin-bottom' => 10,
          'margin-left'   => 10,
      ]);
    
    $filename = 'receipt-' . $payment->receipts->first()->receipt_number . '.pdf';
    return $pdf->download($filename); // Use stream to open in browser
}

public function exportPdf()
{
    $payments = Payment::with(['receipts', 'customer'])->get(); // Include receipts and customer relationships

    // Prepare the data needed for the receipt view
    $data = [
        'payments' => $payments,
    ];

    // Load the view and generate the PDF
    $pdf = PDF::loadView('payments.pdf', $data);
    
    // Download the PDF
    return $pdf->download('payments.pdf');
}



public function exportExcel()
{
    return Excel::download(new PaymentsExport, 'payments.xlsx');
}

    /**
     * Debug helper: return the SQL and bindings used for the payments index query for a sample customer search.
     * Only registered when app.debug is true.
     */
    public function debugSearchSql(Request $request)
    {
        $sample = $request->query('customer', 'john');

        $filter = $request->query('filter', 'unallocated');

        $query = Payment::select(
                'payments.*',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name, ' ', customers.surname) AS customer_full_name"),
                'customers.corporate_name'
            )
            ->join('customers', 'payments.customer_code', '=', 'customers.customer_code')
            ->with('receipts', 'allocations')
            ->orderBy('payments.payment_date', 'desc');

        // apply the search logic used in index
        $rawSearch = trim($sample);
        if ($rawSearch !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $rawSearch);
            $like = "%{$escaped}%";

            $query->where(function ($q) use ($like) {
                $q->where('customers.first_name', 'LIKE', $like)
                  ->orWhere('customers.last_name', 'LIKE', $like)
                  ->orWhere('customers.surname', 'LIKE', $like)
                  ->orWhere('customers.corporate_name', 'LIKE', $like);
            });
        }

        // apply filter summary (only a subset)
        switch ($filter) {
            case 'allocated':
                $query->whereHas('receipts', function ($q) { $q->where('remaining_amount', 0); });
                break;
            case 'unallocated':
                $query->whereHas('receipts', function ($q) { $q->where('allocated_amount', 0); });
                break;
            case 'zero-payment':
                $query->where('payment_amount', 0);
                break;
        }

        // Log SQL and bindings to laravel log for additional inspection
        try {
            Log::info('Debug payments search SQL', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
        } catch (\Exception $e) {
            // swallow logging errors in debug helper
        }

        // Return SQL and bindings for inspection
        return response()->json([
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);
    }

}

