<?php
namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer; 
use App\Models\PolicyTypes;
use App\Models\Insurer; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use PDF; // Assuming this is laravel-dompdf or similar

class PolicyController extends Controller
{
    /**
     * Show the form for creating a new policy.
     */
    public function getCreatePolicyForm()
    {
        // Use Model eloquent methods for consistency where possible
        $insurers = Insurer::pluck('name', 'id');
        $availablePolicyTypes = PolicyTypes::orderBy('type_name', 'asc')->pluck('type_name', 'id');
        
        // Fetch vehicle data
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get();

        return view('policies.create', compact('insurers', 'availablePolicyTypes', 'availableVehicleTypes', 'vehicleModels'));
    }

    /**
     * Store a newly created policy in storage.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        \Log::info('PolicyController@store: Received data: ', $request->all());

        $isAjax = $request->ajax() || $request->wantsJson();
        
        // Calculate the minimum allowed start date: Jan 1st of the current year.
        $minStartDate = Carbon::now()->startOfYear()->format('Y-m-d'); 
        
        try {
            // Define validation rules
            $validatedData = $request->validate([
                'customer_code' => 'required|string|max:255',
                'customer_name' => 'required|string|max:255',
                'policy_type_id' => 'required|integer|exists:policy_types,id', // Added exists rule
                'coverage' => 'nullable|string|max:255',
                
                // Date Restrictions
                'start_date' => ['required', 'date', "after_or_equal:{$minStartDate}"],
                'days' => 'required|integer|min:1', 
                'end_date' => ['required', 'date', 'after:start_date'],
                
                'insurer_id' => 'required|integer|exists:insurers,id', // Added exists rule
                'policy_no' => 'nullable|string|max:255',
                'reg_no' => 'nullable|string|max:255',
                'make' => 'nullable|string|max:255',
                'model' => 'nullable|string|max:255',
                'yom' => 'nullable|integer',
                'cc' => 'nullable|integer',
                'body_type' => 'nullable|string|max:255',
                'chassisno' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255', // Standardized regex
                'engine_no' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255', // Standardized regex
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
                'excess' => 'nullable|numeric',
                'ppl' => 'nullable|numeric',
                'road_rescue' => 'nullable|numeric',
                'pvt' => 'nullable|numeric',
                'courtesy_car' => 'nullable|numeric',
                'training_levy' => 'nullable|numeric',
            ]);

            \Log::info('PolicyController@store: Validation passed.', $validatedData);
        } catch (ValidationException $e) {
            \Log::error('PolicyController@store: Validation failed (Pre-DB transaction):', $e->errors());
            // Re-throw ValidationException to trigger the default Laravel/AJAX error response
            throw $e;
        }

        try {
            DB::beginTransaction();

            // --- BUSINESS LOGIC: DATE CONSISTENCY CHECK (Exclusive Day Count) ---
            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            
            $days = (int) $validatedData['days']; 
            $calculatedExclusiveDays = $startDate->diffInDays($endDate); 
            
            \Log::debug('PolicyController@store: Date Check | Calculated Exclusive Days: ' . $calculatedExclusiveDays . ' | Input Days: ' . $days);

            // Using non-strict comparison (`!=`) to match numeric values regardless of type (e.g., int vs string)
            if ($days != $calculatedExclusiveDays) {
                $errorMessage = "The Policy Days field ({$days}) must match the exclusive day count between the Start Date and End Date ({$calculatedExclusiveDays} days). Please review the dates or the Policy Days value.";

                throw ValidationException::withMessages([
                    'days' => [$errorMessage],
                    'end_date' => [$errorMessage]
                ]);
            }
            
            // --- GENERATE UNIQUE FILENO ---
            $lastPolicy = Policy::latest()->first();
            $lastFileNo = $lastPolicy ? $lastPolicy->fileno : 'FN-00000';
            $lastFileNoNumber = (int) substr($lastFileNo, 3);
            $newFileNoNumber = $lastFileNoNumber + 1;
            
            do {
                $newFileNo = 'FN-' . str_pad($newFileNoNumber, 5, '0', STR_PAD_LEFT);
                $newFileNoNumber++;
            } while (Policy::where('fileno', $newFileNo)->exists());

            // --- HANDLE FILE UPLOADS ---
            $documents = [];
            $documentDescriptions = array_filter($request->input('document_description', [])); // Filter null/empty descriptions

            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $key => $file) {
                    // Check if file is valid before attempting to store
                    if (!$file->isValid()) {
                        throw new \Exception("Uploaded file at index {$key} is invalid.");
                    }
                    
                    $originalFileName = $file->getClientOriginalName();
                    // Store the file in the 'uploads' directory under the 'public' disk
                    $path = $file->storeAs('uploads', $originalFileName, 'public');

                    $description = $documentDescriptions[$key] ?? null;

                    $documents[] = [
                        'name' => $originalFileName,
                        'path' => $path,
                        'description' => $description,
                    ];
                }
            }
            
            // --- PREPARE FINAL DATA AND SAVE ---
            $validatedData['user_id'] = Auth::id();
            $validatedData['fileno'] = $newFileNo;
            $validatedData['bus_type'] = $validatedData['bus_type'] ?? 'New'; // Assuming 'bus_type' can be in validatedData or defaulted
            $validatedData['documents'] = json_encode($documents);
            
            // Remove auxiliary data not in the Policy model
            unset($validatedData['document_description']); 
            if (isset($validatedData['upload_file'])) {
                unset($validatedData['upload_file']); 
            }

            $newPolicy = Policy::create($validatedData);
            
            DB::commit();

            // --- RESPONSE ---
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Policy created successfully. New File Number: ' . $newPolicy->fileno,
                    'redirect' => route('policies.index')
                ]);
            }
            
            return redirect()->route('policies.index')
                ->with('success', 'Policy created successfully. New File Number: ' . $newPolicy->fileno);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('PolicyController@store: Error saving policy (DB transaction failed):', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $isValidationError = $e instanceof ValidationException;
            $errorMessage = $isValidationError 
                                ? 'Validation failed. Please correct the highlighted fields.'
                                : (config('app.debug') ? $e->getMessage() : 'An internal error occurred while saving the policy. Please check the logs.');
            
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'errors' => $isValidationError ? $e->errors() : []
                ], $isValidationError ? 422 : 500);
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Display a listing of the policy resource.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'total'); 
        $search = $request->query('search');
        $perPage = $request->input('per_page', 10); 

        // Initialize query builder for policies
        $query = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->join('insurers', 'policies.insurer_id', '=', 'insurers.id');

        // Apply search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('policies.fileno', 'like', "%{$search}%")
                  ->orWhere('policies.customer_code', 'like', "%{$search}%")
                  ->orWhere('policies.customer_name', 'like', "%{$search}%")
                  ->orWhere('policies.policy_no', 'like', "%{$search}%")
                  ->orWhere('policies.reg_no', 'like', "%{$search}%")
                  ->orWhere('policies.make', 'like', "%{$search}%")
                  ->orWhere('policies.model', 'like', "%{$search}%");
            });
        }

        // Apply filter
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
                // Policies with claims (requires Policy model to have a 'claims' relationship)
                $query->whereHas('claims');
                break;
            case 'expired':
                // Policies that have expired
                $query->where('end_date', '<', now());
                break;
            case 'total':
            default:
                break;
        }

        // Enforce newest-first ordering server-side (prefer created_at, fallback to id)
        // This is more reliable than client-side sorting for ensuring the newest policies appear first.
        if (\Schema::hasColumn((new Policy())->getTable(), 'updated_at')) {
            // Order by the Entry Date (updated_at) newest-first
            $query->orderBy('policies.updated_at', 'desc');
            // Secondary order by id to stabilise ordering when timestamps are identical
            $query->orderBy('policies.id', 'desc');
        } elseif (\Schema::hasColumn((new Policy())->getTable(), 'created_at')) {
            $query->orderBy('policies.created_at', 'desc');
            $query->orderBy('policies.id', 'desc');
        } else {
            $query->orderBy('policies.id', 'desc');
        }

        $policies = $query->paginate($perPage);

        // Calculate policy metrics (NOTE: This involves multiple queries - consider optimization/caching)
        $metrics = [
            'totalPolicies' => Policy::count(),
            'motorPolicies' => Policy::whereIn('policy_type_id', [35, 36, 37])->count(),
            'nonMotorPolicies' => Policy::whereNotIn('policy_type_id', [35, 36, 37])->count(),
            'policiesWithClaims' => Policy::whereHas('claims')->count(), 
            'expiredPolicies' => Policy::where('end_date', '<', now())->count(),
        ];

        return view('policies.index', compact('policies', 'metrics', 'perPage', 'search', 'filter'));
    }

    /**
     * Display the specified policy.
     * * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $policy = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->join('insurers', 'policies.insurer_id', '=', 'insurers.id')
            ->where('policies.id', $id)
            ->firstOrFail();

        // Safely decode the documents field
        $docs = $policy->documents ?? '[]';
        $policy->documents = json_decode($docs, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($policy->documents)) {
            $policy->documents = [];
        }

        return view('policies.show', compact('policy'));
    }

    /**
     * Generate and stream a PDF of the debit note for the policy.
     * * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function printDebitNote($id)
    {
        set_time_limit(120);

        $policy = Policy::with('policyType', 'insurer', 'customer')->findOrFail($id);
        
        $pdf = PDF::loadView('policies.debit_note_pdf', ['policy' => $policy])
                   ->setPaper('a4', 'portrait')
                   ->setOptions([
                       'margin-top'    => 0,
                       'margin-right'  => 0,
                       'margin-bottom' => 0,
                       'margin-left'   => 0,
                   ]);

        return $pdf->stream("debit_note_{$policy->fileno}.pdf"); // Dynamic filename
    }

    /**
     * Generate and stream a PDF of the credit note for the policy.
     */
    public function printCreditNote($id)
    {
        set_time_limit(120);

        $policy = Policy::with('policyType', 'insurer', 'customer')->findOrFail($id);

        $pdf = PDF::loadView('policies.credit_note_pdf', ['policy' => $policy])
                   ->setPaper('a4', 'portrait')
                   ->setOptions([
                       'margin-top'    => 0,
                       'margin-right'  => 0,
                       'margin-bottom' => 0,
                       'margin-left'   => 0,
                   ]);

        return $pdf->stream("credit_note_{$policy->fileno}.pdf");
    }

