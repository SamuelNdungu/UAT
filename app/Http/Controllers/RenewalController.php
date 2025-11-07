<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Policy;
use App\Models\Insurer;    // Added for filters
use App\Models\Agent;      // Added for filters
use App\Models\PolicyType; // Added for filters
use App\Models\RenewalNotice; // Added for notices
use App\Notifications\RenewalSmsNotification;
use Illuminate\Support\Carbon; // Added for date handling
use Maatwebsite\Excel\Facades\Excel; // Added for Excel export
use Barryvdh\DomPDF\Facade\Pdf; // Added for PDF export

class RenewalController extends Controller
{
    protected function findPolicyForCustomer($id)
    {
        // Try to find a policy by policy id, lead_id or user_id - prefer soonest expiry
        return Policy::where('id', $id)
            ->orWhere('lead_id', $id)
            ->orWhere('user_id', $id)
            ->orderBy('end_date', 'asc')
            ->first();
    }

    public function sendEmail($id)
    {
        $policy = $this->findPolicyForCustomer($id);

        if (! $policy) {
            return redirect()->back()->with('error', 'No policy found for that customer.');
        }

        $to = $policy->email ?? ($policy->customer->email ?? null);

        if (! $to) {
            return redirect()->back()->with('error', 'Customer email not available.');
        }

        try {
            Mail::send('emails.renewal', ['policy' => $policy], function ($m) use ($policy, $to) {
                $m->to($to);
                $m->subject('Policy Renewal Notice - ' . ($policy->fileno ?? $policy->policy_no));
            });

            return redirect()->back()->with('success', 'Renewal email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Renewal email error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to send renewal email.');
        }
    }

    public function sendSms($id)
    {
        $policy = $this->findPolicyForCustomer($id);

        if (! $policy) {
            return redirect()->back()->with('error', 'No policy found for that customer.');
        }

        $phone = $policy->phone ?? ($policy->customer->phone ?? null);

        if (! $phone) {
            return redirect()->back()->with('error', 'Customer phone number not available.');
        }

        try {
            $message = view('texts.renewal_sms', compact('policy'))->render();

            // If Nexmo/Vonage configured -> send via notification channel, else fallback to logging
            if (config('services.nexmo.key') || config('services.vonage.key')) {
                Notification::route('nexmo', $phone)
                    ->notify(new RenewalSmsNotification($message));
            } else {
                Log::info("SIMULATED SMS to {$phone}: " . strip_tags($message));
            }

            return redirect()->back()->with('success', 'Renewal SMS sent successfully.');
        } catch (\Exception $e) {
            Log::error('Renewal SMS error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to send renewal SMS.');
        }
    }

    public function index(Request $request)
    {
        // Initialize query for policies
        $baseQuery = Policy::query() // Use a base query builder
            ->where('status', 'Active'); // Assuming only active policies are considered for renewals

        // Apply date filters based on policy end_date
        // Default to current year if no dates are provided
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->endOfYear()->toDateString());

        $baseQuery->whereDate('end_date', '>=', $startDate);
        $baseQuery->whereDate('end_date', '<=', $endDate);

        // Apply other filters
        if ($request->filled('insurer_id')) {
            $baseQuery->where('insurer_id', $request->input('insurer_id'));
        }
        if ($request->filled('agent_id')) {
            $baseQuery->where('agent_id', $request->input('agent_id'));
        }
        if ($request->filled('policy_type_id')) {
            $baseQuery->where('policy_type_id', $request->input('policy_type_id'));
        }

        // --- Calculate Metrics and Total Policies from the base query before pagination ---
        // Clone the query for metrics to avoid modifying the main query for pagination
        $metricsQuery = clone $baseQuery; 
        $totalPolicies = $metricsQuery->count();

        $metrics = [
            '10Days' => (clone $metricsQuery)->whereDate('end_date', '>=', now()->startOfDay())
                                            ->whereDate('end_date', '<=', now()->addDays(10)->endOfDay())
                                            ->count(),
            '30Days' => (clone $metricsQuery)->whereDate('end_date', '>=', now()->startOfDay())
                                            ->whereDate('end_date', '<=', now()->addDays(30)->endOfDay())
                                            ->count(),
            '60Days' => (clone $metricsQuery)->whereDate('end_date', '>=', now()->startOfDay())
                                            ->whereDate('end_date', '<=', now()->addDays(60)->endOfDay())
                                            ->count(),
            'expiredPolicies' => (clone $metricsQuery)->whereDate('end_date', '<', now()->startOfDay()) // Policies whose end_date is before today
                                                     ->count(),
        ];
        // --- End Metric Calculation ---

        // Get filtered policies for display (paginated)
        $policies = $baseQuery->with(['customer', 'policyType', 'insurer', 'agent']) // Eager load relationships
                              ->orderBy('end_date', 'asc')
                              ->paginate(20)
                              ->appends($request->query());

        // Fetch data for filter dropdowns
        $insurers = Insurer::pluck('name', 'id');
        $agents = Agent::pluck('name', 'id');
        $policyTypes = PolicyType::pluck('type_name', 'id');

