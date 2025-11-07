<?php

namespace App\Services\Traits;

use App\Models\Policy; // Assuming you have a Policy Eloquent Model
use App\Models\Customer; // NEW: Assuming you have a Customer Eloquent Model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Services\StatementService;

trait HasDataInterrogation
{
    /**
     * Defines all available tools (functions) for the language model.
     * The single tool now supports complex filtering.
     *
     * @return array The array of tool declarations.
     */
    protected function getToolDeclarations(): array
    {
        return [
            // Generic database query tool
            [
                'functionDeclarations' => [
                    [
                        'name' => 'query_database',
                        'description' => 'A general-purpose tool to query a single database table safely. Accepts table name, optional columns, filters, a free-text search, and a limit. Returns structured JSON results.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'table_name' => ['type' => 'STRING', 'description' => 'The table to query (e.g., "policies").'],
                                'columns' => ['type' => 'ARRAY', 'items' => ['type' => 'STRING'], 'description' => 'Optional list of columns to return. If omitted, returns a reasonable subset.'],
                                'filters' => ['type' => 'OBJECT', 'description' => 'Optional map of column -> value or {operator,value} objects for filtering.'],
                                'search' => ['type' => 'STRING', 'description' => 'Optional free-text search to apply across string columns.'],
                                'limit' => ['type' => 'INTEGER', 'description' => 'Maximum number of rows to return (default 25).'],
                                'order_by' => ['type' => 'STRING', 'description' => 'Optional ordering column (e.g., "created_at desc").'],
                                'offset' => ['type' => 'INTEGER', 'description' => 'Optional offset for paging.'],
                                'safe_where_in' => ['type' => 'OBJECT', 'description' => 'Optional map of column -> [values] for WHERE IN filters.'],
                            ],
                        ],
                    ]
                ]
            ],
            [
                'functionDeclarations' => [
                    [
                        'name' => 'query_policy_data',
                        'description' => 'A versatile tool to retrieve and filter insurance policies based on various criteria, including status, customer name, date ranges, and outstanding financial figures.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'status' => [
                                    'type' => 'STRING',
                                    'description' => 'The policy status (e.g., "ACTIVE", "CLAIMED", "EXPIRED", "OUTSTANDING").'
                                ],
                                'customer_name' => [
                                    'type' => 'STRING',
                                    'description' => 'A partial or full customer name to filter the policies by.'
                                ],
                                'date_filter' => [
                                    'type' => 'STRING',
                                    'description' => 'A date-related criteria (e.g., "due_next_month").'
                                ],
                                'financial_status' => [
                                    'type' => 'STRING',
                                    'description' => 'Used to filter by financial condition, must be "OUTSTANDING" for policies with a negative balance due.'
                                ],
                            ],
                            // All parameters are optional, making the function flexible
                        ]
                    ]
                ]
            ]
            ,
            // Tool to generate, attach and optionally send a statement PDF for a customer
            [
                'functionDeclarations' => [
                    [
                        'name' => 'send_statement_of_account',
                        'description' => 'Generate a branded statement PDF for a customer, attach it to the customer record, and optionally send it by email. Provide customer_code. If email_to is omitted the tool will look up the customer email from the customers table and use it.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'customer_code' => ['type' => 'STRING', 'description' => 'Customer code (e.g., CUS-00093)'],
                                'send_email' => ['type' => 'BOOLEAN', 'description' => 'Whether to send the generated statement by email. Defaults to true.'],
                                'email_to' => ['type' => 'STRING', 'description' => 'Recipient email address (if send_email is true). If omitted, the customer email will be used if available.'],
                                'text' => ['type' => 'STRING', 'description' => 'Optional free-text instruction. When provided, the tool will try to extract customer_code and email address from this text.'],
                            ],
                            'required' => ['customer_code'],
                        ],
                    ]
                ]
            ]
            ,
            // Tool to send renewal notices for customer policies
            [
                'functionDeclarations' => [
                    [
                        'name' => 'send_renewal_notice',
                        'description' => 'Send a policy renewal notice email to a customer. Provide customer_code. If email_to is omitted the tool will look up the customer email from the customers table and use it. Optionally provide policy_ids array to target specific policies.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'customer_code' => ['type' => 'STRING'],
                                'email_to' => ['type' => 'STRING'],
                                'policy_ids' => ['type' => 'ARRAY', 'items' => ['type' => 'INTEGER']],
                                'text' => ['type' => 'STRING', 'description' => 'Optional free-text instruction. When provided, the tool will try to extract customer_code and email address from this text.'],
                            ],
                            'required' => ['customer_code'],
                        ],
                    ]
                ]
            ],
            // Tool to process expired policy renewals
            [
                'functionDeclarations' => [
                    [
                        'name' => 'process_expired_policy_renewals',
                        'description' => 'Process and send renewal notices for expired policies. Can filter by how many days ago they expired.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'days_ago' => [
                                    'type' => 'INTEGER',
                                    'description' => 'Optional: Only include policies that expired in the last X days. If not provided, all expired policies will be processed.'
                                ],
                                'status' => [
                                    'type' => 'STRING',
                                    'description' => 'Optional: Filter by policy status (e.g., "expired"). Defaults to expired policies.'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Local implementation of the query_policy_data function, now fetching from the database using a join.
     *
     * @param string|null $status
     * @param string|null $customer_name
     * @param string|null $date_filter
     * @param string|null $financial_status
     * @return string JSON representation of the function result.
     */
    protected function query_policy_data(?string $status = null, ?string $customer_name = null, ?string $date_filter = null, ?string $financial_status = null): string
    {
        // --- START OF DYNAMIC DATABASE LOGIC ---
        
        // 1. Initialize the base query builder and join the customers table
        $query = Policy::query()
            // Join the customer table on the foreign key (assuming 'customer_code' on both tables)
            ->join('customers', 'policies.customer_code', '=', 'customers.customer_code');
        
        $filtersApplied = [];
        
        // --- IMPROVEMENT: Default Sorting ---
        // If no specific sorting is requested, default to showing the most recently created policies first.
        $query->orderBy('policies.created_at', 'desc');

        // 2. Apply Filters dynamically based on arguments provided by the AI
        
        // Filter 1: Status
        if ($status) {
            $query->where('policies.status', 'ILIKE', '%' . $status . '%'); // Changed to ILIKE for case-insensitivity
            $filtersApplied[] = "Status: " . $status;
        }

        // Filter 2: Customer Name (Searches across all name fields and full name concatenation)
        if ($customer_name) {
            // Lowercase and wildcard the search term only once
            $search = '%' . $customer_name . '%';

            // Use a grouped OR clause to search the name fields in the customers table
            $query->where(function ($q) use ($search) {
                // 1. Search Individual Name Parts (First, Last, Surname) and Corporate Name
                $q->where('customers.first_name', 'ILIKE', $search)
                  ->orWhere('customers.last_name', 'ILIKE', $search)
                  ->orWhere('customers.surname', 'ILIKE', $search)
                  // Search Corporate name
                  ->orWhere('customers.corporate_name', 'ILIKE', $search);
                  
                // 2. Search FULL Individual Name (concatenated string, case-insensitive)
                // COALESCE handles NULL fields gracefully; TRIM cleans up extra spaces if names are missing.
                $q->orWhereRaw(
                    "TRIM(COALESCE(customers.first_name, '') || ' ' || COALESCE(customers.last_name, '') || ' ' || COALESCE(customers.surname, '')) ILIKE ?", 
                    [$search]
                );
            });
            $filtersApplied[] = "Customer Name (Robust ILIKE Search): " . $customer_name;
        }

        // Filter 3: Financial Status (Outstanding is primary focus)
        if ($financial_status && strtoupper($financial_status) === 'OUTSTANDING') {
            $query->where('policies.premium', '<', 0);
            $filtersApplied[] = "Financial Status: OUTSTANDING (Premium < 0)";
        }

        // Filter 4: Date Filter (e.g., "due_next_month")
        if ($date_filter && strtolower($date_filter) === 'due_next_month') {
            $nextMonthStart = now()->addMonth()->startOfMonth()->toDateString();
            $nextMonthEnd = now()->addMonth()->endOfMonth()->toDateString();
            
            // Use end_date as the effective due/expiry date for policies
            $query->whereBetween('policies.end_date', [$nextMonthStart, $nextMonthEnd]);
            $filtersApplied[] = "Due Date: Next Month";
        }
        
        // 3. Execute the query and retrieve results
        // Select required columns, including all name components for reconstruction
        $resultsCollection = $query->limit(10)->get([
            'policies.fileno', 
            // Selecting all individual and corporate name parts (using correct column names)
            'customers.first_name', 
            'customers.last_name', 
            'customers.surname',
            'customers.corporate_name', 
            'policies.status', 
            'policies.premium', 
            'policies.end_date'
        ]);
        
        // 4. Format the final output
        $count = $resultsCollection->count();
        
        $results = $resultsCollection->map(function($p) {
            
            // Logic to construct the full name based on available fields
            $customerName = '';
            if (!empty($p->corporate_name)) {
                // If corporate name exists, use it
                $customerName = $p->corporate_name;
            } else {
                // Otherwise, construct Individual name from parts (filtering out null/empty strings)
                $nameParts = array_filter([$p->first_name, $p->last_name, $p->surname]);
                $customerName = implode(' ', $nameParts);
            }

            return [
                // Map the DB column names to the AI's expected output keys
                'file_number' => $p->fileno,
                'customer' => $customerName, // The reconstructed full name
                'status' => $p->status,
                'premium' => number_format($p->premium, 2),
                'due_date' => $p->end_date,
            ];
        })->toArray();
        
        // Include the sort order in the summary for clarity
        $summary = "Found $count policies matching the criteria, ordered by newest first. Filters applied: " . (empty($filtersApplied) ? 'None' : implode(', ', $filtersApplied));
        
        if ($count === 0 && !empty($filtersApplied)) {
             $summary = "No policies matched the applied filters: " . implode(', ', $filtersApplied);
        }

        return json_encode(['summary' => $summary, 'policies' => $results]);
    }
    

    /**
     * Query the database with the given parameters
     *
     * @param string $table The table to query
     * @param array $params Query parameters including columns, filters, etc.
     * @return string JSON-encoded query results
     */
    /**
     * Query the database with the given parameters
     *
     * @param string $table The table to query
     * @param array $params Query parameters including columns, filters, etc.
     * @return string JSON-encoded query results
     */
    protected function queryDatabase(string $table, array $params = []): string
    {
        // Discover columns for the table
        $allColumns = Schema::getColumnListing($table);
        if (empty($allColumns)) {
            return json_encode(['error' => 'Table not found or no columns available']);
        }

        // Columns requested (intersection with actual columns)
        $reqCols = $params['columns'] ?? null;
        if (is_array($reqCols) && count($reqCols) > 0) {
            $cols = array_values(array_intersect($reqCols, $allColumns));
            if (count($cols) === 0) {
                $cols = $allColumns;
            }
        } else {
            // default subset: all columns (caller can filter later)
            $cols = $allColumns;
        }

        $limit = isset($params['limit']) ? max(1, min(500, (int) $params['limit'])) : 25;
        $query = DB::table($table)->select($cols)->limit($limit);

        // offset
        if (isset($params['offset'])) {
            $off = (int) $params['offset'];
            if ($off > 0) {
                $query->offset($off);
            }
        }

        // order by
        if (!empty($params['order_by']) && is_string($params['order_by'])) {
            // simple parser: allow 'col' or 'col desc'
            $parts = preg_split('/\s+/', trim($params['order_by']));
            $col = $parts[0] ?? null;
            $dir = isset($parts[1]) && strtolower($parts[1]) === 'desc' ? 'desc' : 'asc';
            if ($col && in_array($col, $allColumns)) {
                $query->orderBy($col, $dir);
            }
        }

        // Apply filters: simple equality or operator form
        $filters = $params['filters'] ?? [];
        if (is_array($filters)) {
            foreach ($filters as $col => $val) {
                if (!in_array($col, $allColumns)) {
                    continue;
                }

                if (is_array($val) && isset($val['operator'])) {
                    $op = strtoupper($val['operator']);
                    $v = $val['value'] ?? null;
                    // Basic allowed ops
                    $allowed = ['=', '>', '<', '>=', '<=', '<>', '!=', 'LIKE', 'ILIKE', 'BETWEEN', 'IS NULL', 'IS NOT NULL'];
                    if (in_array($op, $allowed)) {
                        if ($op === 'BETWEEN' && is_array($v) && count($v) === 2) {
                            $query->whereBetween($col, [$v[0], $v[1]]);
                        } elseif ($op === 'IS NULL') {
                            $query->whereNull($col);
                        } elseif ($op === 'IS NOT NULL') {
                            $query->whereNotNull($col);
                        } else {
                            $query->where($col, $op, $v);
                        }
                    }
                } else {
                    // equality or whereIn
                    if (is_array($val)) {
                        $query->whereIn($col, $val);
                    } else {
                        $query->where($col, $val);
                    }
                }
            }
        }

        // safe where in map
        if (!empty($params['safe_where_in']) && is_array($params['safe_where_in'])) {
            foreach ($params['safe_where_in'] as $col => $vals) {
                if (in_array($col, $allColumns) && is_array($vals) && count($vals) > 0) {
                    $query->whereIn($col, $vals);
                }
            }
        }

        // Free-text search across text-like columns
        $search = $params['search'] ?? null;
        if ($search) {
            $search = '%' . $search . '%';
            $query->where(function ($q) use ($allColumns, $search) {
                foreach ($allColumns as $col) {
                    // naive heuristic: search only character varying / text columns by column name patterns
                    if (preg_match('/name|first|last|address|phone|email|note|description|fileno|policy_no/i', $col)) {
                        $q->orWhere($col, 'ILIKE', $search);
                    }
                }
            });
        }

        try {
            $rows = $query->get()->map(function ($r) {
                return (array) $r;
            })->toArray();

            $count = count($rows);
            $summary = "Found $count rows in table $table.";

            return json_encode(['summary' => $summary, 'rows' => $rows]);
        } catch (\Throwable $ex) {
            Log::error('query_database exception', ['table' => $table, 'error' => $ex->getMessage()]);
            return json_encode(['error' => 'Exception: ' . $ex->getMessage()]);
        }
    }

    /**
     * Generate, attach and optionally send a statement of account for a customer.
     * Returns JSON with keys: status, message, path (when generated).
     *
     * @param string|null $customer_code
     * @param bool $send_email
     * @param string|null $email_to
     * @return string JSON
     */
    protected function send_statement_of_account(?string $customer_code = null, bool $send_email = true, ?string $email_to = null): string
    {
        // Allow tolerant inputs: if $customer_code is not provided but caller passed an array-like payload
        // (model function-calling will pass an associative array when using JSON parameters), attempt to
        // extract 'text' or other keys from the first parameter.
        if (is_array($customer_code)) {
            $params = $customer_code;
            $customer_code = $params['customer_code'] ?? null;
            $send_email = isset($params['send_email']) ? (bool) $params['send_email'] : $send_email;
            $email_to = $params['email_to'] ?? $params['email'] ?? $email_to ?? null;
            $text = $params['text'] ?? null;
        } else {
            $text = null;
        }

        // If still missing, try to parse a free-text instruction for customer code and email
        if (empty($customer_code) && !empty($text)) {
            // customer codes like CUS-00129 or CUS00129
            if (preg_match('/(CUS[- ]?\d{2,6})/i', $text, $m)) {
                $customer_code = strtoupper(str_replace(' ', '-', $m[1]));
            }
        }

        if ($send_email && empty($email_to) && !empty($text)) {
            if (preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $text, $me)) {
                $email_to = $me[0];
            }
        }

        if (empty($customer_code)) {
            return json_encode(['status' => 'error', 'message' => 'customer_code is required']);
        }

        try {
            $svc = new StatementService();

            // If send_email is requested but we prefer HTML email only (no PDF), send statement as HTML
            // If send_email requested but email_to missing, try to fetch customer email from DB
            if ($send_email && empty($email_to)) {
                try {
                    $cust = Customer::where('customer_code', $customer_code)->first();
                    if ($cust && !empty($cust->email)) {
                        $email_to = $cust->email;
                        Log::info('send_statement_of_account: looked up customer email', ['customer_code' => $customer_code, 'email' => $email_to]);
                    }
                } catch (\Throwable $ex) {
                    Log::warning('send_statement_of_account: failed to lookup customer email', ['customer_code' => $customer_code, 'error' => $ex->getMessage()]);
                }
            }

            if ($send_email && $email_to) {
                $ok = $svc->sendStatementHtml($customer_code, $email_to);
                if (!$ok) {
                    return json_encode(['status' => 'error', 'message' => 'Failed to send HTML statement to ' . $email_to]);
                }
                return json_encode(['status' => 'ok', 'message' => 'Statement sent as HTML to ' . $email_to]);
            }

            // fallback: generate PDF and attach
            $pdfPath = $svc->generatePdfForCustomer($customer_code);
            if (!$pdfPath) {
                return json_encode(['status' => 'error', 'message' => 'Failed to generate PDF for ' . $customer_code]);
            }

            $ok = $svc->attachPdfToCustomer($customer_code, $pdfPath, $send_email, $email_to);
            if (!$ok) {
                return json_encode(['status' => 'error', 'message' => 'Failed to attach or send PDF for ' . $customer_code, 'path' => $pdfPath]);
            }

            return json_encode(['status' => 'ok', 'message' => 'Statement generated, attached' . ($send_email ? ' and sent' : '' ) . ' for ' . $customer_code, 'path' => $pdfPath]);
        } catch (\Throwable $ex) {
            Log::error('send_statement_of_account exception', ['customer_code' => $customer_code, 'error' => $ex->getMessage()]);
            return json_encode(['status' => 'error', 'message' => 'Exception: ' . $ex->getMessage()]);
        }
    }

    /**
     * Send a renewal notice for a customer.
     * 
     * @param mixed $args Customer code (string) or array of parameters
     * @param string|null $email_to Email address to send to
     * @param mixed $policy_ids Optional policy IDs (if $args is customer_code)
     * @return string JSON response with status and message
     */
    protected function send_renewal_notice($args = null, ?string $email_to = null, $policy_ids = null): string
{
    // Handle both array and parameterized calls
    if (is_array($args)) {
        $customer_code = $args['customer_code'] ?? null;
        $email_to = $args['email_to'] ?? null;
        $policy_ids = $args['policy_ids'] ?? null;
        $text = $args['text'] ?? null;
    } else {
        $customer_code = $args;
        // $email_to and $policy_ids are already set from parameters
    }

    // If email missing but we have customer_code, try to look up in DB
    if (!empty($customer_code) && empty($email_to)) {
        try {
            $cust = Customer::where('customer_code', $customer_code)->first();
            if ($cust && !empty($cust->email)) {
                $email_to = $cust->email;
                Log::info('send_renewal_notice: looked up customer email', [
                    'customer_code' => $customer_code, 
                    'email' => $email_to
                ]);
            }
        } catch (\Exception $ex) {
            Log::warning('send_renewal_notice: failed to lookup customer email', [
                'customer_code' => $customer_code, 
                'error' => $ex->getMessage()
            ]);
        }
    }

    if (empty($customer_code) || empty($email_to)) {
        return json_encode([
            'status' => 'error', 
            'message' => 'customer_code and email_to are required'
        ]);
    }

    try {
        $svc = new StatementService();
        $policyIds = is_array($policy_ids) ? $policy_ids : null;
        $ok = $svc->sendRenewalNotice($customer_code, $email_to, $policyIds);
        
        if ($ok) {
            return json_encode([
                'status' => 'ok', 
                'message' => 'Renewal notice sent to ' . $email_to
            ]);
        }
        
        return json_encode([
            'status' => 'error', 
            'message' => 'Failed to send renewal notice'
        ]);
        
    } catch (\Throwable $ex) {
        Log::error('send_renewal_notice exception', [
            'customer_code' => $customer_code,
            'error' => $ex->getMessage(),
            'trace' => $ex->getTraceAsString()
        ]);
        
        return json_encode([
            'status' => 'error', 
            'message' => 'Exception: ' . $ex->getMessage()
        ]);
    }
}

/**
 * Process and send renewal notices for expired policies
 *
 * @param array $args Arguments including optional 'days_ago' and 'status' filters
 * @return string JSON response with results
 */
protected function process_expired_policy_renewals(array $args = []): string
{
    try {
        // Extract parameters
        $daysAgo = $args['days_ago'] ?? null;
        $status = $args['status'] ?? 'expired';

        // Log the start of the process
        Log::info('Processing expired policy renewals', [
            'days_ago' => $daysAgo,
            'status' => $status,
            'args' => $args
        ]);

        // Build the query for expired policies
        $query = \App\Models\Policy::where('status', $status)
            ->where('expiry_date', '<=', now())
            ->with('customer');

        // If days_ago is provided, filter by policies that expired within the last X days
        if ($daysAgo !== null) {
            $query->where('expiry_date', '>=', now()->subDays($daysAgo));
        }

        // Get the policies
        $policies = $query->get();
        $totalPolicies = $policies->count();
        $successCount = 0;
        $failedCount = 0;
        $results = [];

        Log::info("Found $totalPolicies policies to process");

        // Process each policy
        foreach ($policies as $policy) {
            try {
                $customer = $policy->customer;
                if (!$customer) {
                    Log::warning("No customer found for policy ID: " . $policy->id);
                    $failedCount++;
                    $results[] = [
                        'policy_id' => $policy->id,
                        'policy_number' => $policy->policy_number,
                        'status' => 'failed',
                        'error' => 'No customer associated with this policy'
                    ];
                    continue;
                }

                // Use the existing send_renewal_notice function
                $result = $this->send_renewal_notice([
                    'customer_code' => $customer->customer_code,
                    'policy_ids' => [$policy->id],
                    'email_to' => $customer->email,
                    'text' => "Renewal notice for expired policy #{$policy->policy_number}"
                ]);

                $result = json_decode($result, true);
                if (isset($result['status']) && $result['status'] === 'ok') {
                    $successCount++;
                    $results[] = [
                        'policy_id' => $policy->id,
                        'policy_number' => $policy->policy_number,
                        'status' => 'success',
                        'message' => $result['message'] ?? 'Renewal notice sent successfully'
                    ];
                } else {
                    throw new \Exception($result['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $failedCount++;
                $results[] = [
                    'policy_id' => $policy->id ?? null,
                    'policy_number' => $policy->policy_number ?? null,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                Log::error("Failed to process policy renewal: " . $e->getMessage(), [
                    'policy_id' => $policy->id ?? null,
                    'exception' => $e
                ]);
            }
        }

        // Prepare the response
        $response = [
            'success' => true,
            'message' => "Processed $totalPolicies policies. Success: $successCount, Failed: $failedCount",
            'total_policies' => $totalPolicies,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results
        ];

        return json_encode($response, JSON_PRETTY_PRINT);

    } catch (\Exception $e) {
        Log::error("Error in process_expired_policy_renewals: " . $e->getMessage(), [
            'exception' => $e,
            'args' => $args
        ]);

        return json_encode([
            'success' => false,
            'error' => 'Failed to process expired policy renewals: ' . $e->getMessage()
        ], JSON_PRETTY_PRINT);
    }
}
}