    /**
     * Show the form for editing the specified policy.
     * * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $policy = Policy::findOrFail($id);
        if ($policy->isCancelled()) {
            return redirect()->route('policies.show', $policy->id)
                ->with('error', 'Canceled policies cannot be edited.');
        }

        $availablePolicyTypes = PolicyTypes::pluck('type_name', 'id');
        $insurers = Insurer::pluck('name', 'id');
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get();

        // Decode the documents JSON string to an array safely
        $documents = json_decode($policy->documents, true) ?? [];

        // Compute display-friendly values for code/name and client type
        $displayCustomerCode = $policy->customer_code ?? optional($policy->customer)->customer_code;
        $displayCustomerName = $policy->customer_name ?? optional($policy->customer)->customer_name;
        // Default client_type based on customer_code existence
        $clientType = $policy->client_type ?? ($displayCustomerCode ? 'customer' : 'lead');

        return view('policies.edit', compact(
            'policy',
            'availablePolicyTypes',
            'insurers',
            'availableVehicleTypes',
            'vehicleModels',
            'documents',
            'displayCustomerCode',
            'displayCustomerName',
            'clientType'
        ));
    }

    /**
     * Update the specified policy in storage.
     * * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        \Log::info('PolicyController@update: Received update data: ', $request->all());

        $isAjax = $request->ajax() || $request->wantsJson();
        $minStartDate = Carbon::now()->startOfYear()->format('Y-m-d'); 
        
        try {
            // Define validation rules
            $validatedData = $request->validate([
                'fileno' => 'nullable|string|max:255',
                'customer_code' => 'required|string|max:255',
                'customer_name' => 'required|string|max:255',
                'policy_type_id' => 'required|integer|exists:policy_types,id', // Added exists
                'coverage' => 'nullable|string|max:255',
                'start_date' => ['required', 'date', "after_or_equal:{$minStartDate}"],
                'days' => 'required|integer|min:1', 
                'end_date' => ['nullable', 'date', 'after:start_date'],
                'insurer_id' => 'required|integer|exists:insurers,id', // Added exists
                'policy_no' => 'nullable|string|max:255',
                'reg_no' => 'nullable|string|max:255',
                'make' => 'nullable|string|max:255',
                'model' => 'nullable|string|max:255',
                'yom' => 'nullable|integer',
                'cc' => 'nullable|integer',
                'body_type' => 'nullable|string|max:255',
                'chassisno' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255', // Standardized regex
                'engine_no' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255', // Standardized regex
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
                'excess' => 'nullable|numeric',
                'ppl' => 'nullable|numeric',
                'road_rescue' => 'nullable|numeric',
                'pvt' => 'nullable|numeric',
                'courtesy_car' => 'nullable|numeric',
                'training_levy' => 'nullable|numeric',
            ]);
        } catch (ValidationException $e) {
            // No need for explicit rollback here as no transaction has started
            throw $e;
        }

        DB::beginTransaction();

        try {
            // --- DATE CONSISTENCY CHECK ---
            if ($request->filled('start_date') && $request->filled('end_date') && $request->filled('days')) {
                $startDate = Carbon::parse($validatedData['start_date']);
                $endDate = Carbon::parse($validatedData['end_date']);
                
                $days = (int) $validatedData['days'];
                $diffExclusive = $startDate->diffInDays($endDate);
                
                \Log::debug('PolicyController@update: Date Check | Exclusive: ' . $diffExclusive . ' | Input Days: ' . $days);

                // Use non-strict comparison. Check against exclusive day count as in 'store'.
                if ($days != $diffExclusive) {
                    $errorMessage = "The calculated duration between the start date and end date ({$diffExclusive} days exclusive) does not match the Policy Days field ({$days}).";

                    throw ValidationException::withMessages([
                        'days' => [$errorMessage],
                        'end_date' => [$errorMessage]
                    ]);
                }
            }
            
            $policy = Policy::findOrFail($id);

            // Check if the policy is canceled
            if ($policy->isCancelled()) {
                return redirect()->route('policies.show', $policy->id)
                    ->with('error', 'Canceled policies cannot be updated.');
            }

            // Assign the user_id from the authenticated user
            $validatedData['user_id'] = Auth::id();

            // Update policy fields with validated data
            $policy->fill($validatedData);

            // Handle file uploads and document descriptions
            $documents = json_decode($policy->documents, true) ?? [];
            $documentDescriptions = array_filter($request->input('document_description', []));

            // --- PROCESS NEW UPLOADS ---
            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $key => $file) {
                    if (!$file->isValid()) {
                        throw new \Exception("Uploaded file at index {$key} is invalid.");
                    }
                    
                    $originalFileName = $file->getClientOriginalName();
                    $path = $file->storeAs('uploads', $originalFileName, 'public');

                    $description = $documentDescriptions[$key] ?? null;

                    $documents[] = [
                        'name' => $originalFileName,
                        'path' => $path,
                        'description' => $description,
                    ];
                }
            }

            // The code below assumes $documentDescriptions contains an ordered list of descriptions 
            // where the first N items correspond to the N *existing* documents plus any newly uploaded ones. 
            // It needs careful management if documents are removed in the UI.
            // A more robust approach would be to send back the existing document details with IDs and 
            // only process new uploads here, but maintaining your existing structure:
            
            // Re-map descriptions to all documents (existing + new uploads) based on array index
            // Assuming the frontend ensures $documentDescriptions aligns correctly.
            foreach ($documents as $index => &$document) {
                // $documentDescriptions is an array of all descriptions from the form, new and old
                $document['description'] = $documentDescriptions[$index] ?? null; 
            }
            unset($document); // Unset the reference

            // Convert the documents array to a JSON string
            $policy->documents = json_encode($documents);
            
            // Remove auxiliary data not in the Policy model
            unset($validatedData['document_description']); 
            if (isset($validatedData['upload_file'])) {
                unset($validatedData['upload_file']); 
            }

            $policy->save();

            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Policy updated successfully.',
                    'redirect' => route('policies.index'),
                ]);
            }
            return redirect()->route('policies.index')->with('success', 'Policy updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('An error occurred while updating the policy:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $isValidationError = $e instanceof ValidationException;
            $errorMessage = $isValidationError 
                                ? 'Validation failed. Please correct the highlighted fields.'
                                : (config('app.debug') ? $e->getMessage() : 'An internal error occurred while updating the policy. Please check the logs.');

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'errors' => $isValidationError ? $e->errors() : []
                ], $isValidationError ? 422 : 500);
            }
            return redirect()->back()->with('error', $errorMessage)->withInput();
        }
    }

    /**
     * Remove the specified policy from storage.
     * * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $policy = Policy::findOrFail($id);
        $policy->delete();

        return redirect()->route('policies.index')->with('success', 'Policy deleted successfully.');
    }

    /**
     * Search for customers by name or code.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = strtolower($request->input('query'));
    
        $customers = Customer::whereRaw('LOWER(first_name) LIKE ?', ["%{$query}%"])
            ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$query}%"])
            ->orWhereRaw('LOWER(corporate_name) LIKE ?', ["%{$query}%"])
            ->orWhereRaw('LOWER(customer_code) LIKE ?', ["%{$query}%"])
            ->get(['customer_code', 'first_name', 'last_name', 'corporate_name', 'customer_type']);
    
        // Transform the collection to use the customer_name accessor if defined
        $customers->transform(function ($customer) {
            return [
                'customer_code' => $customer->customer_code,
                'customer_name' => $customer->customer_name, // Uses the Customer model accessor
            ];
        });
    
        return response()->json($customers);
    }
}