        // Fetch renewal notices for the current page's policies
        $policyIds = $policies->pluck('id')->toArray();
        $notices = RenewalNotice::whereIn('policy_id', $policyIds)
                                ->get()
                                ->keyBy('policy_id');

        // Prepare filters array for export title
        $filters = [
            'Start Date' => Carbon::parse($startDate)->format('d-M-Y'),
            'End Date' => Carbon::parse($endDate)->format('d-M-Y'),
            'Insurer' => $request->input('insurer_id') ? $insurers[$request->input('insurer_id')] : 'All',
            'Agent' => $request->input('agent_id') ? $agents[$request->input('agent_id')] : 'All',
            'Policy Type' => $request->input('policy_type_id') ? $policyTypes[$request->input('policy_type_id')] : 'All',
        ];

        return view('renewals.index', compact(
            'policies', 'metrics', 'insurers', 'agents', 'policyTypes', 'notices', 'totalPolicies', 'filters'
        ));
    }

    public function exportExcel(Request $request)
    {
        // Re-apply filters to get the same dataset as displayed
        $query = Policy::query()
            ->with(['customer', 'policyType', 'insurer', 'agent'])
            ->where('status', 'Active');

        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->endOfYear()->toDateString());

        $query->whereDate('end_date', '>=', $startDate);
        $query->whereDate('end_date', '<=', $endDate);

        if ($request->filled('insurer_id')) {
            $query->where('insurer_id', $request->input('insurer_id'));
        }
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }
        if ($request->filled('policy_type_id')) {
            $query->where('policy_type_id', $request->input('policy_type_id'));
        }

        $policies = $query->orderBy('end_date', 'asc')->get();

        // Fetch data for filter dropdowns to resolve names for the report title
        $insurers = Insurer::pluck('name', 'id');
        $agents = Agent::pluck('name', 'id');
        $policyTypes = PolicyType::pluck('type_name', 'id');

        $filters = [
            'Start Date' => Carbon::parse($startDate)->format('d-M-Y'),
            'End Date' => Carbon::parse($endDate)->format('d-M-Y'),
            'Insurer' => $request->input('insurer_id') ? $insurers[$request->input('insurer_id')] : 'All',
            'Agent' => $request->input('agent_id') ? $agents[$request->input('agent_id')] : 'All',
            'Policy Type' => $request->input('policy_type_id') ? $policyTypes[$request->input('policy_type_id')] : 'All',
        ];

        $totalPolicies = $policies->count();

        // Pass notices to the export if needed for the 'Notice Status' column
        $policyIds = $policies->pluck('id')->toArray();
        $notices = RenewalNotice::whereIn('policy_id', $policyIds)
                                ->get()
                                ->keyBy('policy_id');

        return Excel::download(new \App\Exports\RenewalsExport($policies, $filters, $totalPolicies, $notices), 'renewal_notices_report.xlsx');
    }

    public function exportPdf(Request $request)
    {
        // Re-apply filters to get the same dataset as displayed
        $query = Policy::query()
            ->with(['customer', 'policyType', 'insurer', 'agent'])
            ->where('status', 'Active');

        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->endOfYear()->toDateString());

        $query->whereDate('end_date', '>=', $startDate);
        $query->whereDate('end_date', '<=', $endDate);

        if ($request->filled('insurer_id')) {
            $query->where('insurer_id', $request->input('insurer_id'));
        }
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }
        if ($request->filled('policy_type_id')) {
            $query->where('policy_type_id', $request->input('policy_type_id'));
        }

        $policies = $query->orderBy('end_date', 'asc')->get();

        // Fetch data for filter dropdowns to resolve names for the report title
        $insurers = Insurer::pluck('name', 'id');
        $agents = Agent::pluck('name', 'id');
        $policyTypes = PolicyType::pluck('type_name', 'id');

        $filters = [
            'Start Date' => Carbon::parse($startDate)->format('d-M-Y'),
            'End Date' => Carbon::parse($endDate)->format('d-M-Y'),
            'Insurer' => $request->input('insurer_id') ? $insurers[$request->input('insurer_id')] : 'All',
            'Agent' => $request->input('agent_id') ? $agents[$request->input('agent_id')] : 'All',
            'Policy Type' => $request->input('policy_type_id') ? $policyTypes[$request->input('policy_type_id')] : 'All',
        ];

        $totalPolicies = $policies->count();

        // Pass notices to the PDF view if needed for the 'Notice Status' column
        $policyIds = $policies->pluck('id')->toArray();
        $notices = RenewalNotice::whereIn('policy_id', $policyIds)
                                ->get()
                                ->keyBy('policy_id');

        $pdf = Pdf::loadView('reports.renewals.pdf', compact('policies', 'filters', 'totalPolicies', 'notices'));
        return $pdf->download('renewal_notices_report.pdf');
    }

    public function renew($id)
    {
        $policy = Policy::findOrFail($id);
        if ($policy->isCancelled()) {
            return redirect()->route('policies.show', $policy->id)
                ->with('error', 'Canceled policies cannot be renewed.');
        }
        // ...existing renewal logic...
    }
}