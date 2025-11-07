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
use App\Models\Agent;
use Illuminate\Validation\ValidationException;
use App\Models\CompanyData; // NEW: company info for invoice prefix
use Barryvdh\DomPDF\Facade\Pdf; // NEW: PDF generation
use Illuminate\Database\QueryException; // NEW: to catch DB unique constraint errors

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
    
    // <<< ADDED: Fetch all agents to populate the dropdown >>>
    $agents = Agent::pluck('name', 'id'); 
    // <<< END ADDED >>>

    // Fetch vehicle data
    $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
    $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get();

    return view('policies.create', compact('insurers', 'availablePolicyTypes', 'availableVehicleTypes', 'vehicleModels', 'agents'));
}

public function getCustomerAgent(Request $request)
{
    $customer = \App\Models\Customer::where('customer_code', $request->customer_code)->first();
    $agent = $customer && $customer->agent_id ? \App\Models\Agent::find($customer->agent_id) : null;
    return response()->json([
        'agent_id' => $agent ? $agent->id : null,
        'agent_name' => $agent ? $agent->name : '',
    ]);
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
                'agent_id' => 'nullable|exists:agents,id',
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
                'description' => 'nullable|string|max:65535',
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

        DB::beginTransaction();
        try {
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

             

           // <<< CORRECTED AGENT LOGIC >>>
                    // 1. Prioritize agent_id from the form ($validatedData)
                    $agentIdForPolicy = $validatedData['agent_id'] ?? null;
                    $agentCommissionForPolicy = null;

                    // 2. If no agent was explicitly selected on the form, fall back to the customer's default agent.
                    if (empty($agentIdForPolicy)) {
                        $customer = Customer::where('customer_code', $request->customer_code)->first();
                        $agentIdForPolicy = $customer->agent_id ?? null; // Use customer's default
                    }

                    if ($agentIdForPolicy) {
                        // Check if the agent ID retrieved is valid and calculate commission
                        $agent = \App\Models\Agent::find($agentIdForPolicy);
                        if ($agent && ($request->gross_premium ?? 0) > 0) {
                            // Assuming 'commission_rate' is a percentage (e.g., 0.1 for 10%)
                            $agentCommissionForPolicy = ($validatedData['gross_premium'] ?? 0) * ($agent->commission_rate ?? 0);
                        }
                    }

                    // 3. Update $validatedData with the final determined agent info
                    $validatedData['agent_id'] = $agentIdForPolicy;
                    $validatedData['agent_commission'] = $agentCommissionForPolicy;

            // Inject invoice number if not provided in request before create
            if (empty($validatedData['invoice_no'] ?? null)) {
                $validatedData['invoice_no'] = $this->generateInvoiceNo();
            }

            $policy = Policy::create($validatedData);

            DB::commit();

            // --- RESPONSE ---
            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Policy created successfully. New File Number: ' . $policy->fileno,
                    'redirect' => route('policies.index')
                ]);
            }
            
            return redirect()->route('policies.index')
                ->with('success', 'Policy created successfully. New File Number: ' . $policy->fileno);
                
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
    /**
 * Display a listing of the policy resource.
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\View\View
 */
