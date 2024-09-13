<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer; 
use App\Models\PolicyTypes;
use App\Models\Insurer; 


class CustomerController2 extends Controller
{
    public function getCreatePolicyForm()
    {
        $insurers = DB::table('insurers')->pluck('name', 'id');
        $availablePolicyTypes = DB::table('policy_types')->pluck('type_name', 'id');
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get(); // Ensure 'make' and 'model' are selected

        return view('policies.create', compact('insurers', 'availablePolicyTypes', 'availableVehicleTypes', 'vehicleModels'));
    }

    public function store(Request $request)
{
    // Log the incoming request data for debugging purposes
    \Log::info('Received data: ', $request->all());

    // Define validation rules for the incoming request
    $validatedData = $request->validate([
        'customer_code' => 'required|string|max:255',
        'customer_name' => 'required|string|max:255',
        'policy_type_id' => 'required|integer',
        'coverage' => 'required|string|max:255',
        'start_date' => 'required|date',
        'days' => 'nullable|integer',
        'end_date' => 'nullable|date',
        'insurer_id' => 'required|integer',
        'policy_no' => 'nullable|string|max:15',
        'reg_no' => 'nullable|string|max:255',
        'make' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'yom' => 'nullable|integer',
        'cc' => 'nullable|integer',
        'body_type' => 'nullable|string|max:255',
        'chassisno' => 'nullable|string|max:255',
        'engine_no' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'insured' => 'nullable|string|max:255',
        'cover_details' => 'nullable|string',
        'notes' => 'nullable|string',
        'sum_insured' => 'nullable|numeric',
        'rate' => 'nullable|numeric',
        'premium' => 'nullable|numeric',
        'c_rate' => 'nullable|numeric',
        'commission' => 'nullable|numeric',
        'wht' => 'nullable|numeric',
        's_duty' => 'nullable|numeric',
        't_levy' => 'nullable|numeric',
        'pcf_levy' => 'nullable|numeric',
        'policy_charge' => 'nullable|numeric',
        'aa_charges' => 'nullable|numeric',
        'other_charges' => 'nullable|numeric',
        'gross_premium' => 'nullable|numeric',
        'net_premium' => 'nullable|numeric',
        'document_description' => 'nullable|string|max:255',
        'upload_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,docx,txt|max:2048',
    ]);

    // Begin database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // Generate a unique `fileno`
        $lastPolicy = Policy::latest()->first();
        $lastFileNo = $lastPolicy ? $lastPolicy->fileno : 'FN-00000';

        // Increment the number for the new policy fileno
        $newFileNo = 'FN-' . str_pad((int) substr($lastFileNo, 3) + 1, 5, '0', STR_PAD_LEFT);

        // Create a new Policy instance with the validated data
        $policy = new Policy($validatedData);

        // Set the business type for the policy
        $policy->bus_type = 'New';

        // Assign the generated `fileno`
        $policy->fileno = $newFileNo;

        // Handle single file upload, if provided
        if ($request->hasFile('upload_file')) {
            $file = $request->file('upload_file');
            $originalFileName = $file->getClientOriginalName();

            // Store the uploaded file in the 'uploads' directory in the 'public' disk
            $path = $file->storeAs('uploads', $originalFileName, 'public');

            // Log the file upload path for debugging
            \Log::info('Uploaded file path:', [$path]);

            // Assign the original file name to the 'documents' field of the policy
            $policy->documents = $originalFileName;
        }

        // Save the policy to the database
        $policy->save();

        // Log the success message
        \Log::info('Policy saved successfully:', $policy->toArray());

        // Commit the transaction to persist changes
        DB::commit();

        // Redirect to the policies index page with a success message
        return redirect()->route('policies.index')->with('success', 'Policy created successfully.');
    } catch (\Exception $e) {
        // Rollback the transaction in case of an error
        DB::rollBack();

        // Log the error message and stack trace for debugging
        \Log::error('An error occurred while saving the policy:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        // Redirect back with an error message
        return redirect()->back()->with('error', 'An error occurred while saving the policy.');
    }
}

public function index(Request $request)
{
    $filter = $request->query('filter', 'total'); // Default to 'total' if no filter is provided

    // Initialize query builder for policies
    $query = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
        ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
        ->join('insurers', 'policies.insurer_id', '=', 'insurers.id');

    // Apply filter based on the selected card
    switch ($filter) {
        case 'motor':
            // Assuming motor policies have IDs 35, 36, 37
            $query->whereIn('policy_type_id', [35, 36, 37]);
            break;
        case 'nonMotor':
            // Non-motor policies have policy types other than 35, 36, 37
            $query->whereNotIn('policy_type_id', [35, 36, 37]);
            break;
        case 'claims':
            // Policies with claims
            $query->whereHas('claims');
            break;
        case 'total':
        default:
            // No filter applied, retrieve all policies
            break;
    }

    // Fetch filtered policies
    $policies = $query->orderBy('fileno', 'desc')->get();

    // Calculate policy metrics
    $metrics = [
        'totalPolicies' => Policy::count(),
        'motorPolicies' => Policy::whereIn('policy_type_id', [35, 36, 37])->count(), // Motor policies
        'nonMotorPolicies' => Policy::whereNotIn('policy_type_id', [35, 36, 37])->count(), // Non-motor policies
        'policiesWithClaims' => Policy::whereHas('claims')->count(), // Policies with claims
        'expiredPolicies' => Policy::where('end_date', '<', now())->count(), // Expired policies
    ];

    // Pass policies and metrics to the view
    return view('policies.index', compact('policies', 'metrics'));
}


    
    public function show($id)
    {
        $policy = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->join('insurers','policies.insurer_id', '=', 'insurers.id')
            ->where('policies.id', $id)
            ->firstOrFail();
    
        return view('policies.show', compact('policy'));
    }
    

