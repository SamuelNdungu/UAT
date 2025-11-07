<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Insurer;
use App\Models\Agent;
use App\Models\Policy;
use App\Models\PolicyTypes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel; // <-- CRITICAL: Required for Excel::download()
use App\Exports\SalesExport; // <-- CRITICAL: The Export class must be imported
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the sales report index page.
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function salesIndex(Request $request)
    {
        // Set default date range for filtering
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()->endOfDay()));
        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->startOfYear()));

        // 1. Fetch data required for the filter dropdowns
        $insurers = Insurer::pluck('name', 'id');
        $agents = Agent::pluck('name', 'id');
        $policyTypes = PolicyTypes::pluck('type_name', 'id');

        // 2. Build the main query for policies, applying filters
        $policiesQuery = Policy::query();

        $policiesQuery->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        // Apply Filters
        if ($insurerId = $request->input('insurer_id')) {
            $policiesQuery->where('insurer_id', $insurerId);
        }
        if ($agentId = $request->input('agent_id')) {
            $policiesQuery->where('agent_id', $agentId);
        }
        if ($policyTypeId = $request->input('policy_type_id')) {
            $policiesQuery->where('policy_type_id', $policyTypeId);
        }

        // IMPORTANT: Use paginate for the index view
        $policies = $policiesQuery->orderBy('created_at', 'desc')->paginate(20);

        // 3. Calculate Metrics
        // Note: For metric calculation simplicity, the filters used for pagination may not be applied here
        // if this is intended to show TOTALs regardless of current report filter state.
        // Assuming metrics should align with the filtered data for this specific report view:
        $metricsQuery = Policy::query();
        $metricsQuery->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        if ($insurerId) { $metricsQuery->where('insurer_id', $insurerId); }
        if ($agentId) { $metricsQuery->where('agent_id', $agentId); }
        if ($policyTypeId) { $metricsQuery->where('policy_type_id', $policyTypeId); }

        $totalPremium = $metricsQuery->sum('gross_premium');
        $totalCommission = $metricsQuery->sum('commission');
        $totalPolicies = $metricsQuery->count();


        // 4. Pass all necessary data to the view
        return view('reports.sales.index', compact(
            'policies',
            'totalPremium',
            'totalCommission',
            'totalPolicies',
            'insurers',
            'agents',
            'policyTypes',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export the filtered sales data to an Excel file.
     * This method is called by the 'reports.sales.export' route.
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function salesExport(Request $request) // <-- THIS METHOD MUST BE PRESENT
    {
        // 1. Replicate Filtering Logic (Crucial to ensure export matches the report view)
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()->endOfDay()));
        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->startOfYear()));

        $policiesQuery = Policy::query();

        // Apply the same date range filter
        $policiesQuery->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        // Apply Insurer Filter
        if ($insurerId = $request->input('insurer_id')) {
            $policiesQuery->where('insurer_id', $insurerId);
        }

        // Apply Agent Filter
        if ($agentId = $request->input('agent_id')) {
            $policiesQuery->where('agent_id', $agentId);
        }

        // Apply Policy Type Filter
        if ($policyTypeId = $request->input('policy_type_id')) {
            $policiesQuery->where('policy_type_id', $policyTypeId);
        }

        // 2. Fetch ALL filtered data (no pagination)
        $policiesToExport = $policiesQuery
            ->with(['customer', 'policyType', 'insurer']) // Eager load relationships for mapping
            ->orderBy('created_at', 'desc')
            ->get(); // Get all results

        if ($policiesToExport->isEmpty()) {
            return redirect()->route('reports.sales')->with('error', 'No data found for the selected filters to export.');
        }

        // 3. Generate and download the Excel file
        $filename = 'Sales_Report_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.xlsx';

        // Use the SalesExport class to handle the data transformation
        return Excel::download(new SalesExport($policiesToExport), $filename);
    }



public function salesPdfExport(Request $request) // <-- NEW METHOD FOR PDF EXPORT
    {
        // 1. Replicate Filtering Logic (Crucial to ensure export matches the report view)
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()->endOfDay()));
        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->startOfYear()));

        $policiesQuery = Policy::query();

        // Apply the same date range filter
        $policiesQuery->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        // Apply Insurer Filter
        if ($insurerId = $request->input('insurer_id')) {
            $policiesQuery->where('insurer_id', $insurerId);
        }

        // Apply Agent Filter
        if ($agentId = $request->input('agent_id')) {
            $policiesQuery->where('agent_id', $agentId);
        }

        // Apply Policy Type Filter
        if ($policyTypeId = $request->input('policy_type_id')) {
            $policiesQuery->where('policy_type_id', $policyTypeId);
        }

        // 2. Fetch ALL filtered data (no pagination)
        $policiesToExport = $policiesQuery
            ->with(['customer', 'policyType', 'insurer', 'agent']) // Eager load relationships
            ->orderBy('created_at', 'desc')
            ->get();

        if ($policiesToExport->isEmpty()) {
            return redirect()->route('reports.sales.index')->with('error', 'No data found for the selected filters to export.');
        }

        // 3. Calculate Report Totals
        $totalPremium = $policiesToExport->sum('gross_premium');
        $totalCommission = $policiesToExport->sum('commission');
        $totalPolicies = $policiesToExport->count();

        $data = [
            'policies' => $policiesToExport,
            'startDate' => $startDate->format('d-M-Y'),
            'endDate' => $endDate->format('d-M-Y'),
            'totalPremium' => $totalPremium,
            'totalCommission' => $totalCommission,
            'totalPolicies' => $totalPolicies,
            'reportTitle' => 'Sales Performance Report',
            // Pass selected filters for display on the PDF
            'filters' => [
                'Insurer' => $request->input('insurer_id') ? Insurer::find($request->input('insurer_id'))->name : 'All',
                'Agent' => $request->input('agent_id') ? Agent::find($request->input('agent_id'))->name : 'All',
                'Policy Type' => $request->input('policy_type_id') ? PolicyTypes::find($request->input('policy_type_id'))->type_name : 'All',
            ]
        ];

        // 4. Generate and download the PDF file
        $pdf = PDF::loadView('exports.sales_pdf', $data)
                  ->setPaper('a4', 'landscape'); // Use landscape orientation for more columns

        $filename = 'Sales_Report_' . Carbon::now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

}
