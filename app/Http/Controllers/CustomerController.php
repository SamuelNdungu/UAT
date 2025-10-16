<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document; // Import the Document model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use PDF; // Make sure to use PDF class for PDF exports
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function create()
    {
        return view('customers.create');
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', 'total'); // Default to 'total' if no filter is provided

        // Initialize query builder for customers
        $query = Customer::query();

        // Apply filter
        switch ($filter) {
            case 'active':
                $query->where('status', true);
                break;
            case 'inactive':
                $query->where('status', false);
                break;
            case 'claims':
                $query->whereHas('claims'); // Assuming you have a relationship defined for claims
                break;
            default:
                // No filter applied, retrieve all customers
                break;
        }

        // Fetch customers based on filter
        $customers = $query->orderBy('id', 'desc')->get();

        // Calculate customer metrics
        $metrics = [
            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('status', true)->count(),
            'inactiveCustomers' => Customer::where('status', false)->count(),
        ];

        // Pass customers and metrics to the view
        return view('customers.index', compact('customers', 'metrics'));
    }

    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        Log::info('Editing customer:', ['customer_id' => $id, 'current_status' => $customer->status]);
        return view('customers.edit', compact('customer'));
    }

    public function store(StoreCustomerRequest $request)
    {
        $validatedData = $request->validated();

        // Handle file upload
        if ($request->hasFile('documents')) {
            $file = $request->file('documents');
            $originalName = $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $originalName, 'public');
            $validatedData['documents'] = $filePath;
        }

        // Generate a unique customer_code starting from CUS-00100
        $validatedData['customer_code'] = $this->generateUniqueCustomerCode();

        // Set default status as active
        $validatedData['status'] = true; // Ensure it's true for active by default

        // Assign the user_id from the authenticated user
        $validatedData['user_id'] = Auth::id();

        // Log the data before saving it
        Log::info('Customer data to be saved:', $validatedData);

        // Try saving the customer and handle any potential errors
        try {
            // Create the customer
            $customer = Customer::create($validatedData);
            
            // Log after the customer is saved
            Log::info('Customer created successfully with customer_code: ' . $validatedData['customer_code']);

            // Redirect back with success message
            return redirect()->route('customers.index')->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            // Log the exception if saving fails
            Log::error('Error creating customer: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Redirect back with error message
            return redirect()->back()->with('error', 'An error occurred while creating the customer.');
        }
    }

    public function update(UpdateCustomerRequest $request, $id)
{
    try {
        Log::info('=== CUSTOMER UPDATE STARTED ===', ['customer_id' => $id]);
        
        // Find the customer
        $customer = Customer::findOrFail($id);
        Log::info('Customer found:', ['customer_id' => $customer->id, 'current_status' => $customer->status]);

        // Validate the request data
        Log::info('Raw request data:', $request->all());
        $validatedData = $request->validated();
        Log::info('Validated data:', $validatedData);

        // Handle status conversion
        if (isset($validatedData['status'])) {
            Log::info('Status field received:', ['status_value' => $validatedData['status']]);
            
            // Convert form values to appropriate database values
            if ($validatedData['status'] === '1') {
                $validatedData['status'] = 'Active';
            } elseif ($validatedData['status'] === '0') {
                $validatedData['status'] = 'Inactive';
            } elseif ($validatedData['status'] === 'Blacklisted') {
                $validatedData['status'] = 'Blacklisted';
            }
            Log::info('Status after conversion:', ['status' => $validatedData['status']]);
        }

        // Handle file upload
        if ($request->hasFile('documents')) {
            $file = $request->file('documents');
            $originalName = $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $originalName, 'public');
            $validatedData['documents'] = $filePath;
            Log::info('File uploaded:', ['file_path' => $filePath]);
        }

        // Assign the user_id from the authenticated user
        $validatedData['user_id'] = Auth::id();
        Log::info('Final data to be updated:', $validatedData);

        // Update customer data
        $updateResult = $customer->update($validatedData);
        Log::info('Update result:', ['success' => $updateResult]);

        // Refresh the customer model to get updated data
        $customer->refresh();
        Log::info('Customer after update:', [
            'customer_id' => $customer->id,
            'status' => $customer->status,
            'updated_at' => $customer->updated_at
        ]);

        Log::info('=== CUSTOMER UPDATE COMPLETED SUCCESSFULLY ===');

        // Redirect back to the customer index with a success message
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');

    } catch (\Exception $e) {
        Log::error('=== CUSTOMER UPDATE FAILED ===', [
            'customer_id' => $id,
            'error_message' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return redirect()->back()
            ->with('error', 'Failed to update customer: ' . $e->getMessage())
            ->withInput();
    }
}

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    // Method to generate a unique customer code
    private function generateUniqueCustomerCode()
    {
        // Get the maximum numeric part of the existing customer codes
        $maxNumber = Customer::max(DB::raw('CAST(SUBSTRING(customer_code, 5) AS INTEGER)'));

        // Increment to generate the new customer code
        $newNumber = $maxNumber ? $maxNumber + 1 : 100; // Start at 100 if no customer codes exist

        // Format the new customer code
        return sprintf('CUS-%05d', $newNumber);
    }

    public function searchCustomer(Request $request)
    {
        $query = $request->input('query');

        $customers = Customer::where('first_name', 'like', "%$query%")
            ->orWhere('last_name', 'like', "%$query%")
            ->orWhere('surname', 'like', "%$query%")
            ->get();

        return response()->json($customers);
    }

    public function exportPdf()
    {
        $customers = Customer::all();
        $pdf = PDF::loadView('customers.pdf', compact('customers'));
        return $pdf->download('customers.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }
}