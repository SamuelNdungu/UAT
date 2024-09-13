<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Policy;
use App\Models\Insurer;
use App\Models\Receipt;
use App\Models\PolicyTypes;
use App\Models\PolicyType;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Claim;
use App\Models\ClaimEvent;
use Illuminate\Support\Facades\Log;

class ClaimController extends Controller
{

    public function index(Request $request)
    {
        // Calculate metrics for claims
        $metrics = [
            'totalClaims' => Claim::count(),
            'openClaims' => Claim::where('status', 'Open')->count(),
            'closedClaims' => Claim::where('status', 'Closed')->count(),
            'pendingClaims' => Claim::where('status', 'Pending')->count(),
        ];
    
        // Determine the filter based on the request query parameter
        $filter = $request->query('filter', 'all'); // Default to 'all'
    
        // Fetch claims with policy and customer information
        $claimsQuery = DB::table('claims')
            ->join('policies', 'claims.policy_id', '=', 'policies.id')
            ->join('customers', 'claims.customer_code', '=', 'customers.customer_code')
            ->leftJoin('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->select(
                'claims.*',
                'policies.policy_no as policy_number', 
                'policy_types.type_name as policy_type_name',
                'policies.reg_no',
                'policies.sum_insured',
                DB::raw("
                    CASE 
                        WHEN customers.customer_type = 'Individual' 
                            THEN CONCAT(COALESCE(customers.first_name, ''), ' ', COALESCE(customers.last_name, ''), ' ', COALESCE(customers.surname, ''))
                        WHEN customers.customer_type = 'Corporate' 
                            THEN COALESCE(customers.corporate_name, 'N/A')
                        ELSE 'N/A'
                    END as customer_name
                "),
                'customers.customer_code'
            );
    
        // Apply filtering based on the status selected in the cards
        if ($filter !== 'all') {
            $claimsQuery->where('claims.status', $filter);
        }
    
        // Fetch filtered claims
        $claims = $claimsQuery->paginate(10);
    
        // Pass metrics and claims data to the view
        return view('claims.index', compact('metrics', 'claims'));
    }
    



    

    public function create()
    {
        // Generate a unique claim number
        $lastClaim = Claim::latest()->first();
        $lastClaimNumber = $lastClaim ? $lastClaim->claim_number : 'CLM-00000';

        // Increment the number for the new claim
        $newClaimNumber = 'CLM-' . str_pad((int)substr($lastClaimNumber, 4) + 1, 5, '0', STR_PAD_LEFT);
        
        // Fetch all policies to associate with a claim
        $policies = Policy::all();

        // Log the creation of a new claim
        Log::info('Creating a new claim with claim number: ' . $newClaimNumber);

        return view('claims.create', compact('policies', 'newClaimNumber'));
    }

    public function show(Claim $claim)
    {
        // Eager load the policy with its type
        $claim->load('policy.policy_type');
    
        // Log the display of the claim details
        Log::info('Displaying details for claim ID: ' . $claim->id);
    
        return view('claims.show', compact('claim'));
    }
    
    

    public function edit(Claim $claim)
    {
        $policies = Policy::all();
        $events = $claim->events()->orderBy('event_date', 'desc')->get();

        return view('claims.edit', compact('claim', 'policies', 'events'));
    }



    public function store(Request $request)
    {
        // Log the incoming request data for debugging purposes
        \Log::info('Received data: ', $request->all());
    
        // Define validation rules for the incoming request
        $validatedData = $request->validate([
            'fileno' => 'required|string|max:255',
            'customer_code' => 'required|string|max:255',
            'claim_number' => 'required|string|max:255|unique:claims',
            'reported_date' => 'required|date',
            'type_of_loss' => 'required|string|max:255',
            'loss_details' => 'nullable|string',
            'loss_date' => 'required|date',
            'followup_date' => 'nullable|date',
            'claimant_name' => 'required|string|max:255',
            'amount_claimed' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'status' => 'required|string',
            'upload_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);
    
        // Begin database transaction to ensure atomicity
        DB::beginTransaction();
    
        try {
            // Retrieve the policy_id using the fileno
            $policy = Policy::where('fileno', $validatedData['fileno'])->firstOrFail();
            $validatedData['policy_id'] = $policy->id;
    
            // Handle file upload
            if ($request->hasFile('upload_file')) {
                $filePath = $request->file('upload_file')->store('documents');
                $validatedData['upload_file'] = $filePath; // Store file path in the database
            }
    
            // Create a new claim with the validated data
            $claim = new Claim($validatedData);
    
            // Save the claim to the database
            $claim->save();
    
            // Log the success message
            \Log::info('Claim saved successfully:', $claim->toArray());
    
            // Commit the transaction to persist changes
            DB::commit();
    
            // Redirect to the claims index page with a success message
            return redirect()->route('claims.index')->with('success', 'Claim created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
    
            // Log the error message and stack trace for debugging
            \Log::error('An error occurred while saving the claim:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            // Redirect back with an error message
            return redirect()->back()->with('error', 'An error occurred while saving the claim.');
        }
    }

    


    public function update(Request $request, Claim $claim)
    {
        // Define validation rules for updating the claim
        $validated = $request->validate([
            'fileno' => 'nullable|string|max:255',
            'reported_date' => 'required|date',
            'type_of_loss' => 'required|string|max:255',
            'loss_date' => 'required|date',
            'followup_date' => 'nullable|date',
            'claimant_name' => 'required|string|max:255',
            'amount_claimed' => 'required|numeric',
            'amount_paid' => 'nullable|numeric',
            'status' => 'required|string',
            'loss_details' => 'nullable|string',
            'events' => 'nullable|array',
            'events.*.event_date' => 'required_with:events|date',
            'events.*.event_type' => 'required_with:events|string|max:255',
            'events.*.description' => 'nullable|string',
        ]);

        // Log the update action
        Log::info('Updating claim ID: ' . $claim->id, $validated);

        // Update the claim with the validated data
        $claim->update($validated);

        // If there are events provided, update or create them
        if ($request->has('events')) {
            // Clear existing events
            $claim->events()->delete();

            // Add the new events
            foreach ($request->events as $event) {
                $claim->events()->create($event);
            }
        }

        // Log the success message
        Log::info('Claim updated successfully:', $claim->toArray());

        return redirect()->route('claims.index')->with('success', 'Claim updated successfully.');
    }

    public function getPolicyDetails(Request $request)
    {
        // Fetch the file number from the request query
        $fileno = $request->query('fileno');
        Log::info('Searching for policy with fileno: ' . $fileno);

        // Retrieve the policy details based on the file number
        $policy = DB::table('policies')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->join('insurers', 'policies.insurer_id', '=', 'insurers.id') // Assuming the insurer's name is stored in a column called 'name'
            ->join('customers', 'policies.customer_code', '=', 'customers.customer_code')
            ->select(
                'policies.customer_code',
                'policies.fileno',
                'policies.customer_name',
                'policy_types.type_name as policy_type', // Adjust this line based on your column name
                'policies.reg_no',
                'policies.make',
                'policies.model',
                'policies.description',
                'policies.start_date',
                'policies.end_date',
                'insurers.name as insurer', // Assuming insurers table has a 'name' column
                'policies.sum_insured',
                'policies.gross_premium',
                'policies.paid_amount',
                'policies.outstanding_amount as due_amount',
                'customers.phone', // Fetch the customer's phone
                'customers.email'  // Fetch the customer's email
            )
            ->where('policies.fileno', $fileno)
            ->first();

        // Log the retrieved policy details
        Log::info('Policy found: ' . json_encode($policy));

        return response()->json($policy);
    }

    public function searchPolicies(Request $request)
    {
        // Convert search input to lowercase for case-insensitive search
        $search = strtolower($request->query('search'));
        Log::info('Searching policies with term: ' . $search);

        // Query the policies based on the search term
        $policies = DB::table('policies')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->select('policies.fileno', 'policies.customer_name', 'policy_types.type_name as policy_type', 'policies.reg_no')
            ->where(function($query) use ($search) {
                $query->where(DB::raw('LOWER(policies.fileno)'), 'like', '%' . $search . '%')
                      ->orWhere(DB::raw('LOWER(policies.reg_no)'), 'like', '%' . $search . '%')
                      ->orWhere(DB::raw('LOWER(policies.customer_name)'), 'like', '%' . $search . '%');
            })
            ->limit(10) // Limit the results for performance
            ->get();

        // Log the number of policies found
        Log::info('Found ' . $policies->count() . ' policies matching the search term.');

        return response()->json($policies);
    }
}