    public function edit($id)
{
    // Find the policy by its ID
    $policy = Policy::findOrFail($id);

    // Fetch available policy types, insurers, and vehicle types
    $availablePolicyTypes = PolicyTypes::pluck('type_name', 'id');
    $insurers = Insurer::pluck('name', 'id');
    $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make'); // Fetch vehicle types
    $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get(); // Fetch vehicle models

    // Pass the policy, available policy types, insurers, and vehicle types to the view
    return view('policies.edit', compact('policy', 'availablePolicyTypes', 'insurers', 'availableVehicleTypes', 'vehicleModels'));
}

    

    public function update(Request $request, $id)
{
    \Log::info('Received update data: ', $request->all());

    // Define validation rules
    $validatedData = $request->validate([
        'customer_code' => 'required|string|max:255',
        'customer_name' => 'required|string|max:255',
        'policy_type_id' => 'required|integer',
        'coverage' => 'required|string|max:255',
        'start_date' => 'required|date',
        'days' => 'nullable|integer',
        'end_date' => 'nullable|date',
        'insurer_id' => 'required|integer',
        'policy_no' => 'nullable|string|max:15',
        'reg_no' => 'nullable|string|max:255',
        'make' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'yom' => 'nullable|integer',
        'cc' => 'nullable|integer',
        'body_type' => 'nullable|string|max:255',
        'chassisno' => 'nullable|string|max:255',
        'engine_no' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'insured' => 'nullable|string|max:255',
        'cover_details' => 'nullable|string',
        'notes' => 'nullable|string',
        'sum_insured' => 'nullable|numeric',
        'rate' => 'nullable|numeric',
        'premium' => 'nullable|numeric',
        'c_rate' => 'nullable|numeric',
        'commission' => 'nullable|numeric',
        'wht' => 'nullable|numeric',
        's_duty' => 'nullable|numeric',
        't_levy' => 'nullable|numeric',
        'pcf_levy' => 'nullable|numeric',
        'policy_charge' => 'nullable|numeric',
        'aa_charges' => 'nullable|numeric',
        'other_charges' => 'nullable|numeric',
        'gross_premium' => 'nullable|numeric',
        'net_premium' => 'nullable|numeric',
        'document_description' => 'nullable|string|max:255',
        
        'upload_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,docx,txt|max:2048',
    ]);

    DB::beginTransaction();

    try {
        // Find the existing policy
        $policy = Policy::findOrFail($id);

        // Update policy fields with validated data
        $policy->fill($validatedData);

        // Handle single file upload
        if ($request->hasFile('upload_file')) {
            $file = $request->file('upload_file');
            $originalFileName = $file->getClientOriginalName();
            $path = $file->storeAs('uploads', $originalFileName, 'public');
            \Log::info('Uploaded file path:', [$path]);

            // Assign just the original file name to the documents field
            $policy->documents = $originalFileName; // Store only the file name
        }

        // Save the updated policy to the database
        $policy->save();

        \Log::info('Policy updated successfully:', $policy->toArray());

        DB::commit();

        return redirect()->route('policies.index')->with('success', 'Policy updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('An error occurred while updating the policy:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred while updating the policy.');
    }
}


    public function destroy($id)
    {
        $policy = Policy::findOrFail($id);
        $policy->delete();

        return redirect()->route('policies.index')->with('success', 'Policy deleted successfully.');
    }

    // Method in CustomerController2 for searching customers
    public function search(Request $request)
        {
            \Log::info('Search query:', ['query' => $request->input('query')]);

            $query = strtolower($request->input('query')); // Convert the search query to lowercase

            $customers = Customer::whereRaw('LOWER(first_name) LIKE ?', ["%{$query}%"])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$query}%"])
                        ->orWhereRaw('LOWER(corporate_name) LIKE ?', ["%{$query}%"])
                        ->orWhereRaw('LOWER(customer_code) LIKE ?', ["%{$query}%"])
                        ->get(['customer_code', 'first_name', 'last_name', 'corporate_name']);

            $customers->transform(function ($customer) {
                $customer->customer_name = $customer->first_name . ' ' . $customer->last_name . ' ' . $customer->corporate_name;
                return $customer;
            });

            \Log::info('Search results:', ['customers' => $customers]);

            return response()->json($customers);
        }

    
}
