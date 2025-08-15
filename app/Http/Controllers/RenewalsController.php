<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\Renewal;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\PolicyTypes;
use App\Models\Insurer;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RenewalsExport;
use Illuminate\Support\Facades\Mail;
use App\Mail\RenewalNotification;
use PDF;

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

        return view('renewals.index', compact('policies', 'metrics'));
    }

    /**
     * Show the form for editing the specified policy.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function renew($id)
    {
        // Find the policy by its ID
        $policy = Policy::findOrFail($id);

        // Get documents from the policy
        $documents = json_decode($policy->documents, true) ?? [];

        // Fetch available policy types, insurers, and vehicle types
        $availablePolicyTypes = PolicyTypes::pluck('type_name', 'id');
        $insurers = Insurer::pluck('name', 'id');
        $availableVehicleTypes = DB::table('vehicle_types')->distinct()->pluck('make', 'make');
        $vehicleModels = DB::table('vehicle_types')->select('make', 'model')->get();

        return view('renewals.renew', compact(
            'policy',
            'documents',
            'availablePolicyTypes',
            'insurers',
            'availableVehicleTypes',
            'vehicleModels'
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
    DB::transaction(function () use ($request, $id) {
        // Get the original policy
        $originalPolicy = Policy::findOrFail($id);
        
        // Create new policy record with renewed data
        $newPolicy = $originalPolicy->replicate();
        $newPolicy->policy_no = $request->policy_no;
        $newPolicy->start_date = $request->start_date;
        $newPolicy->end_date = $request->end_date;
        $newPolicy->status = 'active';
        $newPolicy->save();
        
        // Create renewal record
        Renewal::create([
            'fileno' => $originalPolicy->fileno,
            'original_policy_id' => $originalPolicy->id,
            'renewed_policy_id' => $newPolicy->id,
            'renewal_date' => now(),
            'renewal_type' => 'standard',
            'created_by' => auth()->id()
        ]);
        
        // Update original policy status
        $originalPolicy->status = 'renewed';
        $originalPolicy->save();
    });
    
    return redirect()->route('policies.index')
        ->with('success', 'Policy renewed successfully');
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
}
