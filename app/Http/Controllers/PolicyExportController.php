<?php
namespace App\Http\Controllers;

use App\Exports\PoliciesExport;
use App\Exports\PDFExport;
use App\Models\Policy;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use PDF;

class PolicyExportController extends Controller
{
    public function exportExcel()
    {
        return Excel::download(new PoliciesExport, 'policies.xlsx');
    }

        // Method to export data to PDF
        public function exportPDF()
        {
            $pdf = PDF::loadView('exports.policies', [
                'policies' => Policy::with(['customer', 'policyType', 'insurer'])->get()
            ]);
            return $pdf->download('policies.pdf');
        }
    
}
