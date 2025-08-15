<?php
namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer; 
use App\Models\PolicyTypes;
use App\Models\Insurer; 
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDF;

class CustomerController2 extends Controller
{
    public function getCreatePolicyForm()
    {
        $insurers = DB::table('insurers')->pluck('name', 'id');
        $availablePolicyTypes = DB::table('policy_types')->orderBy('type_name', 'asc')->pluck('type_name', 'id');
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get(); // Ensure 'make' and 'model' are selected

        return view('policies.create', compact('insurers', 'availablePolicyTypes', 'availableVehicleTypes', 'vehicleModels'));
    }

    public function store(Request $request)
    {
        // Log the incoming request data for debugging purposes
        \Log::info('Received data: ', $request->all());
        try {
            // Define validation rules for the incoming request
            $validatedData = $request->validate([
                'customer_code' => 'required|string|max:255',
                'customer_name' => 'required|string|max:255',
                'policy_type_id' => 'required|integer',
                'coverage' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'days' => 'nullable|integer',
                'end_date' => 'nullable|date',
                'insurer_id' => 'required|integer',
                'policy_no' => 'nullable|string|max:255',
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
                'rate' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
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
                'document_description.*' => 'nullable|string|max:255',
                'upload_file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,docx,txt|max:2048',
            ]);

            \Log::info('Validation passed:', $validatedData);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            throw $e;
        }

        try {
            // Begin database transaction
            DB::beginTransaction();

            // Generate unique `fileno`
            $lastPolicy = Policy::latest()->first();
            $lastFileNo = $lastPolicy ? $lastPolicy->fileno : 'FN-00000';
            $lastFileNoNumber = (int) substr($lastFileNo, 3);
            $newFileNoNumber = $lastFileNoNumber + 1;

            // Ensure file number uniqueness in case of concurrent requests
            do {
                $newFileNo = 'FN-' . str_pad($newFileNoNumber, 5, '0', STR_PAD_LEFT);
                $newFileNoNumber++;
            } while (Policy::where('fileno', $newFileNo)->exists());

            // Handle file uploads and document descriptions
            $documents = [];
            $documentDescriptions = $request->input('document_description', []);

            // Filter out any null or empty values from document_descriptions
            $documentDescriptions = array_filter($documentDescriptions, function($value) {
                return $value !== null && $value !== '';
            });

            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $key => $file) {
                    try {
                        $originalFileName = $file->getClientOriginalName();
                        $path = $file->storeAs('uploads', $originalFileName, 'public');
    
                        // Log the file upload path for debugging
                        \Log::info('Uploaded file path:', [$path]);
    
                        // Get the description for this file
                        $description = isset($documentDescriptions[$key]) ? $documentDescriptions[$key] : null;
    
                        $documents[] = [
                            'name' => $originalFileName,
                            'path' => $path,
                            'description' => $description,
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Error uploading file:', [
                            'message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
    

            // Assign additional fields
            $validatedData['user_id'] = Auth::id();
            $validatedData['fileno'] = $newFileNo;
            $validatedData['bus_type'] = 'New';
            $validatedData['document_description'] = $documentDescriptions;
            $validatedData['documents'] = json_encode($documents);

            // Debugging: Check final data before saving
            \Log::info('Data to be saved:', $validatedData);

            // Save the policy
            $newPolicy = Policy::create($validatedData);

            // Commit transaction if everything is successful
            DB::commit();

            // Redirect or respond based on context
            return redirect()->route('policies.index')->with('success', 'Policy created successfully. New File Number: ' . $newPolicy->fileno);
        } catch (\Exception $e) {
            // Rollback transaction if an error occurs
            DB::rollBack();

            \Log::error('Error saving policy:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect back with error message and old input data for user convenience
            return redirect()->back()->with('error', 'An error occurred while saving the policy.')->withInput();
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
            ->join('insurers', 'policies.insurer_id', '=', 'insurers.id')
            ->where('policies.id', $id)
            ->firstOrFail();

        // Decode the documents JSON string to an array
        $policy->documents = json_decode($policy->documents, true) ?? [];

        return view('policies.show', compact('policy'));
    }

    public function printDebitNote($id)
    {
        set_time_limit(120); // Increase execution time

        $policy = Policy::with('policyType', 'insurer')->find($id);
        if (!$policy) {
            abort(404);
        }

        // Pass the policy to the view directly
        $pdf = PDF::loadView('policies.debit_note_pdf', ['policy' => $policy])
                   ->setPaper('a4', 'portrait') // Set paper size and orientation
                   ->setOptions([
                       'margin-top'    => 0,
                       'margin-right'  => 0,
                       'margin-bottom' => 0,
                       'margin-left'   => 0,
                   ]);

        // Return the PDF for printing
        return $pdf->stream('debit_note.pdf');
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

        // Decode the documents JSON string to an array
        $documents = json_decode($policy->documents, true) ?? [];

        // Pass the policy, available policy types, insurers, vehicle types, and documents to the view
        return view('policies.edit', compact('policy', 'availablePolicyTypes', 'insurers', 'availableVehicleTypes', 'vehicleModels', 'documents'));
    }

    public function update(Request $request, $id)
    {
        // Log the incoming request data for debugging purposes
        \Log::info('Received update data: ', $request->all());

        // Define validation rules
        $validatedData = $request->validate([
            'customer_code' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'policy_type_id' => 'required|integer',
            'coverage' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'days' => 'nullable|integer',
            'end_date' => 'nullable|date',
            'insurer_id' => 'required|integer',
            'policy_no' => 'nullable|string|max:255',
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
            'rate' => 'nullable|numeric|regex:/^\d+(\.\d+)?$/',
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
            'document_description.*' => 'nullable|string|max:255',
            'upload_file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,docx,txt|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // Find the existing policy
            $policy = Policy::findOrFail($id);

            // Assign the user_id from the authenticated user
            $validatedData['user_id'] = Auth::id();

            // Update policy fields with validated data
            $policy->fill($validatedData);

            // Handle file uploads and document descriptions
            $documents = json_decode($policy->documents, true) ?? [];
            $documentDescriptions = $request->input('document_description', []);

            // Filter out any null or empty values from document_descriptions
            $documentDescriptions = array_filter($documentDescriptions, function($value) {
                return $value !== null && $value !== '';
            });

            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $key => $file) {
                    $originalFileName = $file->getClientOriginalName();
                    $path = $file->storeAs('uploads', $originalFileName, 'public');

                    // Log the file upload path for debugging
                    \Log::info('Uploaded file path:', [$path]);

                    // Get the description for this file
                    $description = isset($documentDescriptions[$key]) ? $documentDescriptions[$key] : null;

                    $documents[] = [
                        'name' => $originalFileName,
                        'path' => $path,
                        'description' => $description,
                    ];
                }
            }

            // Ensure that descriptions for existing documents are updated
            foreach ($documents as $index => &$document) {
                if (isset($documentDescriptions[$index])) {
                    $document['description'] = $documentDescriptions[$index];
                }
            }

            // Convert the documents array to a JSON string
            $policy->documents = json_encode($documents);

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
