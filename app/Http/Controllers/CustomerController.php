<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document; // Import the Document model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
        {
            // Validate incoming request
            $validatedData = $request->validate([
                'customer_type' => 'required|string',
                'title' => 'nullable|string',
                'first_name' => 'nullable|string',
                'last_name' => 'nullable|string',
                'surname' => 'nullable|string',
                'dob' => 'nullable|date',
                'occupation' => 'nullable|string',
                'corporate_name' => 'nullable|string',
                'business_no' => 'nullable|string',
                'contact_person' => 'nullable|string',
                'designation' => 'nullable|string',
                'industry_class' => 'nullable|string',
                'industry_segment' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'city' => 'nullable|string',
                'county' => 'nullable|string',
                'postal_code' => 'nullable|string',
                'country' => 'nullable|string',
                'id_number' => 'nullable|string|unique:customers,id_number,' . $id,  
                'kra_pin' => 'nullable|string|unique:customers,kra_pin,' . $id,  
                'documents' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048', 
                'notes' => 'nullable|string',
                'status' => 'required|in:0,1',  // Validate as 0 or 1
            ]);

            // Retrieve the customer to update
            $customer = Customer::findOrFail($id);

            // Handle file upload
            if ($request->hasFile('documents')) {
                $file = $request->file('documents');
                $originalName = $file->getClientOriginalName(); // Get the original name of the file
                
                // Store the file in 'documents' directory in 'public/storage', retaining the original file name
                $filePath = $file->storeAs('documents', $originalName, 'public');
                
                // Add the file path to the validated data array
                $validatedData['documents'] = $filePath;
            }

            // Update customer data
            $customer->update($validatedData);

            // Redirect back to the customer index with a success message
            return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
        }


    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function store(Request $request)
{
    // Define validation rules
    $rules = [
        'customer_type' => 'required|string', 
        'kra_pin' => 'required|string|unique:customers,kra_pin',
        'email' => 'required|email',
        'phone' => ['required','string','regex:/^(\+?254|0)?7\d{8}$/'],
        'city' => 'required|string',
        'address' => 'nullable|string',
        'county' => 'nullable|string',
        'postal_code' => 'nullable|string',
        'country' => 'nullable|string',
        'dob' => 'nullable|date',
        'occupation' => 'nullable|string',
        'business_no' => 'nullable|string',
        'contact_person' => 'nullable|string',
        'designation' => 'nullable|string',
        'industry_class' => 'nullable|string',
        'industry_segment' => 'nullable|string',
        'documents' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048', // Single file handling
        'notes' => 'nullable|string',
        'status' => 'boolean',  // Ensuring it's a boolean
    ];

    // Additional validation for Individual customers
    if ($request->customer_type == 'Individual') {
        $rules['first_name'] = 'required|string';
        $rules['last_name'] = 'required|string';
        $rules['id_number'] = 'required|string|unique:customers,id_number';
    }

    // Additional validation for Corporate customers
    if ($request->customer_type == 'Corporate') {
        $rules['corporate_name'] = 'required|string'; 
    }

    $validatedData = $request->validate($rules);

    // Handle file upload
    if ($request->hasFile('documents')) {
        $file = $request->file('documents');
        $originalName = $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $originalName, 'public');
        $validatedData['documents'] = $filePath;
    }

    // Generate a unique customer_code starting from CUS-00100
    $lastCustomer = Customer::latest()->first();
    $lastCustomerCode = $lastCustomer ? $lastCustomer->customer_code : 'CUS-00099';

    // Extract the numeric part of the last customer code
    $lastCustomerNumber = (int) substr($lastCustomerCode, 4);

    // Ensure the new customer code starts from CUS-00100 or above
    $newCustomerNumber = max($lastCustomerNumber + 1, 100);

    // Generate the new customer code
    $newCustomerCode = 'CUS-' . str_pad($newCustomerNumber, 5, '0', STR_PAD_LEFT);
    
    // Add the new customer code to the validated data
    $validatedData['customer_code'] = $newCustomerCode;

    // Set default status as active
    $validatedData['status'] = true;  // Ensure it's true for active by default

    // Log the data before saving it
    \Log::info('Customer data to be saved:', $validatedData);

    // Try saving the customer and handle any potential errors
    try {
        // Create the customer
        $customer = Customer::create($validatedData);

        // Log after the customer is saved
        \Log::info('Customer created successfully with customer_code: ' . $newCustomerCode);

        // Redirect back with success message
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');

    } catch (\Exception $e) {
        // Log the exception if saving fails
        \Log::error('Error creating customer: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

        // Redirect back with error message
        return redirect()->back()->with('error', 'An error occurred while creating the customer.');
    }
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



}


