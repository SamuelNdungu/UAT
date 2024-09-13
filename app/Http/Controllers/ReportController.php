<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Policy;
use App\Models\Customer;
use App\Models\Report;
use App\Exports\ReportExport;
use App\Exports\ClaimsReportExport;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display a listing of all reports.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all reports from the database
        $reports = Report::all();

        // Return the reports.index view with the list of reports
        return view('reports.index', compact('reports'));
    }

    /**
     * Generate a report based on the module, date range, and status.
     * The report can be exported to either PDF or Excel format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request)
    {
        // Get the module (e.g., claims, policies, customers) from the request
        $module = $request->input('module');

        // Get the date range for filtering data (optional)
        $dateRange = $request->input('date_range');

        // Get the status for filtering data (optional)
        $status = $request->input('status');

        // Get the export format (pdf or excel) from the request
        $exportFormat = $request->input('export_format');

        // Fetch data based on the selected module, date range, and status
        $data = $this->fetchReportData($module, $dateRange, $status);

        // Generate and return the report in the desired format
        if ($exportFormat === 'pdf') {
            return $this->exportToPDF($data, $module);
        } else {
            return $this->exportToExcel($data, $module);
        }
    }

    /**
     * Fetch the data for the report based on the selected module, date range, and status.
     *
     * @param string $module
     * @param string|null $dateRange
     * @param string|null $status
     * @return \Illuminate\Support\Collection
     */
    protected function fetchReportData($module, $dateRange, $status)
    {
        // Initialize the query based on the selected module
        switch ($module) {
            case 'claims':
                $query = Claim::query();
                break;
            case 'policies':
                $query = Policy::query();
                break;
            case 'customers':
                $query = Customer::query();
                break;
            default:
                // Return an empty collection if the module is not recognized
                return collect();
        }

        // Apply date range filter if provided
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            $query->whereBetween('created_at', [$dates[0], $dates[1]]);
        }

        // Apply status filter if provided
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Return the result of the query as a collection
        return $query->get();
    }

    /**
     * Export the report data to a PDF file.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $module
     * @return \Illuminate\Http\Response
     */
 

    protected function exportToPDF($data, $module)
    {
        // Select the appropriate view based on the module
        $view = $module === 'claims' ? 'reports.claims_pdf' : 'reports.pdf';
    
        // Load the view and pass the data to it
        $pdf = PDF::loadView($view, ['claims' => $data]);
    
        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');
    
        return $pdf->download("{$module}_report.pdf");
    }
    

    /**
     * Export the report data to an Excel file.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $module
     * @return \Illuminate\Http\Response
     */
    protected function exportToExcel($data, $module)
    {
        // Pass the data collection directly to ReportExport
        return Excel::download(new ReportExport($data), "{$module}_report.xlsx");
    }
    

    /**
     * Download a specific report file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        // Find the report by its ID or fail if not found
        $report = Report::findOrFail($id);

        // Get the file path for the report
        $filePath = storage_path('app/' . $report->file_path);

        // Check if the file exists and return it as a download
        if (file_exists($filePath)) {
            return response()->download($filePath);
        } else {
            // Redirect back to the report index page with an error message if the file is not found
            return redirect()->route('reports.index')->with('error', 'File not found.');
        }
    }

    /**
     * Export a specific report to an Excel file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function exportExcel($id)
    {
        // Find the report by its ID or fail if not found
        $report = Report::findOrFail($id);

        // Use the ReportExport class to export the report data to Excel
        return Excel::download(new ReportExport([$report]), 'report_' . $report->id . '.xlsx');
    }

    /**
     * Export a specific report to a PDF file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function exportPDF($id)
    {
        // Find the report by its ID or fail if not found
        $report = Report::findOrFail($id);

        // Load the PDF view with the report data
        $pdf = PDF::loadView('reports.pdf', compact('report'));

        // Return the generated PDF file as a download
        return $pdf->download('report_' . $report->id . '.pdf');
    }

    /**
     * Export claims data to an Excel file using the generateReport method.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportClaimsExcel(Request $request)
    {
        // Get filter inputs
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');
    
        // Build the query
        $query = Claim::query();
    
        // Apply filters if they are provided
        if ($dateFrom) {
            $query->where('reported_date', '>=', $dateFrom);
        }
    
        if ($dateTo) {
            $query->where('reported_date', '<=', $dateTo);
        }
    
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
    
        // Fetch the filtered data
        $claims = $query->get();
    
        // Pass the filtered data to the export method
        return Excel::download(new ReportExport($claims), 'claims_report.xlsx');
    }
    
    /**
     * Export claims data to a PDF file using the generateReport method.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportClaimsPDF(Request $request)
    {
        // Fetch claims based on your requirements
        $claims = Claim::all();
    
        // Use the exportToPDF method to generate the PDF in landscape
        return $this->exportToPDF($claims, 'claims');
    }
    
}
