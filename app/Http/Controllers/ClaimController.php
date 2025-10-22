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
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
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
                'policies.status as policy_status',
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
    // Filter out canceled policies using model helper
    $policies = $policies->filter(function($p) { return ! $p->isCancelled(); });

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
        $policy = Policy::findOrFail($request->input('policy_id'));
        if ($policy->isCancelled()) {
            return redirect()->back()->with('error', 'Cannot register claims for a canceled policy.');
        }

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
            if (!empty($attachmentsMeta)) {
                $finalDir = 'claims/claim_' . $claim->id;
                foreach ($attachmentsMeta as $i => $att) {
                    $oldPath = $att['path'] ?? null;
                    if (! $oldPath) continue;

                    $filename = basename($oldPath);
                    $newPath = $finalDir . '/' . $filename;

                    try {
                            if (Storage::disk('public')->exists($oldPath)) {
                            // Ensure directory exists
                            Storage::disk('public')->makeDirectory($finalDir);
                            Storage::disk('public')->move($oldPath, $newPath);
                            // update attachments meta to final path
                            $attachmentsMeta[$i]['path'] = $newPath;
                            // If this is an image, generate a thumbnail
                            $ext = strtolower(pathinfo($newPath, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                                try {
                                    $thumbDir = $finalDir . '/thumbs';
                                    Storage::disk('public')->makeDirectory($thumbDir);
                                    $fullPath = Storage::disk('public')->path($newPath);
                                    $thumbPath = $thumbDir . '/' . basename($newPath);
                                    $thumbFull = Storage::disk('public')->path($thumbPath);
                                    $manager = new ImageManager('gd');
                                    $img = $manager->make($fullPath);
                                    if (method_exists($img, 'orientate')) {
                                        $img->orientate();
                                    }
                                    $img->resize(300, null, function ($constraint) {
                                        $constraint->aspectRatio();
                                        $constraint->upsize();
                                    });
                                    $img->save($thumbFull, 80);
                                    $attachmentsMeta[$i]['thumb_path'] = $thumbPath;
                                } catch (\Exception $e) {
                                    Log::warning('Failed to create thumbnail for ' . $newPath . ': ' . $e->getMessage());
                                }
                            }
                            Log::info("ClaimController@store: moved file {$oldPath} -> {$newPath} for claim {$claim->id}");
                        } else {
                            Log::warning("ClaimController@store: expected file missing when moving: {$oldPath}");
                        }
                    } catch (\Exception $e) {
                        Log::error("ClaimController@store: failed to move attachment {$oldPath} for claim {$claim->id}: " . $e->getMessage());
                    }
                }

                // Persist attachments metadata if column exists
                if (Schema::hasColumn($claimsTable, 'attachments')) {
                    $claim->attachments = $attachmentsMeta;
                    $claim->saveQuietly();
                }

                // Persist Document rows for new uploads
                try {
                    foreach ($attachmentsMeta as $att) {
                        Document::create([
                            'claim_id' => $claim->id,
                            'path' => $att['path'] ?? null,
                            'original_name' => $att['original_name'] ?? basename($att['path'] ?? ''),
                            'mime' => $att['mime'] ?? null,
                            'size' => $att['size'] ?? null,
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('ClaimController@store: failed to persist Document rows: ' . $e->getMessage());
                }

                Log::info("ClaimController@store: uploaded files for claim {$claim->id} are stored under storage/app/public/{$finalDir}/");
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
        $claimsTable = (new Claim())->getTable();
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

                // Assign remaining attachments back to the claim only if the DB column exists
                if (Schema::hasColumn($claimsTable, 'attachments')) {
                    $claim->attachments = $remaining;
                    // Persist the change now so further update logic sees the updated attachments
                    $claim->saveQuietly();
                    Log::info('ClaimController@update: removed attachments for claim ' . $claim->id . '; remaining count: ' . count($remaining));
                } else {
                    // No attachments column: just log and ensure files were deleted from storage
                    Log::info('ClaimController@update: attachments column missing on claims table; files deleted from storage for claim ' . $claim->id . '; remaining count: ' . count($remaining));
                }
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
                        // store in a claim-specific folder
                        $finalDir = 'claims/claim_' . $claim->id;
                        Storage::disk('public')->makeDirectory($finalDir);
                        $path = $file->storeAs($finalDir, $file->getClientOriginalName(), 'public');
                        // generate thumbnail for images
                        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                            try {
                                $thumbDir = $finalDir . '/thumbs';
                                Storage::disk('public')->makeDirectory($thumbDir);
                                $fullPath = Storage::disk('public')->path($path);
                                $thumbPath = $thumbDir . '/' . basename($path);
                                $thumbFull = Storage::disk('public')->path($thumbPath);
                                $manager = new ImageManager('gd');
                                $img = $manager->make($fullPath);
                                if (method_exists($img, 'orientate')) {
                                    $img->orientate();
                                }
                                $img->resize(300, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                                $img->save($thumbFull, 80);
                                $meta['thumb_path'] = $thumbPath;
                            } catch (\Exception $e) {
                                Log::warning('ClaimController@update: failed to create thumbnail: ' . $e->getMessage());
                            }
                        }
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

            // Save updated attachments only if the DB column exists
            if (Schema::hasColumn($claimsTable, 'attachments')) {
                $claim->attachments = $existingAttachments;
                $claim->saveQuietly();
            } else {
                Log::warning('ClaimController@update: attachments column missing on claims table; stored files for claim ' . $claim->id . ' were not saved to DB.');
            }
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
                'policies.status',
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

    /**
     * Securely stream or download an attachment for a claim.
     * idx may be a numeric index into the attachments array or the string 'upload_file' for the legacy single-file column.
     */
    public function attachment(Claim $claim, $idx)
    {
        $serveThumb = request()->query('thumb') == '1';
        // Authorize: simple auth middleware already applied by routes group; additional checks can be added here
        // Resolve attachment path
        $path = null;
        $name = null;

        // If attachments (array) exist, try numeric index
        $attachments = $claim->attachments ?? null;
        if (is_string($attachments)) {
            $decoded = @json_decode($attachments, true);
            $attachments = is_array($decoded) ? $decoded : null;
        }

        if (is_array($attachments) && is_numeric($idx)) {
            $i = (int)$idx;
            if (isset($attachments[$i]['path'])) {
                if ($serveThumb && !empty($attachments[$i]['thumb_path'])) {
                    $path = $attachments[$i]['thumb_path'];
                } else {
                    $path = $attachments[$i]['path'];
                }
                $name = $attachments[$i]['original_name'] ?? basename($path);
            }
        }

        // Support legacy single upload_file column
        if (!$path && $idx === 'upload_file' && $claim->upload_file) {
            $path = $claim->upload_file;
            $name = basename($path);
        }

        // If idx is a filename (string) try to find first matching path
        if (!$path && is_string($idx) && !is_numeric($idx) && is_array($attachments)) {
            foreach ($attachments as $att) {
                if ((isset($att['original_name']) && $att['original_name'] === $idx) || (isset($att['path']) && basename($att['path']) === $idx)) {
                    $path = $att['path'] ?? null;
                    $name = $att['original_name'] ?? basename($path);
                    break;
                }
            }
        }

        if (!$path) {
            abort(404, 'Attachment not found');
        }

        // Ensure file exists on the public disk
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found on disk');
        }

        $fullPath = Storage::disk('public')->path($path);
        $mime = Storage::disk('public')->mimeType($path) ?? 'application/octet-stream';

        // Stream file to browser with proper headers
        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . ($name ?? basename($fullPath)) . '"'
        ]);
    }
}
