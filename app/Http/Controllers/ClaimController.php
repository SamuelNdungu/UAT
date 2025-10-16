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
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;


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
        $claim->load('policy.policyType');
    
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



    /**
     * Store a newly created claim.
     */
    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        // Validate the form inputs (names used on the create page)
        $rules = [
            'policy_id' => 'required|exists:policies,id',
            'claim_no' => 'nullable|string|max:255',
            'date_of_loss' => 'required|date',
            'reported_at' => 'nullable|date',
            'type_of_loss' => 'nullable|string|max:255',
            'claimant_name' => 'nullable|string|max:255',
            'status' => 'required|string',
            'loss_details' => 'nullable|string',
            'amount_claimed' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ];

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Map incoming fields to DB columns (use DB names your table expects)
            $payload = [
                'policy_id'      => $validated['policy_id'],
                'claim_number'   => $validated['claim_no'] ?? $request->input('claim_number') ?? null,
                'loss_date'      => $validated['date_of_loss'],
                'reported_date'  => $validated['reported_at'] ?? null,
                'type_of_loss'   => $validated['type_of_loss'] ?? null,
                'claimant_name'  => $validated['claimant_name'] ?? $request->input('claimant_name') ?? null,
                'status'         => $validated['status'],
                'loss_details'   => $validated['loss_details'] ?? $request->input('loss_details') ?? null,
                'amount_claimed' => $validated['amount_claimed'] ?? null,
                'amount_paid'    => $validated['amount_paid'] ?? null,
                // do not add attachments/created_by here yet; we'll decide below
            ];

            Log::info('ClaimController@store: creating claim payload (pre-attachments)', $payload);

            // Save uploaded files to storage and collect metadata
            $attachmentsMeta = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('claims', 'public'); // stores in storage/app/public/claims
                        $attachmentsMeta[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime' => $file->getClientMimeType(),
                        ];
                        Log::info("ClaimController@store: uploaded file saved to storage/public/{$path}");
                    }
                }
            }

            $claimsTable = (new Claim())->getTable();

            // Only include attachments if the DB actually has an attachments column
            if (Schema::hasColumn($claimsTable, 'attachments')) {
                $payload['attachments'] = $attachmentsMeta;
            } else {
                Log::warning("Claims table does not have 'attachments' column; uploaded files will not be saved to DB. Files saved to storage and will be moved to claim folder after creation.");
            }

            // Only include created_by if the column exists
            if (Schema::hasColumn($claimsTable, 'created_by')) {
                $payload['created_by'] = Auth::id();
            }

            // Filter payload to only actual columns to avoid SQL errors
            $availableColumns = Schema::getColumnListing($claimsTable);
            $filteredPayload = array_intersect_key($payload, array_flip($availableColumns));

            Log::debug('ClaimController@store: filtered payload keys: ' . implode(',', array_keys($filteredPayload)));

            // Create the claim
            $claim = Claim::create($filteredPayload);

            // If attachments were uploaded but the DB did not have an attachments column,
            // move files into a claim-specific folder for easier manual/linking later.
            if (!Schema::hasColumn($claimsTable, 'attachments') && !empty($attachmentsMeta)) {
                foreach ($attachmentsMeta as $att) {
                    $oldPath = $att['path'] ?? null;
                    if (! $oldPath) continue;

                    $filename = basename($oldPath);
                    $newDir = 'claims/claim_' . $claim->id;
                    $newPath = $newDir . '/' . $filename;

                    try {
                        // Ensure directory exists (disk 'public')
                        // Storage::disk('public')->makeDirectory($newDir); // optional, move will create dirs
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->move($oldPath, $newPath);
                            Log::info("ClaimController@store: moved file {$oldPath} -> {$newPath} for claim {$claim->id}");
                        } else {
                            Log::warning("ClaimController@store: expected file missing when moving: {$oldPath}");
                        }
                    } catch (\Exception $e) {
                        Log::error("ClaimController@store: failed to move attachment {$oldPath} for claim {$claim->id}: " . $e->getMessage());
                    }
                }

                // Log guidance so admin/dev can later link attachments into DB or separate table
                Log::info("ClaimController@store: uploaded files for claim {$claim->id} are stored under storage/app/public/claims/claim_{$claim->id}/");
            }

            DB::commit();

            Log::info('ClaimController@store: created claim id=' . $claim->id);

            if ($isAjax) {
                return response()->json(['success' => true, 'claim_id' => $claim->id], 201);
            }

            return redirect()->route('claims.show', $claim->id)->with('success', 'Claim created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClaimController@store: error creating claim', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            if ($isAjax) {
                return response()->json(['success' => false, 'error' => 'Unable to create claim.'], 500);
            }

            return redirect()->back()->withInput()->with('error', 'An error occurred while creating the claim. Check logs for details.');
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
            // attachments handling if new files uploaded
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            // remove_attachments expected as array of indices or filenames (from edit form checkboxes)
            'remove_attachments' => 'nullable|array',
        ]);

        // Log the update action
        Log::info('Updating claim ID: ' . $claim->id, $validated);

        // Process attachment removals (if any)
        try {
            $removeList = $request->input('remove_attachments', []);
            // Normalize remove list
            if (!is_array($removeList)) {
                if (is_string($removeList)) {
                    $decoded = json_decode($removeList, true);
                    $removeList = is_array($decoded) ? $decoded : [$removeList];
                } else {
                    $removeList = (array)$removeList;
                }
            }

            // Ensure attachments is an array
            $existingAttachments = $claim->attachments ?? [];
            if (is_string($existingAttachments)) {
                $decoded = json_decode($existingAttachments, true);
                $existingAttachments = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
            } elseif (!is_array($existingAttachments)) {
                $existingAttachments = [];
            }

            if (!empty($removeList) && count($existingAttachments) > 0) {
                $remaining = [];
                foreach ($existingAttachments as $idx => $att) {
                    $shouldRemove = false;

                    // Accept removal by index (numeric) or by path/name string
                    if (in_array($idx, $removeList, true)) {
                        $shouldRemove = true;
                    } elseif (isset($att['path']) && in_array($att['path'], $removeList, true)) {
                        $shouldRemove = true;
                    } elseif (isset($att['original_name']) && in_array($att['original_name'], $removeList, true)) {
                        $shouldRemove = true;
                    } elseif (in_array((string)$idx, $removeList, true)) {
                        // sometimes form posts string indices
                        $shouldRemove = true;
                    }

                    if ($shouldRemove) {
                        // Attempt to delete physical file from storage/public
                        $path = $att['path'] ?? $att['file'] ?? null;
                        if ($path) {
                            try {
                                if (Storage::disk('public')->exists($path)) {
                                    Storage::disk('public')->delete($path);
                                    Log::info("Deleted attachment file for claim {$claim->id}: {$path}");
                                } else {
                                    Log::warning("Attachment file not found for deletion (claim {$claim->id}): {$path}");
                                }
                            } catch (\Exception $e) {
                                Log::error("Error deleting attachment for claim {$claim->id}: " . $e->getMessage());
                                // continue without throwing to allow other removals/updates
                            }
                        } else {
                            Log::warning("Attachment entry had no path for claim {$claim->id}: " . json_encode($att));
                        }
                        // do not add to $remaining (effectively removed)
                    } else {
                        $remaining[] = $att;
                    }
                }

                // Assign remaining attachments back to the claim
                $claim->attachments = $remaining;
                // Persist the change now so further update logic sees the updated attachments
                $claim->saveQuietly();
                Log::info('ClaimController@update: removed attachments for claim ' . $claim->id . '; remaining count: ' . count($remaining));
            }
        } catch (\Exception $e) {
            // Log but do not abort update flow; inform admin in logs
            Log::error('ClaimController@update: error processing attachment removals for claim ' . $claim->id . ' - ' . $e->getMessage());
        }

        // Assign the user_id from the authenticated user
        $validated['user_id'] = Auth::id();

        // Update the claim with the validated data
        $claim->update($validated);

        // Handle newly uploaded attachments (append)
        if ($request->hasFile('attachments')) {
            $existingAttachments = $claim->attachments ?? [];
            if (is_string($existingAttachments)) {
                $decoded = json_decode($existingAttachments, true);
                $existingAttachments = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
            } elseif (!is_array($existingAttachments)) {
                $existingAttachments = [];
            }

            foreach ($request->file('attachments') as $file) {
                if ($file && $file->isValid()) {
                    try {
                        $path = $file->store('claims', 'public');
                        $meta = [
                            'original_name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime' => $file->getClientMimeType(),
                        ];
                        $existingAttachments[] = $meta;
                        Log::info('ClaimController@update: uploaded attachment for claim ' . $claim->id . ' -> ' . $path);
                    } catch (\Exception $e) {
                        Log::error('ClaimController@update: failed to store uploaded attachment: ' . $e->getMessage());
                    }
                }
            }

            // Save updated attachments
            $claim->attachments = $existingAttachments;
            $claim->saveQuietly();
        }

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
                'policies.id as policy_id',                       // <-- added numeric policy id for form validation
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
