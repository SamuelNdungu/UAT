<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Schema; // Used in _getStatementData
use Illuminate\Support\Collection; // Used in _getStatementData
use Intervention\Image\ImageManager;
use App\Models\Policy;
use App\Models\Payment; // <-- IMPORTED FOR STATEMENT HELPER
use App\Models\CompanyData; // <-- IMPORTED FOR STATEMENT HELPER
use App\Models\RenewalNotice;
use Illuminate\Support\Facades\Mail;
use App\Mail\RenewalNotification;
use App\Mail\CustomerStatementMail;
use Carbon\Carbon;

class CustomerController extends Controller
{
    // Define the precision constant required by _getStatementData
    const PRECISION = 2; 

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
                $query->where('status', CustomerStatus::ACTIVE->value);
                break;
            case 'inactive':
                $query->where('status', CustomerStatus::INACTIVE->value);
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
            'activeCustomers' => Customer::where('status', CustomerStatus::ACTIVE->value)->count(),
            'inactiveCustomers' => Customer::where('status', CustomerStatus::INACTIVE->value)->count(),
        ];

        // Pass customers and metrics to the view
        return view('customers.index', compact('customers', 'metrics'));
    }

public function show($id)
{
    try {
        // Find customer
        $customer = Customer::findOrFail($id);
        
        // MANUALLY load documents to bypass broken relationship - THIS WILL WORK!
        $documents = Document::where('documentable_id', $customer->id)
            ->where('documentable_type', 'App\Models\Customer')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Manually set the relationship for the view
        $customer->setRelation('documents', $documents);

        // Debug logging - you should see 9 documents now!
        Log::info('Customer show with manual documents:', [
            'customer_id' => $customer->id,
            'customer_code' => $customer->customer_code,
            'documents_count' => $documents->count(), // Should be 9!
            'document_samples' => $documents->take(3)->pluck('description')->toArray()
        ]);

        return view('customers.show', compact('customer','documents'));
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Customer not found:', ['id' => $id]);
        abort(404, 'Customer not found');
    } catch (\Exception $e) {
        Log::error('Unexpected error in customer show:', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        return redirect()->route('customers.index')
            ->with('error', 'Error loading customer: ' . $e->getMessage());
    }
}

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        
        // MANUALLY load documents (as done in show method) to pass to the edit view
        $documents = Document::where('documentable_id', $customer->id)
            ->where('documentable_type', 'App\Models\Customer')
            ->orderBy('created_at', 'desc')
            ->get();
            
        Log::info('Editing customer:', [
            'customer_id' => $id, 
            'current_status' => $customer->status,
            'documents_count' => $documents->count()
        ]);
        
        // Pass the documents collection to the view
        return view('customers.edit', compact('customer', 'documents'));
    }

    public function store(StoreCustomerRequest $request) // Use Form Request here
{
    // Get validated data from the Form Request
    $validated = $request->validated();

    // Use the safe model method to generate customer code
    $validated['customer_code'] = Customer::generateNextCustomerCode('CUST-', 5);

    // Set default status as active (store integer 1)
    $validated['status'] = 1;

    // Assign the user_id from the authenticated user
    $validated['user_id'] = auth()->id();

    DB::beginTransaction();
    try {
        // Log the data before saving it - NOW YOU SHOULD SEE ALL FIELDS
        \Log::info('Customer data to be saved:', $validated);

        // Create the customer
        $customer = Customer::create($validated);
        
        // Handle document uploads with descriptions
        $documentsData = [];
        if ($request->has('document_description')) {
            foreach ($request->document_description as $index => $description) {
                $file = $request->file('upload_file')[$index] ?? null;
                
                if ($file && $file->isValid()) {
                    $finalDir = 'customers/customer_' . $customer->id;
                    Storage::disk('public')->makeDirectory($finalDir);
                    
                    $path = $file->storeAs($finalDir, $file->getClientOriginalName(), 'public');
                    
                    // Generate thumbnail for images
                    $thumbPath = null;
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
                        } catch (\Exception $e) {
                            Log::warning('Failed to create thumbnail for customer document: ' . $e->getMessage());
                        }
                    }
                    
                    $documentsData[] = [
                        'description' => $description,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getClientMimeType(),
                    ];
                    
                    Log::info("CustomerController@store: uploaded document with description for customer {$customer->id} -> {$path}");
                }
            }
        }

        // Create Document records for new document uploads
        if (!empty($documentsData)) {
            foreach ($documentsData as $docData) {
                Document::create([
                    'documentable_id' => $customer->id,
                    'documentable_type' => Customer::class,
                    'description' => $docData['description'],
                    'path' => $docData['path'],
                    'original_name' => $docData['original_name'],
                    'mime' => $docData['mime'],
                    'size' => $docData['size'],
                    'uploaded_by' => Auth::id(),
                ]);
            }
            Log::info("CustomerController@store: created " . count($documentsData) . " document records for customer {$customer->id}");
        }

        DB::commit();

        // Log after the customer is saved
        Log::info('Customer created successfully with customer_code: ' . $validated['customer_code']);

        // Redirect back with success message
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Log the exception if saving fails
        Log::error('Error creating customer: ', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

        // Redirect back with error message
        return redirect()->back()->with('error', 'An error occurred while creating the customer.')->withInput();
    }
}

   public function update(UpdateCustomerRequest $request, $id)
{
    DB::beginTransaction();
    try {
        Log::info('=== CUSTOMER UPDATE STARTED ===', ['customer_id' => $id]);
        $customer = Customer::findOrFail($id);
        Log::info('Customer found:', ['customer_id' => $customer->id, 'current_status' => $customer->status]);

        $validatedData = $request->validated();
        Log::info('Validated data:', $validatedData);

        // Handle status conversion: only allow 1 or 0
        if (array_key_exists('status', $validatedData)) {
            Log::info('Status field received:', ['status_value' => $validatedData['status']]);
            $raw = $validatedData['status'];

            if (is_numeric($raw)) {
                $val = (int)$raw;
                if ($val === 1 || $val === 0) {
                    $validatedData['status'] = $val;
                } else {
                    unset($validatedData['status']);
                    Log::warning('Invalid numeric status received; skipping status update', ['raw' => $raw]);
                }
            } else {
                $lower = strtolower(trim((string)$raw));
                if (in_array($lower, ['active', '1', 'true', 'yes'], true)) {
                    $validatedData['status'] = 1;
                } elseif (in_array($lower, ['inactive', '0', 'false', 'no'], true)) {
                    $validatedData['status'] = 0;
                } else {
                    unset($validatedData['status']);
                    Log::warning('Unrecognized status value received; skipping status update', ['raw' => $raw]);
                }
            }

            Log::info('Status after conversion:', ['status' => $validatedData['status'] ?? null]);
        }

        // Handle document uploads with descriptions - MULTIPLE FILES (like store method)
        $documentsData = [];
        
        // Check if document_description exists and is an array
        if ($request->has('document_description') && is_array($request->document_description)) {
            foreach ($request->document_description as $index => $description) {
                $file = $request->file('upload_file')[$index] ?? null;
                
                if ($file && $file->isValid()) {
                    $finalDir = 'customers/customer_' . $customer->id;
                    Storage::disk('public')->makeDirectory($finalDir);
                    
                    $path = $file->storeAs($finalDir, $file->getClientOriginalName(), 'public');
                    
                    // Generate thumbnail for images
                    $thumbPath = null;
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif'])) {
                        try {
                            // Check if Intervention Image is available
                            if (class_exists('Intervention\Image\ImageManager')) {
                                $thumbDir = $finalDir . '/thumbs';
                                Storage::disk('public')->makeDirectory($thumbDir);
                                $fullPath = Storage::disk('public')->path($path);
                                $thumbPath = $thumbDir . '/' . basename($path);
                                $thumbFull = Storage::disk('public')->path($thumbPath);
                                
                                $manager = new \Intervention\Image\ImageManager('gd');
                                $img = $manager->make($fullPath);
                                if (method_exists($img, 'orientate')) {
                                    $img->orientate();
                                }
                                $img->resize(300, null, function ($constraint) {
                                    $constraint->aspectRatio();
                                    $constraint->upsize();
                                });
                                $img->save($thumbFull, 80);
                            } else {
                                Log::warning('Intervention Image not available, skipping thumbnail generation');
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to create thumbnail for customer document: ' . $e->getMessage());
                            // DON'T fail the entire update if thumbnail fails
                        }
                    }
                    
                    $documentsData[] = [
                        'description' => $description,
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getClientMimeType(),
                    ];
                    
                    Log::info("CustomerController@update: uploaded document with description for customer {$customer->id} -> {$path}");
                }
            }
        }

        // Assign the user_id from the authenticated user
        $validatedData['user_id'] = Auth::id();
        Log::info('Final data to be updated:', $validatedData);

        // Update customer data
        $updateResult = $customer->update($validatedData);
        Log::info('Update result:', ['success' => $updateResult]);

        // Create Document records for new document uploads
        if (!empty($documentsData)) {
            foreach ($documentsData as $docData) {
                Document::create([
                    'documentable_id' => $customer->id,
                    'documentable_type' => Customer::class,
                    'description' => $docData['description'],
                    'path' => $docData['path'],
                    'original_name' => $docData['original_name'],
                    'mime' => $docData['mime'],
                    'size' => $docData['size'],
                    'uploaded_by' => Auth::id(),
                ]);
            }
            Log::info("CustomerController@update: created " . count($documentsData) . " document records for customer {$customer->id}");
        }

        // Refresh the customer model to get updated data
        $customer->refresh();
        Log::info('Customer after update:', [
            'customer_id' => $customer->id,
            'status' => $customer->status,
            'updated_at' => $customer->updated_at
        ]);

        DB::commit();
        Log::info('=== CUSTOMER UPDATE COMPLETED SUCCESSFULLY ===');

        // Redirect back to the customer index with a success message
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
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

    /**
     * Send a manual renewal email for the given policy id and log a RenewalNotice.
     *
     * Route used by renewals.index "Send renewal email" action.
     */
    public function sendRenewalEmail($policyId)
    {
        try {
            Log::info('Manual renewal email requested', ['policy_id' => $policyId, 'user_id' => Auth::id()]);

            $policy = Policy::with('customer')->findOrFail($policyId);

            $email = $policy->customer->email ?? $policy->customer_email ?? null;

            $noticePayload = [
                'fileno' => $policy->fileno,
                'policy_id' => $policy->id,
                'notice_type' => 'manual',
                'recipient_email' => $email,
                'sent_at' => Carbon::now(),
            ];

            if (empty($email)) {
                $noticePayload['status'] = 'skipped';
                $noticePayload['message'] = 'No customer email available';
                try {
                    RenewalNotice::create($noticePayload);
                } catch (\Throwable $e) {
                    Log::error('Failed to record skipped RenewalNotice (manual send)', ['policy_id' => $policyId, 'error' => $e->getMessage()]);
                }
                return redirect()->back()->with('error', 'No email found for this policy\'s customer. Notice recorded as skipped.');
            }

            try {
                Mail::to($email)->send(new RenewalNotification($policy));

                $noticePayload['status'] = 'sent';
                $noticePayload['message'] = 'Manual send';
                RenewalNotice::create($noticePayload);

                Log::info('Manual renewal email sent', ['policy_id' => $policyId, 'email' => $email]);
                return redirect()->back()->with('success', 'Renewal email sent successfully.');

            } catch (\Throwable $e) {
                Log::error('Failed to send manual renewal email', ['policy_id' => $policyId, 'email' => $email, 'error' => $e->getMessage()]);

                $noticePayload['status'] = 'failed';
                $noticePayload['message'] = substr($e->getMessage(), 0, 1000);
                try {
                    RenewalNotice::create($noticePayload);
                } catch (\Throwable $inner) {
                    Log::error('Failed to record failed RenewalNotice (manual send)', ['policy_id' => $policyId, 'error' => $inner->getMessage()]);
                }

                return redirect()->back()->with('error', 'Failed to send renewal email: ' . $e->getMessage());
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Manual renewal email failed - policy not found', ['policy_id' => $policyId]);
            return redirect()->back()->with('error', 'Policy not found.');
        } catch (\Throwable $e) {
            Log::error('Unexpected error in sendRenewalEmail', ['policy_id' => $policyId, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'An unexpected error occurred.');
        }
    }

    /**
     * Helper method to retrieve all data required for the customer statement.
     * This logic was extracted from the working generate() method.
     *
     * @param Customer $customer
     * @param Request $request
     * @return array
     */
    private function _getStatementData(Customer $customer, Request $request): array
    {
        // Set BCMath scale for all subsequent calculations to ensure consistency
        bcscale(self::PRECISION); 
        
        // Load company data
        $company = CompanyData::first();

        // Load policies
        $policies = Policy::with('policyType')->where(function($q) use ($customer) {
            $q->where('customer_code', $customer->customer_code)
              ->orWhere('lead_id', $customer->id)
              ->orWhere('user_id', $customer->id);
        })
        ->get(['id','fileno','policy_no','policy_type_id','start_date','end_date','gross_premium','status']);

        // Flexible lookups for payments
        $payments = collect();

        if (class_exists(\App\Models\Payment::class)) {
            $paymentModel = new Payment();
            $paymentsTable = $paymentModel->getTable();

            $paymentsQuery = Payment::query();

            // Ensure correct query grouping if orWhere is used
            $paymentsQuery->where(function ($q) use ($customer) {
                $q->where('customer_code', $customer->customer_code);
                // Schema check logic for customer_id column in payments table
                if (Schema::hasColumn($q->getModel()->getTable(), 'customer_id')) {
                    $q->orWhere('customer_id', $customer->id);
                }
            });

            $payments = $paymentsQuery->get();
        } else {
            // Fallback: try payments table (fetch all columns) with schema-safe checks
            $table = 'payments';
            $qb = DB::table($table)->where('customer_code', $customer->customer_code);

            if (Schema::hasColumn($table, 'customer_id')) {
                $qb->orWhere('customer_id', $customer->id);
            }

            $payments = collect($qb->get());
        }

        // Build transactions list
        $transactions = new Collection();
        
        // Policies and Endorsements (Debits/Credits)
        foreach ($policies as $p) {
            $date = $p->start_date ? Carbon::parse($p->start_date) : (isset($p->created_at) ? Carbon::parse($p->created_at) : Carbon::now());
            // Ensure gross_premium is treated as a string for high precision, then formatted to 2 decimal places
            $debitAmount = number_format((float)($p->gross_premium ?? 0), self::PRECISION, '.', ''); 
            
            $transactions->push((object)[
                'date' => $date,
                'type' => 'Policy',
                'description' => 'Policy - ' . ($p->policyType->type_name ?? $p->policy_type_id),
                'policy_no' => $p->policy_no,
                'debit' => $debitAmount,
                'credit' => '0.00',
            ]);

            // Add Endorsements for this policy
            if (method_exists($p, 'endorsements')) {
                // Ensure endorsements are eagerly loaded or load them now
                $p->loadMissing('endorsements'); 
                foreach ($p->endorsements as $endorsement) {
                    $endorsementDate = $endorsement->effective_date ? Carbon::parse($endorsement->effective_date) : ($endorsement->created_at ?? Carbon::now());
                    $amount = number_format((float)($endorsement->premium_impact ?? 0), self::PRECISION, '.', '');
                    $transactions->push((object)[
                        'date' => $endorsementDate,
                        'type' => 'Endorsement',
                        'description' => 'Endorsement - ' . ($endorsement->endorsement_type ?? '') . ($endorsement->description ? (': ' . $endorsement->description) : ''),
                        'policy_no' => $p->policy_no,
                        'debit' => $amount > 0 ? $amount : '0.00',
                        'credit' => $amount < 0 ? abs($amount) : '0.00',
                    ]);
                }
            }
        }

        // Payments (Credits)
        foreach ($payments as $pay) {
            $receiptIdentifier = $pay->receipt_no ?? $pay->receipt ?? $pay->receipt_number ?? $pay->id ?? null;
            $paymentAmount = number_format((float) ($pay->payment_amount ?? $pay->amount ?? $pay->paid_amount ?? 0), self::PRECISION, '.', '');

            if (isset($pay->payment_date) && $pay->payment_date) {
                $date = Carbon::parse($pay->payment_date);
            } elseif (isset($pay->date) && $pay->date) {
                $date = Carbon::parse($pay->date);
            } elseif (isset($pay->created_at) && $pay->created_at) {
                $date = Carbon::parse($pay->created_at);
            } else {
                $date = Carbon::now();
            }

            $description = $pay->description ?? $pay->notes ?? ('Receipt: ' . ($receiptIdentifier ?? ($pay->id ?? '')));

            $transactions->push((object)[
                'date' => $date,
                'type' => 'Payment',
                'description' => $description,
                'policy_no' => '',
                'debit' => '0.00',
                'credit' => $paymentAmount,
            ]);
        }

        // Sort by date ascending
        $transactions = $transactions->sortBy(function($t) {
            return $t->date->timestamp;
        })
        ->values()
        ->sortBy(function($t) {
            // Secondary sort: Policies (Debit) before Payments (Credit) for transactions on the same second
            return $t->type == 'Policy' ? 0 : 1; 
        })
        ->values();

        // Calculate running balance using BCMath (precise strings)
        $running = '0.00';
        $transactions = $transactions->map(function($t) use (&$running) {
            // Running = Running + Debit - Credit
            $running = bcsub(bcadd($running, $t->debit ?? '0.00'), $t->credit ?? '0.00');
            
            // Format the final running balance string to the required precision
            $t->running = $running; 
            
            // format dates as d-m-Y for display
            $t->date_formatted = $t->date->format('d-m-Y');
            return $t;
        });

        // Determine statement date range
        $startDate = $transactions->first() ? $transactions->first()->date->format('d-m-Y') : null;
        $endDate = $transactions->last() ? $transactions->last()->date->format('d-m-Y') : null;

        return [
            'customer' => $customer,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now()->format('d-m-Y H:i:s'),
            'company' => $company,
        ];
    }

    /**
     * Sends the customer statement as a PDF attachment via email.
     *
     * @param Customer $customer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emailStatement(Customer $customer)
    {
        try {
            // Basic guard: Check for email address
            if (empty($customer->email)) {
                return redirect()->back()->with('error', 'Customer has no email address on file.');
            }

            // Use the working statement data preparation logic
            $request = new Request(); 
            $data = $this->_getStatementData($customer, $request);

            // Render PDF binary using existing statement view
            $pdfBinary = PDF::loadView('customers.statement', $data)->output();

            // Send email with PDF attachment
            Mail::to($customer->email)->send(new CustomerStatementMail($customer, $pdfBinary));

            return redirect()->back()->with('success', 'Customer statement emailed successfully.');

        } catch (\Throwable $e) {
            Log::error('Failed to email customer statement', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to send statement: ' . $e->getMessage());
        }
    }
    
    /**
     * Generates and forces the download of the customer statement PDF by ID.
     * This method uses the shared data preparation logic.
     * * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generate($id, Request $request)
    {
        // Set BCMath scale only once inside the helper method for safety.
        bcscale(self::PRECISION); 
        
        $customer = Customer::findOrFail($id);

        // Retrieve data using the shared helper function
        $data = $this->_getStatementData($customer, $request);
        
        // Render PDF view and force download
        $filename = 'CustomerStatement_' . ($customer->customer_code ?? $customer->id) . '.pdf';

        $pdf = PDF::loadView('customers.statement', $data)->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Generate and download the customer statement PDF using Route Model Binding.
     * This method now uses the shared data preparation logic for accuracy.
     * * @param Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function downloadStatement(Customer $customer)
    {
        try {
            // Retrieve data using the shared helper function
            $request = new Request(); 
            $data = $this->_getStatementData($customer, $request);
            
            // Render view to PDF binary
            $pdf = PDF::loadView('customers.statement', $data);

            $filename = 'Statement_' . ($customer->customer_code ?? $customer->id) . '.pdf';

            return $pdf->download($filename);

        } catch (\Throwable $e) {
            Log::error('Failed to generate/download statement PDF (via downloadStatement)', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Failed to generate statement PDF: ' . $e->getMessage());
        }
    }
}