public function index(Request $request)
{
    $filter = $request->query('filter', 'total'); 
    $search = $request->query('search');

    // Initialize query builder for policies, joining necessary tables
    $baseQuery = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
        ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
        ->join('insurers', 'policies.insurer_id', '=', 'insurers.id');

    // Apply search
    if ($search) {
        $baseQuery->where(function($q) use ($search) {
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
            $baseQuery->whereIn('policy_type_id', [35, 36, 37]); 
            break;
        case 'nonMotor': 
            $baseQuery->whereNotIn('policy_type_id', [35, 36, 37]); 
            break;
        case 'claims': 
            $baseQuery->where('policies.id', -1);
            break;
    }
    
    // Clone the query before applying ordering for the metrics calculation
    $metricsQuery = clone $baseQuery;
    $metrics = $this->_getPolicyMetrics($metricsQuery);
    
    // Apply newest-first ordering server-side
    if (\Schema::hasColumn((new Policy())->getTable(), 'created_at')) {
        // Order by created_at (entry date) newest-first
        $baseQuery->orderBy('policies.created_at', 'desc');
        // Secondary order by id to stabilise ordering when timestamps are identical
        $baseQuery->orderBy('policies.id', 'desc');
    } elseif (\Schema::hasColumn((new Policy())->getTable(), 'updated_at')) {
        // Fallback to updated_at if created_at doesn't exist
        $baseQuery->orderBy('policies.updated_at', 'desc');
        $baseQuery->orderBy('policies.id', 'desc');
    } else {
        // Final fallback to id
        $baseQuery->orderBy('policies.id', 'desc');
    }

    // Apply pagination with the ordering
    $policies = $baseQuery->paginate(1000)->withQueryString();

    return view('policies.index', compact('policies', 'metrics', 'filter', 'search'));
} 
protected function _getPolicyMetrics($query)
    {
        // Get the total count from the current query context (which includes search if applied)
        $totalPolicies = $query->count();
        
        // To get specific counts, we may need to run separate counts on the base query.
        // We'll reset the builder state and apply specific filters for each metric.
        $motorPolicies = (clone $query)->whereIn('policy_type_id', [35, 36, 37])->count();
        $nonMotorPolicies = (clone $query)->whereNotIn('policy_type_id', [35, 36, 37])->count();
        //$policiesWithClaims = (clone $query)->where('has_claims', true)->count(); 

        return [
            'totalPolicies' => $totalPolicies,
            'motorPolicies' => $motorPolicies,
            'nonMotorPolicies' => $nonMotorPolicies,
            //'policiesWithClaims' => $policiesWithClaims,
        ];
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
        $docs = $policy->documents ?? null;
        // If it's already an array (e.g., casted earlier), keep it
        if (is_array($docs)) {
            $policy->documents = $docs;
        } elseif (is_string($docs)) {
            $decoded = json_decode($docs, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $policy->documents = $decoded;
            } else {
                // Try to recover if the value was stored as a PHP serialized array or malformed
                $policy->documents = [];
            }
        } else {
            // Null or unexpected type: default to empty array
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
            'policy_type_id' => 'required|integer|exists:policy_types,id',
            'coverage' => 'nullable|string|max:255',
            'start_date' => ['required', 'date', "after_or_equal:{$minStartDate}"],
            'days' => 'required|integer|min:1', 
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'insurer_id' => 'required|integer|exists:insurers,id',
            'policy_no' => 'nullable|string|max:255',
            'reg_no' => 'nullable|string|max:255',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'yom' => 'nullable|integer',
            'cc' => 'nullable|integer',
            'body_type' => 'nullable|string|max:255',
            'chassisno' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255',
            'engine_no' => 'nullable|string|regex:/^[a-zA-Z0-9-]*$/|max:255',
            'description' => 'nullable|string|max:65535',
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
            'agent_id' => 'nullable|exists:agents,id',
        ]);
    } catch (ValidationException $e) {
        throw $e;
    }

    DB::beginTransaction();

    try {
        // --- DATE CONSISTENCY CHECK --- (Existing logic is fine)
        if ($request->filled('start_date') && $request->filled('end_date') && $request->filled('days')) {
            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            
            $days = (int) $validatedData['days'];
            $diffExclusive = $startDate->diffInDays($endDate);
            
            \Log::debug('PolicyController@update: Date Check | Exclusive: ' . $diffExclusive . ' | Input Days: ' . $days);

            if ($days != $diffExclusive) {
                $errorMessage = "The calculated duration between the start date and end date ({$diffExclusive} days exclusive) does not match the Policy Days field ({$days}).";

                throw ValidationException::withMessages([
                    'days' => [$errorMessage],
                    'end_date' => [$errorMessage]
                ]);
            }
        }
        
        $policy = Policy::findOrFail($id);

        // Check if the policy is canceled (Existing logic is fine)
        if ($policy->isCancelled()) {
            return redirect()->route('policies.show', $policy->id)
                ->with('error', 'Canceled policies cannot be updated.');
        }

        // Assign the user_id from the authenticated user
        $validatedData['user_id'] = Auth::id();

        // Handle file uploads and document descriptions (Existing logic is fine)
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

        // Re-map descriptions to all documents (existing + new uploads) based on array index
        foreach ($documents as $index => &$document) {
            $document['description'] = $documentDescriptions[$index] ?? null; 
        }
        unset($document); 

        // Convert the documents array to a JSON string
        $policy->documents = json_encode($documents);
        
        // Remove auxiliary data not in the Policy model
        unset($validatedData['document_description']); 
        if (isset($validatedData['upload_file'])) {
            unset($validatedData['upload_file']); 
        }

        // ====================================================================
        // <<< ADDED AGENT COMMISSION LOGIC >>>
        // ====================================================================
        
        // 1. Prioritize agent_id from the form ($validatedData)
        $agentIdForPolicy = $validatedData['agent_id'] ?? null;
        $agentCommissionForPolicy = null;

        // 2. If no agent was explicitly selected on the form, fall back to the customer's default agent.
        if (empty($agentIdForPolicy)) {
            // Use customer_code from validated data (which is a required field)
            $customer = Customer::where('customer_code', $validatedData['customer_code'])->first();
            $agentIdForPolicy = $customer->agent_id ?? null; // Use customer's default
        }

        if ($agentIdForPolicy) {
            // Check if the agent ID retrieved is valid and calculate commission
            $agent = \App\Models\Agent::find($agentIdForPolicy);
            // Use validatedData['gross_premium'] for commission calculation
            if ($agent && ($validatedData['gross_premium'] ?? 0) > 0) {
                // Assuming 'commission_rate' is a decimal (e.g., 0.1 for 10%)
                $agentCommissionForPolicy = ($validatedData['gross_premium'] ?? 0) * ($agent->commission_rate ?? 0);
            }
        }

        // 3. Update $validatedData with the final determined agent info before filling
        $validatedData['agent_id'] = $agentIdForPolicy;
        $validatedData['agent_commission'] = $agentCommissionForPolicy;

        // ====================================================================
        // <<< END AGENT COMMISSION LOGIC >>>
        // ====================================================================

        // Update policy fields with validated data (including the new agent/commission)
        $policy->fill($validatedData);

        // if policy has no invoice number, generate and set it before saving
        if (empty($policy->invoice_no)) {
            $policy->invoice_no = $this->generateInvoiceNo();
        }

        $policy->save();

        DB::commit();

        // --- RESPONSE ---
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

    /**
     * Generate invoice number using Company Prefix / INV / YEAR / sequential (001)
     *
     * Format: [PREFIX]/INV/[YYYY]/[NNN]
     *
     * This implementation ensures the returned invoice_no does not already exist by
     * iterating the sequence until an unused candidate is found.
     */
    protected function generateInvoiceNo(): string
    {
        $company = CompanyData::first();
        $year = Carbon::now()->format('Y');

        // Determine prefix: first word's first three letters, uppercase, non-alpha stripped
        $prefix = 'CMP';
        if ($company && !empty($company->company_name)) {
            $firstWord = preg_split('/\s+/', trim($company->company_name))[0] ?? $company->company_name;
            $alpha = preg_replace('/[^A-Za-z]/', '', $firstWord);
            $prefix = strtoupper(substr($alpha, 0, 3)) ?: 'CMP';
        }

        $like = $prefix . '/INV/' . $year . '/%';

        // Find the last numeric sequence (best-effort)
        $lastInvoice = DB::table('policies')
            ->where('invoice_no', 'like', $like)
            ->orderBy('id', 'desc')
            ->value('invoice_no');

        $lastSeq = 0;
        if ($lastInvoice) {
            $parts = explode('/', $lastInvoice);
            $possible = end($parts);
            $possible = preg_replace('/[^0-9]/', '', $possible);
            $lastSeq = intval($possible);
        }

        // Try candidates until we find one that does not exist (protect against races)
        $nextSeq = max(1, $lastSeq + 1);
        $attempts = 0;
        $maxAttempts = 1000;

        do {
            $seqPadded = str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);
            $candidate = "{$prefix}/INV/{$year}/{$seqPadded}";

            $exists = DB::table('policies')->where('invoice_no', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }

            $nextSeq++;
            $attempts++;
        } while ($attempts < $maxAttempts);

        // As a last resort, append timestamp to guarantee uniqueness (very unlikely)
        return "{$prefix}/INV/{$year}/" . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT) . '-' . time();
    }

    /**
     * Print / Download Invoice PDF for a policy.
     * Safe-save the generated invoice_no with retry on unique-constraint failures.
     */
    public function printInvoice($id)
    {
        $policy = \App\Models\Policy::with(['customer', 'policyType', 'insurer', 'agent'])->findOrFail($id);

        $company = CompanyData::first();

        // If the policy has no invoice_no, attempt to generate and persist one safely.
        if (empty($policy->invoice_no)) {
            $maxRetries = 5;
            $saved = false;
            $lastException = null;

            for ($try = 0; $try < $maxRetries; $try++) {
                $candidate = $this->generateInvoiceNo();
                $policy->invoice_no = $candidate;

                try {
                    $policy->save();
                    $saved = true;
                    break;
                } catch (QueryException $ex) {
                    // Unique constraint race — try again with a new candidate
                    $lastException = $ex;
                    // small sleep to reduce tight-loop races (microseconds)
                    usleep(100000); // 100ms
                    continue;
                } catch (\Exception $ex) {
                    // Non-DB error: rethrow
                    throw $ex;
                }
            }

            if (! $saved) {
                // Log and throw the last DB exception (or a generic error)
                \Log::error('Failed to persist unique invoice_no after retries', [
                    'policy_id' => $policy->id,
                    'lastException' => $lastException ? $lastException->getMessage() : 'none'
                ]);
                throw $lastException ?? new \Exception('Failed to generate unique invoice number.');
            }
        }

        $pdf = Pdf::loadView('policies.invoice_pdf', compact('policy', 'company'))
                  ->setPaper('a4', 'portrait');

        // sanitize filename: invoice numbers contain slashes (/) which break header disposition
        $rawFilename = 'Invoice_' . ($policy->invoice_no ?? $policy->id) . '.pdf';
        $safeFilename = str_replace(['/', '\\'], ['-', '-'], $rawFilename);

        // force download; change to ->stream() if preferred
        return $pdf->download($safeFilename);
    }
}