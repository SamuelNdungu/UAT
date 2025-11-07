<?php

namespace App\Http\Controllers;

use App\Services\DMVICService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DMVICController extends Controller
{
    protected $dmvicService;

    public function __construct(DMVICService $dmvicService)
    {
        $this->dmvicService = $dmvicService;
    }

    /**
     * Dashboard - displays stocks + policies table
     */
    public function dashboard()
    {
        $data = [];

        // Try to get stock data (leave intact)
        try {
            $stockData = $this->dmvicService->getStock();

            // Ensure each stock has the required properties for the view
            if (isset($stockData['stocks'])) {
                foreach ($stockData['stocks'] as &$stock) {
                    $stock['color'] = $stock['Color'] ?? ($stock['color'] ?? 'blue');
                    $stock['icon']  = $stock['icon'] ?? 'fa-box';
                }
                unset($stock);
            }

            $data = array_merge($data, $stockData);
            $data['stockSuccess'] = true;

            // Build Chart.js data from byCompany items
            $labels = [];
            $datasets = [];
            if (!empty($stockData['byCompany'])) {
                // Collect all unique classification titles as labels
                $labelSet = [];
                foreach ($stockData['byCompany'] as $companyName => $info) {
                    foreach (($info['items'] ?? []) as $item) {
                        $title = $item['ClassificationTitle'] ?? null;
                        if ($title) { $labelSet[$title] = true; }
                    }
                }
                $labels = array_values(array_keys($labelSet));

                // Build dataset per company aligned to labels
                foreach ($stockData['byCompany'] as $companyName => $info) {
                    $map = [];
                    foreach (($info['items'] ?? []) as $item) {
                        $title = $item['ClassificationTitle'] ?? null;
                        if ($title) { $map[$title] = (int)($item['Stock'] ?? 0); }
                    }
                    $row = [];
                    foreach ($labels as $label) {
                        $row[] = $map[$label] ?? 0;
                    }
                    $color = $info['color'] ?? 'rgba(75, 192, 192, 0.7)';
                    $datasets[] = [
                        'label' => $companyName,
                        'data' => $row,
                        'backgroundColor' => $color,
                    ];
                }
            }
            $data['chartData'] = json_encode([
                'labels' => $labels,
                'datasets' => $datasets,
            ]);
        } catch (\Exception $e) {
            Log::error('DMVIC Stock Error: ' . $e->getMessage());
            $data['stockSuccess'] = false;
            $data['stockError'] = 'Failed to load stock data: ' . $e->getMessage();
            $data['stocks'] = [];
            $data['totalStock'] = 0;
            $data['byCompany'] = [];
            $data['chartData'] = json_encode(['labels' => [], 'datasets' => []]);
        }

        // Try to get policies data (used for the table)
        try {
            $policies = DB::table('policies')
                ->leftJoin('customers', 'policies.customer_code', '=', 'customers.customer_code')
                ->leftJoin('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
                ->leftJoin('dmvic_certificates', function ($join) {
                    // left join only latest issued certificate (if you want latest only you can refine)
                    $join->on('dmvic_certificates.policy_id', '=', 'policies.id')
                         ->where('dmvic_certificates.status', '=', 'issued');
                })
                ->whereIn('policies.policy_type_id', [35, 36, 37])
                ->where('policies.end_date', '>=', now())
                ->select([
                    'policies.id',
                    'policies.fileno',
                    'policies.coverage',
                    'policies.reg_no',
                    DB::raw("COALESCE(customers.corporate_name, CONCAT(customers.first_name, ' ', COALESCE(customers.last_name, customers.surname))) as customer_name"),
                    'policy_types.type_name as policy_type_name',

                    // DMVIC cert fields (if none -> N/A)
                    DB::raw("COALESCE(dmvic_certificates.type, 'N/A') as coverages"),
                    DB::raw("COALESCE(dmvic_certificates.certificate_no, 'N/A') as certificate_no"),
                    DB::raw("COALESCE(TO_CHAR(dmvic_certificates.commencing_date, 'DD/MM/YYYY'), 'N/A') as start_date"),
                    DB::raw("COALESCE(TO_CHAR(dmvic_certificates.created_at, 'DD/MM/YYYY'), 'N/A') as created_at"),
                    DB::raw("COALESCE(TO_CHAR(dmvic_certificates.expiring_date, 'DD/MM/YYYY'), 'N/A') as expiry_date"),
                ])
                ->orderBy('policies.fileno', 'asc')
                ->get();

            $data['policies'] = $policies;
            $data['policiesSuccess'] = true;
        } catch (\Exception $e) {
            Log::error('DMVIC Policies Error: ' . $e->getMessage());
            $data['policies'] = collect([]);
            $data['policiesSuccess'] = false;
            $data['policiesError'] = 'Failed to load policies: ' . $e->getMessage();
        }

        return view('dmvic.dashboard', $data);
    }

    /**
     * AJAX: get stock (keeps existing behavior)
     */
    public function getStock()
    {
        try {
            $data = $this->dmvicService->getStock();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show issuance form for a given policy
     */
    public function showIssuanceForm($policyId)
    {
        // Load policy (with customer, type, and insurer)
        $policy = DB::table('policies')
            ->leftJoin('customers', 'policies.customer_code', '=', 'customers.customer_code')
            ->leftJoin('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->leftJoin('insurers', 'policies.insurer_id', '=', 'insurers.id')
            ->where('policies.id', $policyId)
            ->select([
                'policies.*',
                DB::raw("COALESCE(customers.corporate_name, CONCAT(customers.first_name, ' ', COALESCE(customers.last_name, customers.surname))) as customer_name"),
                'policy_types.type_name as policy_type_name',
                'insurers.name as insurer_name',
                // customer contact details for auto-filling phone and email
                'customers.phone as customer_phone',
                'customers.email as customer_email',
                // customer KRA PIN for insured pin prefill
                'customers.kra_pin as customer_kra_pin',
            ])
            ->first();

        if (!$policy) {
            return redirect()->back()->with('error', 'Policy not found.');
        }

        // Existing certificate (if any)
        $existingCertificate = DB::table('dmvic_certificates')
            ->where('policy_id', $policyId)
            ->orderByDesc('issued_at')
            ->first();

        // Auto-derive certificate class from policy_type_id
        // 35 => C, 36 => B, 37 => D
        $autoClass = null;
        if (isset($policy->policy_type_id)) {
            $pt = (int)$policy->policy_type_id;
            if ($pt === 35) { $autoClass = 'C'; }
            elseif ($pt === 36) { $autoClass = 'B'; }
            elseif ($pt === 37) { $autoClass = 'D'; }
        }

        // Auto-derive type of cover code from policy coverage text
        $autoCoverCode = null; // 100=COMP, 200=TPO, 300=TPTF
        $cov = strtolower((string)($policy->coverage ?? $policy->policy_type_name ?? ''));
        if ($cov !== '') {
            if (str_contains($cov, 'comprehensive')) {
                $autoCoverCode = 100;
            } elseif (str_contains($cov, 'third') && (str_contains($cov, 'fire') || str_contains($cov, 'theft'))) {
                $autoCoverCode = 300; // TPTF
            } elseif (str_contains($cov, 'tptf')) {
                $autoCoverCode = 300;
            } elseif (str_contains($cov, 'tpo') || str_contains($cov, 'third')) {
                $autoCoverCode = 200;
            }
        }
        // Fallback if still null
        if ($autoCoverCode === null) { $autoCoverCode = 200; }

        // Default dates: commencing today, expiring +364 days
        $autoCommencing = Carbon::now()->format('Y-m-d');
        $autoExpiring = Carbon::now()->addDays(364)->format('Y-m-d');

        // Build insurer member company options from mapping table
        // Schema: insurer_member_companies (id, insurer_id, member_company_id)
        $memberCompanyRows = collect();
        $memberLookupError = null;
        try {
            $memberCompanyRows = DB::table('insurer_member_companies')
                ->join('insurers', 'insurer_member_companies.insurer_id', '=', 'insurers.id')
                ->select([
                    'insurer_member_companies.insurer_id',
                    'insurer_member_companies.member_company_id',
                    'insurers.name as insurer_name',
                ])
                ->orderBy('insurers.name')
                ->get();
        } catch (\Throwable $e) {
            // If permission denied or table missing, fall back to manual entry in the view
            Log::warning('Unable to load insurer_member_companies mapping: ' . $e->getMessage());
            $memberLookupError = 'Unable to load insurer-member mapping. Please enter DMVIC MemberCompanyID manually.';
        }

        // Vehicle types for Class B (id => label) — adjust to your DMVIC mapping as needed
        $vehicleTypes = [
           
            1 => 'MOTOR COMMERCIAL OWN GOODS',
            2 => 'MOTOR COMMERCIAL GENERAL CARTAGE',
            3 => 'MOTOR INSTITUTIONAL VEHICLE',
            4 => 'MOTOR SPECIAL VEHICLES',
            5 => 'TANKERS (LIQUID CARRYING)',
            6 => 'MOTOR TRADE (ROAD RISK)',
        ];

        return view('dmvic.certissuance', [
            'policy' => $policy,
            'existingCertificate' => $existingCertificate,
            'memberCompanyRows' => $memberCompanyRows,
            'memberLookupError' => $memberLookupError,
            'vehicleTypes' => $vehicleTypes,
            'autoClass' => $autoClass,
            'autoCoverCode' => $autoCoverCode,
            'autoCommencing' => $autoCommencing,
            'autoExpiring' => $autoExpiring,
        ]);
    }

    /**
     * Issue certificate (per policy)
     * This builds the DMVIC payload depending on certificate class and calls the service.
     */
    public function issue(Request $request, $policyId)
    {
        // Minimal validation (class-specific validation happens below)
        // We'll validate dynamically depending on certificate/class provided by the form.
        $request->validate([
            'membercompanyid' => 'required|integer',
            'typeofcover' => 'required|integer',
            // typeofcertificate will be validated conditionally based on class (e.g., required for D)
            'commencing_date' => 'required|date',
            'expiring_date' => 'required|date|after_or_equal:commencing_date',
            'email' => 'required|email',
        ]);

        // Ensure policy exists (with customer contact fields for fallbacks)
        $policy = DB::table('policies')
            ->leftJoin('customers', 'policies.customer_code', '=', 'customers.customer_code')
            ->where('policies.id', $policyId)
            ->select([
                'policies.*',
                'customers.phone as customer_phone',
                'customers.email as customer_email',
                'customers.kra_pin as customer_kra_pin',
            ])
            ->first();
        if (!$policy) {
            return redirect()->back()->with('error', 'Policy not found.');
        }

        // Normalize some incoming fields and format dates to DD/MM/YYYY as DMVIC expects
        $memberCompanyId = (int) $request->input('membercompanyid');
        $typeOfCover = (int) $request->input('typeofcover');
        $typeOfCertificate = $request->input('typeofcertificate'); // may be int (1,4,8,9,10) or class id
        $commencingDate = Carbon::parse($request->input('commencing_date'))->format('d/m/Y');
        $expiringDate = Carbon::parse($request->input('expiring_date'))->format('d/m/Y');

        // Normalize phone to last 9 digits, fallback to customer's phone
        $rawPhone = (string) $request->input('phonenumber', '');
        $digitsOnly = preg_replace('/\D+/', '', $rawPhone ?? '');
        $phone9 = $digitsOnly ? substr($digitsOnly, -9) : '';
        if ($phone9 === '' && !empty($policy->customer_phone)) {
            $digitsOnly = preg_replace('/\D+/', '', (string)$policy->customer_phone);
            $phone9 = $digitsOnly ? substr($digitsOnly, -9) : '';
        }

        // Build base payload (fields present in multiple certificate types)
        $payload = [
            'Membercompanyid'   => $memberCompanyId,
            'Typeofcover'       => $typeOfCover,
            'Policyholder'      => $request->input('policyholder', $policy->insured ?? $policy->customer_name ?? ''),
            'policynumber'      => $request->input('policynumber', $policy->policy_no ?? $policy->fileno ?? ''),
            'Commencingdate'    => $commencingDate,
            'Expiringdate'      => $expiringDate,
            'Registrationnumber'=> $request->input('registrationnumber', $policy->reg_no ?? ''),
            'Chassisnumber'     => $request->input('chassisnumber', $policy->chassisno ?? $policy->chassisno ?? ''),
            'Phonenumber'       => $phone9,
            'Bodytype'          => $request->input('bodytype', $policy->body_type ?? ''),
            'Email'             => $request->input('email', $policy->customer_email ?? null),
        ];

        // Attach optional common fields if provided
        if ($request->filled('vehiclemake'))       $payload['Vehiclemake'] = $request->input('vehiclemake');
        if ($request->filled('vehiclemodel'))      $payload['Vehiclemodel'] = $request->input('vehiclemodel');
        if ($request->filled('enginenumber'))      $payload['Enginenumber'] = $request->input('enginenumber');
        if ($request->filled('suminsured'))        $payload['SumInsured'] = $request->input('suminsured');
        if ($request->filled('insuredpin')) {
            $payload['InsuredPIN'] = $request->input('insuredpin');
        } elseif (!empty($policy->customer_kra_pin)) {
            $payload['InsuredPIN'] = $policy->customer_kra_pin;
        }
        if ($request->filled('yearofmanufacture')) $payload['Yearofmanufacture'] = $request->input('yearofmanufacture');
        if ($request->filled('hudumanumber'))      $payload['HudumaNumber'] = $request->input('hudumanumber');

        // Decide certificate class and attach class-specific fields and endpoint
        // We'll accept numeric TypeOfCertificate values (1,4,8,9,10 etc) OR certificate "class" passed as letter A/B/C/D
        $certificateClass = null;
        $endpoint = null;

        // Try to interpret request input
        $tcLower = strtolower((string)$typeOfCertificate);
        if (in_array($tcLower, ['a','b','c','d'])) {
            $certificateClass = strtoupper($tcLower);
        } elseif (is_numeric($typeOfCertificate)) {
            // map numeric values to classes — adjust mapping if your certificate_types table is different
            $num = (int)$typeOfCertificate;
            if (in_array($num, [1,8])) $certificateClass = 'A';
            elseif (in_array($num, [/* list B numeric */])) $certificateClass = 'B';
            elseif (in_array($num, [/* list C numeric */])) $certificateClass = 'C';
            elseif (in_array($num, [4,9,10])) $certificateClass = 'D';
            else {
                // fallback: if certificate_types table has mapping you can pass a class param from front-end instead
                $certificateClass = $request->input('certificate_class', null);
            }
        } else {
            $certificateClass = $request->input('certificate_class', null);
        }

        // Attach class-specific fields + endpoint selection
        switch ($certificateClass) {
            case 'A':
                // TypeOfCertificate numeric may be required (1 or 8 etc)
                if ($request->filled('typeofcertificate')) {
                    $payload['TypeOfCertificate'] = $request->input('typeofcertificate');
                }
                // Licensed to carry (A)
                if ($request->filled('licensedtocarry')) $payload['Licensedtocarry'] = (int)$request->input('licensedtocarry');
                // A endpoint
                $endpoint = 'IssuanceTypeACertificate';
                break;

            case 'B':
                // VehicleType & TonnageCarryingCapacity required for B
                if ($request->filled('vehicletype')) $payload['VehicleType'] = (int)$request->input('vehicletype');
                // Some DMVIC endpoints expect the field name 'Tonnage' while others accept 'TonnageCarryingCapacity'.
                // Provide both to be compatible with UAT/prod.
                if ($request->filled('tonnagecarryingcapacity')) {
                    $tonnage = (int)$request->input('tonnagecarryingcapacity');
                    $payload['TonnageCarryingCapacity'] = $tonnage;
                    $payload['Tonnage'] = $tonnage;
                } elseif ($request->filled('tonnage')) {
                    $tonnage = (int)$request->input('tonnage');
                    $payload['Tonnage'] = $tonnage;
                    $payload['TonnageCarryingCapacity'] = $tonnage;
                }
                $endpoint = 'IssuanceTypeBCertificate';
                break;

            case 'C':
                // C typically similar to A but without TypeOfCertificate
                $endpoint = 'IssuanceTypeCCertificate';
                break;

            case 'D':
                // D requires TypeOfCertificate (4,9,10), Licensed to carry (conditional), Tonnage (for commercial)
                if ($request->filled('typeofcertificate')) {
                    $payload['TypeOfCertificate'] = $request->input('typeofcertificate');
                }
                if ($request->filled('licensedtocarry')) $payload['Licensedtocarry'] = (int)$request->input('licensedtocarry');
                if ($request->filled('tonnage')) $payload['Tonnage'] = (int)$request->input('tonnage');
                $endpoint = 'IssuanceTypeDCertificate';
                break;

            default:
                // If class couldn't be determined, abort with message
                return redirect()->back()->withInput()->with('error', 'Unable to determine certificate class. Please select a certificate type.');
        }

        // Additional validation per class (basic)
        $validatorRules = [];
        if ($certificateClass === 'A') {
            $validatorRules = [
                // chassis & phone & email already handled, add any class-specific checks here
                'chassisnumber' => 'required|string|min:4|max:50',
            ];
        } elseif ($certificateClass === 'B') {
            $validatorRules = [
                'vehicletype' => 'required|integer',
                'tonnagecarryingcapacity' => 'required|integer',
                'chassisnumber' => 'required|string|min:4|max:50',
            ];
        } elseif ($certificateClass === 'C') {
            $validatorRules = [
                'chassisnumber' => 'required|string|min:4|max:50',
            ];
        } elseif ($certificateClass === 'D') {
            $validatorRules = [
                'typeofcertificate' => 'required',
                'chassisnumber' => 'required|string|min:4|max:50',
            ];
        }

        // run validator if rules provided
        if (!empty($validatorRules)) {
            $request->validate($validatorRules);
        }

        // Call DMVIC API and store result
        DB::beginTransaction();
        try {
            // Call service to issue certificate.
            // Service should accept an endpoint (e.g. 'IssuanceTypeACertificate') and payload array
            $serviceResponse = $this->dmvicService->issueCertificate($endpoint, $payload);

            // Normalize response for storing
            $success = false;
            $transactionNo = null;
            $actualCNo = null;
            $apiRequestNo = null;

            if (is_array($serviceResponse)) {
                if (isset($serviceResponse['callbackObj']['issueCertificate'])) {
                    $cb = $serviceResponse['callbackObj']['issueCertificate'];
                    $transactionNo = $cb['TransactionNo'] ?? $serviceResponse['callbackObj']['issueCertificate']['TransactionNo'] ?? null;
                    $actualCNo = $cb['actualCNo'] ?? $cb['actualCNo'] ?? null;
                }
                $apiRequestNo = $serviceResponse['APIRequestNumber'] ?? ($serviceResponse['APIRequestNumber'] ?? null);
                $success = !empty($serviceResponse['success']) ? (bool)$serviceResponse['success'] : (bool)($actualCNo || $transactionNo);
            } else {
                // If service returned non-array (unexpected), keep raw
                Log::warning('DMVIC service returned unexpected response type', ['resp' => $serviceResponse]);
            }

            // Store the certificate row (request/response saved as JSON)
            $insert = [
                'policy_id'     => $policyId,
                'transaction_no'=> $transactionNo ?? ($apiRequestNo ?? null),
                'certificate_no'=> $actualCNo ?? null,
                'type'          => $certificateClass,
                'status'        => $success ? 'issued' : 'failed',
                'api_response'  => json_encode($serviceResponse),
                'request_data'  => json_encode($payload),
                'issued_at'     => $success ? now() : null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            // Insert and get id
            $certId = DB::table('dmvic_certificates')->insertGetId($insert);

            DB::commit();

            if ($success) {
                return redirect()->route('dmvic.dashboard')->with('success', 'Certificate issued successfully. DMVIC CNo: ' . ($actualCNo ?? 'N/A'));
            } else {
                return redirect()->back()->withInput()->with('error', 'DMVIC API did not return an issued certificate. Check logs for details.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('DMVIC Issue Error: ' . $e->getMessage(), [
                'policy_id' => $policyId,
                'payload' => $payload,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()->with('error', 'Failed to issue certificate: ' . $e->getMessage());
        }
    }

    /**
     * View certificate details (for a policy) - shows DB-stored API response + request
     */
    public function view($policyId)
    {
        $cert = DB::table('dmvic_certificates')->where('policy_id', $policyId)->orderByDesc('issued_at')->first();
        $policy = DB::table('policies')->where('id', $policyId)->first();

        if (!$policy) {
            return redirect()->back()->with('error', 'Policy not found.');
        }

        return view('dmvic.view', compact('policy', 'cert'));
    }

    /**
     * Download certificate (stub) - adapt to how you store PDFs
     */
    public function download($policyId)
    {
        // Example: certificates are stored as storage_path("app/certificates/{policyId}.pdf")
        $path = storage_path("app/certificates/{$policyId}.pdf");
        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'Certificate file not found.');
        }

        return response()->download($path);
    }

    /**
     * Cancel certificate - mark as cancelled in DB
     */
    public function cancel($policyId)
    {
        try {
            DB::table('dmvic_certificates')
                ->where('policy_id', $policyId)
                ->where('status', 'issued')
                ->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Certificate cancelled.');
        } catch (\Exception $e) {
            Log::error('DMVIC Cancel Error: ' . $e->getMessage(), ['policy_id' => $policyId]);
            return redirect()->back()->with('error', 'Failed to cancel certificate: ' . $e->getMessage());
        }
    }

    /**
     * Show double issuance check form
     */
    public function showDoubleIssuanceForm()
    {
        return view('dmvic.double-issuance');
    }

    /**
     * Check double issuance - delegates to service
     */
    public function checkDoubleIssuance(Request $request)
    {
        $request->validate([
            'vehicleregistrationnumber' => 'nullable|string|max:15',
            'chassisnumber' => 'nullable|string|min:4|max:20|regex:/^[A-Za-z0-9]+$/i',
            'policystartdate' => 'required|date_format:d/m/Y',
            'policyenddate' => 'required|date_format:d/m/Y|after_or_equal:policystartdate',
        ]);

        try {
            $response = $this->dmvicService->checkDoubleIssuance([
                'vehicleregistrationnumber' => $request->input('vehicleregistrationnumber'),
                'chassisnumber' => $request->input('chassisnumber'),
                'policystartdate' => $request->input('policystartdate'),
                'policyenddate' => $request->input('policyenddate'),
            ]);

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}