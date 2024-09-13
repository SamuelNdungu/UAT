<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy; 
use Illuminate\Support\Facades\DB;
use App\Models\Customer; 
use App\Models\PolicyTypes;
use App\Models\Insurer; 
use Carbon\Carbon;

class RenewalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $now = Carbon::now();
    $next10Days = $now->copy()->addDays(10);
    $next30Days = $now->copy()->addDays(30);
    $next60Days = $now->copy()->addDays(60);

    // Get the filter from the query string, default to 'total'
    $filter = $request->query('filter', 'total');

    // Initialize the query builder
    $policiesQuery = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
        ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
        ->join('insurers', 'policies.insurer_id', '=', 'insurers.id')
        ->orderBy('policies.fileno', 'desc');

    // Apply the appropriate filter
    switch ($filter) {
        case '10Days':
            $policiesQuery->whereBetween('policies.end_date', [$now, $next10Days]);
            break;
        case '30Days':
            $policiesQuery->whereBetween('policies.end_date', [$now, $next30Days]);
            break;
        case '60Days':
            $policiesQuery->whereBetween('policies.end_date', [$now, $next60Days]);
            break;
        case 'expired':
            $policiesQuery->where('policies.end_date', '<', $now);
            break;
        default:
            // No filter is applied, so show all policies
            break;
    }

    // Fetch the filtered policies
    $policies = $policiesQuery->get();

    // Calculate metrics for the cards
    $metrics = [
        'totalPolicies' => Policy::count(),
        '10Days' => Policy::whereBetween('end_date', [$now, $next10Days])->count(),
        '30Days' => Policy::whereBetween('end_date', [$now, $next30Days])->count(),
        '60Days' => Policy::whereBetween('end_date', [$now, $next60Days])->count(),
        'expiredPolicies' => Policy::where('end_date', '<', $now)->count(),
    ];

    // Pass the filtered policies and metrics to the view
    return view('renewals.index', compact('policies', 'metrics'));
}


    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
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
    return view('renewals.edit', compact('policy', 'availablePolicyTypes', 'insurers', 'availableVehicleTypes', 'vehicleModels'));
}


    /**
     * Update the specified resource in storage.
     */
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
                // Update the bus_type to 'renewed'
                
            
            'upload_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,docx,txt|max:2048',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Find the existing policy
            $policy = Policy::findOrFail($id);

            $policy->bus_type = 'Renewed';
    
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
    
            return redirect()->route('renewals.index')->with('success', 'Policy updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('An error occurred while updating the policy:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'An error occurred while updating the policy.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function search(Request $request)
{
    $query = $request->input('query');

    // Search policies based on multiple criteria
    $policies = Policy::select('policies.*', 'policy_types.type_name as policy_type_name', 'insurers.name as insurer_name')
        ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
        ->join('insurers', 'policies.insurer_id', '=', 'insurers.id')
        ->where(function ($queryBuilder) use ($query) {
            $queryBuilder->where('policies.fileno', 'like', "%$query%")
                ->orWhere('policies.customer_code', 'like', "%$query%")
                ->orWhere('policies.customer_name', 'like', "%$query%")
                ->orWhere('policy_types.type_name', 'like', "%$query%")
                ->orWhere('insurers.name', 'like', "%$query%")
                ->orWhere('policies.policy_no', 'like', "%$query%");
        })
        ->orderBy('policies.fileno', 'desc')
        ->get();

    // Recalculate metrics to pass to the view
    $now = Carbon::now();
    $next10Days = $now->copy()->addDays(10);
    $next30Days = $now->copy()->addDays(30);
    $next60Days = $now->copy()->addDays(60);

    $metrics = [
        'totalPolicies' => Policy::count(),
        'activePolicies' => Policy::where('coverage', "Comprehensive")->count(),
        'inactivePolicies' => Policy::where('coverage', "TPO")->count(),
        'expiredPolicies' => Policy::where('end_date', '<', $now)->count(),
        '10Days' => Policy::whereBetween('end_date', [$now, $next10Days])->where('end_date', '>=', $now)->count(),
        '30Days' => Policy::whereBetween('end_date', [$next10Days->addSecond(), $next30Days])->count(),
        '60Days' => Policy::whereBetween('end_date', [$next30Days->addSecond(), $next60Days])->count(),
    ];

    return view('renewals.index', compact('policies', 'metrics'));
}

}
