<?php

namespace App\Http\Controllers;

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
class PaymentsController extends Controller
{
    // Display a list of payments
    public function index()
    {
        // Fetch payments with customer details and related receipts and allocations
        $payments = Payment::select(
                'payments.*',
                DB::raw("CONCAT(customers.first_name, ' ', customers.last_name, ' ', customers.surname) AS customer_full_name"),
                'customers.corporate_name'
            )
            ->join('customers', 'payments.customer_code', '=', 'customers.customer_code')
            ->with('receipts', 'allocations')
            ->orderBy('payments.payment_date', 'desc')
            ->get();
    
        // Calculate payment metrics
        $totalSales = Policy::sum('gross_premium');
        $totalPayments = Payment::sum('payment_amount');
        $totalAllocated = Receipt::sum('allocated_amount');
        $totalRemaining = Receipt::sum('remaining_amount');
        $balance = $totalSales - $totalAllocated;
    
        // Debtors Aging
        $currentDate = now();
        $balanceLessThan30 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->subDays(30), $currentDate])
                            ->sum('balance');
    
        $balance30To60 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->subDays(60), $currentDate->subDays(31)])
                            ->sum('balance');
    
        $balance60To90 = Policy::where('balance', '>', 0)
                            ->whereBetween('start_date', [$currentDate->subDays(90), $currentDate->subDays(61)])
                            ->sum('balance');
    
        $balanceMoreThan90 = Policy::where('balance', '>', 0)
                            ->where('start_date', '<', $currentDate->subDays(91))
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

        DB::beginTransaction();

        try {
            $payment = Payment::findOrFail($id);

            $receipt = $payment->receipts->first(); // Assuming one receipt per payment
            if (!$receipt) {
                \Log::error('PaymentsController@storeAllocation: no receipt found for payment', ['payment_id' => $id]);
                return redirect()->back()->with('error', 'No receipt found for this payment. Allocation aborted.');
            }

            $totalAllocated = 0;

            // Prepare allocations to process (skip canceled targets)
            $allocationsToProcess = [];
            foreach ($validatedData['allocations'] as $allocationData) {
                $policy = Policy::find($allocationData['policy_id']);
                if ($policy && $policy->isCancelled()) {
                    // skip canceled
                    continue;
                }
                $allocationsToProcess[] = $allocationData;
            }

            if (empty($allocationsToProcess)) {
                // Nothing to process (all allocations were canceled or zero)
                \Log::info('PaymentsController@storeAllocation: no allocations to process after skipping canceled policies', ['payment_id' => $id, 'canceled' => $canceled, 'user_id' => optional(auth()->user())->id]);
                if (!empty($canceled)) {
                    $msg = 'All requested allocations were skipped because the targeted policies are canceled: ' . implode(', ', $canceled);
                    return redirect()->back()->with('warning', $msg);
                }
                return redirect()->back()->with('info', 'No allocations to process.');
            }

            foreach ($allocationsToProcess as $allocationData) {
                // Skip zero allocations to avoid creating no-op rows
                $allocAmount = floatval($allocationData['allocation_amount']);
                if ($allocAmount <= 0) {
                    \Log::info('PaymentsController@storeAllocation: skipping zero allocation', ['payment_id' => $id, 'policy_id' => $allocationData['policy_id'], 'amount' => $allocAmount, 'user_id' => optional(auth()->user())->id]);
                    continue;
                }
                $policy = Policy::find($allocationData['policy_id']);
                if (!$policy) {
                    \Log::warning('PaymentsController@storeAllocation: policy not found for allocation', ['payment_id' => $id, 'policy_id' => $allocationData['policy_id'], 'user_id' => optional(auth()->user())->id]);
                    continue; // skip this allocation
                }

                // Create allocation record
                $allocation = Allocation::create([
                    'payment_id' => $payment->id,
                    'policy_id' => $allocationData['policy_id'],
                    'allocation_amount' => $allocAmount,
                    'allocation_date' => now(),
                    'user_id' => optional(auth()->user())->id,
                ]);

                \Log::info('PaymentsController@storeAllocation: allocation created', ['allocation_id' => $allocation->id, 'payment_id' => $payment->id, 'policy_id' => $policy->id, 'amount' => $allocAmount, 'user_id' => optional(auth()->user())->id]);

                // Debugging: Log the policy before updating
                \Log::info('Policy before update', ['policy_id' => $policy->id, 'paid_amount' => $policy->paid_amount, 'balance' => $policy->balance]);

                $policy->paid_amount += $allocAmount;
                $policy->outstanding_amount -= $allocAmount;
                $policy->balance = $policy->gross_premium - $policy->paid_amount;
                $policy->save();

                // Debugging: Log the policy after updating
                \Log::info('Policy after update', ['policy_id' => $policy->id, 'paid_amount' => $policy->paid_amount, 'balance' => $policy->balance]);

                $totalAllocated += $allocAmount;
            }

            // Update the receipt
            $receipt->allocated_amount += $totalAllocated;
            $receipt->remaining_amount -= $totalAllocated;
            $receipt->save();

            // Debugging: Log the receipt after updating
            \Log::info('Receipt after update', $receipt->toArray());

            \Log::info('PaymentsController@storeAllocation: allocation completed', ['payment_id' => $payment->id, 'total_allocated' => $totalAllocated, 'user_id' => optional(auth()->user())->id]);

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

    $stkPushResponse = $this->triggerSTKPush($validated['phone_number'], $validated['amount']);
    
    if ($stkPushResponse->ResponseCode == '0') {
        return back()->with('success', 'Please complete the payment on your phone.');
    }
    
    return back()->with('error', 'Failed to initiate M-PESA payment.');
}

private function triggerSTKPush($phoneNumber, $amount)
{
    $accessToken = $this->getMpesaAccessToken();
    $url = config('mpesa.stk_push_url');
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);

    $curl_post_data = [
        // STK Push parameters here
    ];

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($curl_post_data));
    $curl_response = curl_exec($curl);
    curl_close($curl);

    return json_decode($curl_response);
}
public function handleMpesaCallback(Request $request)
{
    $callbackJSONData = file_get_contents('php://input');
    $callbackData = json_decode($callbackJSONData);

    $transaction = $this->storeTransaction($callbackData);

    // Process the transaction based on received data
    // E.g., verify transaction, allocate to a customer account, etc.
    
    return response()->json(['status' => 'success', 'message' => 'Callback received successfully']);
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
    // Eager load the customer and receipts relationships in one query
    $payment = Payment::with(['customer', 'receipts'])->findOrFail($id);
    
    //dd($payment->customer); 
    // Debugging: Log payment details
    Log::info('Payment Details:', [
        'payment_id' => $payment->id,
        'payment_amount' => $payment->payment_amount,
        'customer' => $payment->customer->first_name,
        'customer_name' => $payment->customer ? $payment->customer->customer_name : 'N/A',
        'receipts' => $payment->receipts,
    ]);
    
    // Check if receipts exist
    if ($payment->receipts->isEmpty()) {
        Log::error('No receipts found for payment:', ['payment_id' => $id]);
        return redirect()->back()->with('error', 'No receipts found for this payment.');
    }

    // Prepare the data needed for the receipt view
    $data = [
        'payment' => $payment,
    ];

    // Load the view and generate the PDF
    $pdf = PDF::loadView('receipts.print', $data);

    // Download the PDF
    return $pdf->download('receipt_' . $payment->receipts->first()->receipt_number . '.pdf');
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


}
