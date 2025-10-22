<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\Renewal;
use App\Models\RenewalNotice;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\PolicyTypes;
use App\Models\Insurer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RenewalsExport;
use Illuminate\Support\Facades\Mail;
use App\Mail\RenewalNotification;
use PDF;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RenewalsController extends Controller
{
    /**
     * Display a listing of policies based on renewal status.
     *
     * @param Request $request
     * @return \Illuminate\View\View
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
                $policiesQuery->where('policies.end_date', '<', $now)
                    ->where('policies.status', '!=', 'renewed')
                    ->whereNotIn('policies.fileno', function($query) use ($now) {
                        $query->select('fileno')
                            ->from('policies')
                            ->where('start_date', '>', $now);
                    });
                break;
            default:
                // No filter is applied, show all policies
                break;
        }

        // Fetch the filtered policies
        $policies = $policiesQuery->get();

        // Load latest notice per fileno to display status quickly
        $filenos = $policies->pluck('fileno')->unique()->values()->all();
        $notices = RenewalNotice::whereIn('fileno', $filenos)
            ->orderBy('sent_at', 'desc')
            ->get()
            ->groupBy('fileno')
            ->map(function($group) { return $group->first(); })
            ->toArray();

        // Calculate metrics for the cards
        $metrics = [
            'totalPolicies' => Policy::count(),
            '10Days' => Policy::whereBetween('end_date', [$now, $next10Days])->count(),
            '30Days' => Policy::whereBetween('end_date', [$now, $next30Days])->count(),
            '60Days' => Policy::whereBetween('end_date', [$now, $next60Days])->count(),
            'expiredPolicies' => Policy::where('end_date', '<', $now)
                ->where('status', '!=', 'renewed')
                ->whereNotIn('fileno', function($query) use ($now) {
                    $query->select('fileno')
                        ->from('policies')
                        ->where('start_date', '>', $now);
                })
                ->count()
        ];

        // Send renewal notifications for policies expiring in 30 days
        $this->sendRenewalNotifications();

        return view('renewals.index', compact('policies', 'metrics', 'notices'));
    }

    /**
     * Show the renewal form prefilled from an existing policy.
     * Acts as createRenewal($policyId).
     */
     public function renew($id)
    {
        // Find the original policy
        $policy = Policy::with(['policyType', 'insurer'])->findOrFail($id);

        // --- BACK-END RENEWAL RESTRICTION ---
        // 1. If the policy is already marked as 'Renewed', disallow renewal.
        if (strtolower($policy->status) === 'renewed') {
            return redirect()->route('policies.show', $id)
                ->with('error', 'Renewal is not allowed. Policy #' . $id . ' has already been renewed.');
        }

        // 2. Add an optional date check (e.g., must be within 90 days of expiry)
        // This assumes $policy->end_date is a Carbon instance or date string.
        $expiryDate = Carbon::parse($policy->end_date);
        $renewalWindow = Carbon::now()->addDays(90); // Allows renewal up to 90 days before expiry

        if ($expiryDate->isFuture() && $expiryDate->greaterThan($renewalWindow)) {
            return redirect()->route('policies.show', $id)
                ->with('error', 'Renewal is not yet due. Policy #' . $id . ' expires on ' . $expiryDate->format('d-m-Y') . '.');
        }
        // ------------------------------------

        // decode documents/risk_details for the view
        
        $documents = $policy->documents;
        
        // Normalize and convert to Collection
        if (is_string($documents)) {
            // Attempt to decode JSON string from the database field
            $decoded = json_decode($documents, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $documents = collect($decoded);
            } else {
                // Fallback for comma-separated or simple string path
                $documents = collect(array_filter(array_map('trim', explode(',', $documents))));
            }
        } elseif (is_array($documents)) {
            // Already an array, convert to Collection
            $documents = collect($documents);
        } else {
            // Null or other type, use an empty Collection
            $documents = collect([]);
        }
        $riskDetails = $policy->risk_details ?? [];

        // Prepare defaults for new policy dates:
        $startDate = Carbon::parse($policy->end_date)->addDay()->format('Y-m-d');
        $endDate = Carbon::parse($startDate)->addMonths(12)->format('Y-m-d'); // auto 12 months

        // Fetch available lookup data (same as create)
        $availablePolicyTypes = PolicyTypes::pluck('type_name', 'id');
        $insurers = Insurer::pluck('name', 'id');
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get();

        // Return the renew view (mirrors create policy form, prefilled)
        return view('policies.renew', compact(
            'policy',
            'documents',
            'riskDetails',
            'availablePolicyTypes',
            'insurers',
            'availableVehicleTypes',
            'vehicleModels',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Process the renewal of the specified policy by creating a new policy record.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $id)
    {
        $originalPolicy = Policy::findOrFail($id);

        // Basic validation (extend as needed)
        $validated = $request->validate([
            'policy_no' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            // include other fields you allow editing, e.g. premium, coverage etc.
        ]);

        // prepare a variable to hold created policy
        $newPolicy = null;

        DB::transaction(function () use ($request, $originalPolicy, $validated, &$newPolicy) {
            // Determine new policy dates (allow user override)
            $startDate = $validated['start_date'] ?? Carbon::parse($originalPolicy->end_date)->addDay()->toDateString();
            $endDate = $validated['end_date'] ?? Carbon::parse($startDate)->addMonths(12)->toDateString();

            // Replicate the original policy (copies attributes except PK)
            $newPolicy = $originalPolicy->replicate();
            // Retain same fileno
            $newPolicy->fileno = $originalPolicy->fileno;

            // Overwrite fields that must be provided / updated for renewal
            $newPolicy->policy_no = $validated['policy_no'];
            $newPolicy->start_date = $startDate;
            $newPolicy->end_date = $endDate;
            $newPolicy->status = 'Active';
            $newPolicy->created_at = now();
            $newPolicy->updated_at = now();

            // Allow overriding some financials if provided in the request
            if ($request->filled('gross_premium')) {
                $newPolicy->gross_premium = $request->input('gross_premium');
            }
            if ($request->filled('sum_insured')) {
                $newPolicy->sum_insured = $request->input('sum_insured');
            }
            if ($request->filled('coverage')) {
                $newPolicy->coverage = $request->input('coverage');
            }
            // ... add other overwritable fields as needed ...

            // Clone and COPY documents: the form will indicate which existing documents to keep.
            $originalDocs = $originalPolicy->documents ?? [];
            $newDocuments = [];

            // Normalize originalDocs into a numerically indexed array
            if (is_string($originalDocs)) {
                $decoded = json_decode($originalDocs, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $originalDocs = array_values($decoded);
                } else {
                    // fallback: comma-separated or single path
                    $trim = trim($originalDocs);
                    $originalDocs = $trim === '' ? [] : array_values(array_map('trim', explode(',', $trim)));
                }
            } elseif (is_object($originalDocs)) {
                // convert object to array values
                $originalDocs = array_values((array) $originalDocs);
            } elseif (is_array($originalDocs)) {
                $originalDocs = array_values($originalDocs);
            } else {
                $originalDocs = [];
            }

            // Determine which original docs to keep/remove (normalize inputs)
            $keeps = $request->input('keep_documents', []);
            $removes = $request->input('remove_documents', []);

            // Helper to normalize keep/remove inputs (support JSON strings, comma-separated, single value)
            $normalizeList = function ($val) {
                if (is_array($val)) return $val;
                if (is_null($val) || $val === '') return [];
                if (is_string($val)) {
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                    return array_map('trim', explode(',', $val));
                }
                return (array) $val;
            };

            $keeps = $normalizeList($keeps);
            $removes = $normalizeList($removes);

            // Iterate original docs and copy selected ones
            foreach ($originalDocs as $idx => $doc) {
                // Determine original file path and original name
                if (is_array($doc) && isset($doc['file'])) {
                    $origPath = $doc['file'];
                    $origName = $doc['original_name'] ?? basename($origPath);
                } elseif (is_string($doc)) {
                    $origPath = $doc;
                    $origName = basename($doc);
                } else {
                    continue;
                }

                // Decide if this doc should be kept
                $keepThis = empty($keeps) ? true : (in_array($idx, $keeps, true) || in_array($origPath, $keeps, true) || in_array($origName, $keeps, true));
                $removeThis = in_array($idx, $removes, true) || in_array($origPath, $removes, true) || in_array($origName, $removes, true);

                if ($removeThis) {
                    continue;
                }

                if (! $keepThis) {
                    continue;
                }

                // Copy the physical file on the 'public' disk if it exists
                try {
                    if ($origPath && Storage::disk('public')->exists($origPath)) {
                        $newFilename = 'policy_documents/' . uniqid('doc_') . '_' . basename($origPath);
                        Storage::disk('public')->copy($origPath, $newFilename);
                        $newDocuments[] = [
                            'original_name' => $origName,
                            'file' => $newFilename,
                        ];
                    } else {
                        // If file doesn't exist on disk, still keep reference (safer than losing data)
                        $newDocuments[] = [
                            'original_name' => $origName,
                            'file' => $origPath,
                        ];
                    }
                } catch (\Exception $e) {
                    // On any copy error, fallback to referencing the original path
                    $newDocuments[] = [
                        'original_name' => $origName,
                        'file' => $origPath,
                    ];
                }
            }

            // Handle new uploaded files (if any), append to newDocuments
            if ($request->hasFile('upload_file')) {
                foreach ($request->file('upload_file') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('policy_documents', 'public');
                        $newDocuments[] = [
                            'original_name' => $file->getClientOriginalName(),
                            'file' => $path,
                        ];
                    }
                }
            }

            // Assign documents (array/json) to new policy
            $newPolicy->documents = $newDocuments;

            // Clone risk_details (deep copy)
            $newPolicy->risk_details = $originalPolicy->risk_details;

            // Reset financial tracking on new policy (payments remain on original)
            $newPolicy->paid_amount = 0;
            $newPolicy->outstanding_amount = $newPolicy->gross_premium ?? 0;
            $newPolicy->balance = $newPolicy->outstanding_amount ?? 0;

            // Save the new policy (now persists new id)
            $newPolicy->save();

            // Create a renewal record linking original -> new
            Renewal::create([
                'fileno' => $originalPolicy->fileno,
                'original_policy_id' => $originalPolicy->id,
                'renewed_policy_id' => $newPolicy->id,
                'renewal_date' => now(),
                'renewal_type' => $request->input('renewal_type', 'standard'),
                'created_by' => auth()->id(),
            ]);

            // Mark original policy as renewed and increment its renewal_count
            $originalPolicy->status = 'Renewed';
            $originalPolicy->save();

            // Increment renewal_count only if model provides helper OR DB column exists.
            if (method_exists($originalPolicy, 'incrementRenewalCount')) {
                $originalPolicy->incrementRenewalCount();
            } else {
                // Only attempt direct update if the DB column exists to avoid SQL errors.
                $policiesTable = $originalPolicy->getTable();
                if (Schema::hasColumn($policiesTable, 'renewal_count')) {
                    $originalPolicy->renewal_count = ($originalPolicy->renewal_count ?? 0) + 1;
                    // use saveQuietly to avoid firing events
                    $originalPolicy->saveQuietly();
                }
                // if column doesn't exist, skip silently
            }

            // end transaction
        });

        // After transaction complete, redirect to the newly created policy if available
        if ($newPolicy && isset($newPolicy->id)) {
            return redirect()->route('policies.show', $newPolicy->id)
                ->with('success', 'Policy renewed successfully. New policy created.');
        }

        return redirect()->route('policies.show', $originalPolicy->id)
            ->with('warning', 'Renewal created but could not determine new policy to open.');
    }

    /**
     * Export renewals data to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportExcel()
    {
        return Excel::download(new RenewalsExport, 'renewals_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export renewals data to PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPDF()
    {
        $policies = Policy::with(['policyType', 'insurer'])->get();
        $pdf = PDF::loadView('renewals.pdf', compact('policies'));
        return $pdf->download('renewals_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Send renewal notifications for policies expiring in 30 days
     */
    private function sendRenewalNotifications()
    {
        $thirtyDaysFromNow = Carbon::now()->addDays(30);
        $policies = Policy::with(['customer'])
            ->whereDate('end_date', $thirtyDaysFromNow->toDateString())
            ->get();

        foreach ($policies as $policy) {
            if ($policy->customer && $policy->customer->email) {
                Mail::to($policy->customer->email)
                    ->send(new RenewalNotification($policy));
            }
        }
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

        // Calculate metrics
        $now = Carbon::now();
        $next10Days = $now->copy()->addDays(10);
        $next30Days = $now->copy()->addDays(30);
        $next60Days = $now->copy()->addDays(60);

        $metrics = [
            'totalPolicies' => Policy::count(),
            'activePolicies' => Policy::where('coverage', "Comprehensive")->count(),
            'inactivePolicies' => Policy::where('coverage', "TPO")->count(),
            'expiredPolicies' => Policy::where('end_date', '<', $now)->count(),
            '10Days' => Policy::whereBetween('end_date', [$now, $next10Days])
                ->where('end_date', '>=', $now)->count(),
            '30Days' => Policy::whereBetween('end_date', [$next10Days->addSecond(), $next30Days])->count(),
            '60Days' => Policy::whereBetween('end_date', [$next30Days->addSecond(), $next60Days])->count(),
        ];

        // Send renewal notifications for policies expiring in 30 days
        $this->sendRenewalNotifications();

        return view('renewals.index', compact('policies', 'metrics'));
    }

    /**
     * Show renewal history (all policies with same fileno)
     */
    public function history($id)
    {
        $policy = Policy::findOrFail($id);
        $chain = Policy::where('fileno', $policy->fileno)
            ->orderBy('start_date', 'asc')
            ->get();

        return view('policies.history', compact('policy', 'chain'));
    }
}
