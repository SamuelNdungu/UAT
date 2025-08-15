<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//se Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Policy;   
use App\Models\PolicyTypes;
use App\Models\Insurer; 
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDF;

class FeesController extends Controller
{
    public function print($id)
    {
        $fee = Fee::with('customer')->findOrFail($id);
        
        $pdf = PDF::loadView('fees.print', compact('fee'));
        return $pdf->stream('invoice-' . $fee->invoice_number . '.pdf');
    }

    public function index(Request $request)
    {
        $query = Fee::with('customer')->latest();

        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'overdue':
                    $query->where('status', 'overdue');
                    break;
            }
        }

        $metrics = [
            'totalFees' => Fee::count(),
            'totalAmount' => Fee::sum('amount'),
            'paidFees' => Fee::where('status', 'paid')->count(),
            'pendingFees' => Fee::where('status', 'pending')->count(),
            'overdueFees' => Fee::where('status', 'overdue')->count(),
        ];

        $fees = $query->paginate(10);
        return view('fees.index', compact('fees', 'metrics'));
    }

    public function create()
    {
        $invoice_number = 'INV-' . date('Ymd') . '-' . str_pad(Fee::count() + 1, 4, '0', STR_PAD_LEFT);
        return view('fees.create', compact('invoice_number'));
    }

    public function store(Request $request)
    {
        \Log::info('Attempting to create new fee:', ['request_data' => $request->except('_token')]);

        $request->validate([
            'customer_code' => 'required|exists:customers,customer_code',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'date' => 'required|date',
            'invoice_number' => 'required|string|unique:fees,invoice_number'
        ]);

        try {
            DB::beginTransaction();
            
            $customer = Customer::where('customer_code', $request->customer_code)->first();
            
            $fee = Fee::create([
                'customer_code' => $request->customer_code,
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'due_date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'status' => 'pending',
                'created_by' => Auth::id()
            ]);

            DB::commit();
            \Log::info('Fee created successfully:', ['fee_id' => $fee->id, 'customer_id' => $customer->id]);
            return redirect()->route('fees.index')
                            ->with('success', 'Fee created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to create fee:', ['error' => $e->getMessage(), 'customer_code' => $request->customer_code]);
            return back()->with('error', 'Error creating fee: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $fee = Fee::with('customer')->findOrFail($id);
        return view('fees.show', compact('fee'));
    }

    public function edit($id)
    {
        $fee = Fee::with('customer')->findOrFail($id);
        $customers = Customer::all();
        return view('fees.edit', compact('fee', 'customers'));
    }

    public function update(Request $request, $id)
    {
        try {
            $fee = Fee::findOrFail($id);
            \Log::info('Attempting to update fee:', [
                'fee_id' => $id,
                'original_data' => $fee->toArray(),
                'request_data' => $request->except('_token')
            ]);

            \Log::info('Validating fee update request');
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'description' => 'required|string',
                'date' => 'required|date',
                'status' => 'required|in:pending,paid,overdue'
            ]);

            \Log::info('Validation passed, proceeding with update');
            DB::beginTransaction();
            
            $changes = array_diff_assoc(
                [
                    'amount' => $request->amount,
                    'description' => $request->description,
                    'date' => $request->date,
                    'status' => $request->status
                ],
                $fee->only(['amount', 'description', 'date', 'status'])
            );

            \Log::info('Changes to be applied:', ['changes' => $changes]);
            
            $fee->update([
                'amount' => $request->amount,
                'description' => $request->description,
                'date' => $request->date,
                'status' => $request->status
            ]);

            DB::commit();
            \Log::info('Fee updated successfully:', [
                'fee_id' => $id,
                'changes_applied' => $changes,
                'final_data' => $fee->fresh()->toArray()
            ]);
            return redirect()->route('fees.index')
                            ->with('success', 'Fee updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to update fee:', ['fee_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error updating fee: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        \Log::info('Attempting to delete fee:', ['fee_id' => $id]);
        try {
            $fee = Fee::findOrFail($id);
            $fee->delete();
            \Log::info('Fee deleted successfully:', ['fee_id' => $id]);
            return redirect()->route('fees.index')
                            ->with('success', 'Fee deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete fee:', ['fee_id' => $id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Error deleting fee: ' . $e->getMessage());
        }
    }
    public function search(Request $request)
    {
        \Log::info('Search query:', ['query' => $request->input('query')]);
    
        $query = strtolower($request->input('query')); // Convert the search query to lowercase
    
        $customers = Customer::whereRaw('LOWER(first_name) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw('LOWER(corporate_name) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw('LOWER(customer_code) LIKE ?', ["%{$query}%"])
                    ->get(['customer_code', 'first_name', 'last_name', 'corporate_name', 'customer_type']);
    
        // Since we now have the accessor, Laravel will automatically use it
        $customers->transform(function ($customer) {
            return [
                'customer_code' => $customer->customer_code,
                'customer_name' => $customer->customer_name, // Uses the accessor
            ];
        });
    
        \Log::info('Search results:', ['customers' => $customers]);
    
        return response()->json($customers);
    }
}