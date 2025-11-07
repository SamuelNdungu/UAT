<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    // ...existing code...

    public function store(Request $request)
    {
        $policy = Policy::findOrFail($request->input('policy_id'));
        if ($policy->isCancelled()) {
            return redirect()->back()->with('error', 'Cannot receipt a canceled policy.');
        }
        // ...existing code...
    }


public function printReceipt($id)
{
    $payment = Payment::with(['customer', 'receipts', 'allocations.policy'])->findOrFail($id);
    $company = \App\Models\CompanyData::first();
    
    $pdf = PDF::loadView('receipts.pdf', [
        'payment' => $payment,
        'company' => $company
    ]);
    
    // Generate and use the dynamic filename
    $filename = 'receipt-' . $payment->receipts->first()->receipt_number . '.pdf';
    return $pdf->download($filename);
}
}