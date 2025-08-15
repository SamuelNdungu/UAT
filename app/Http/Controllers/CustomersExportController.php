<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Customer;

use PDF; // Assuming you are using barryvdh/laravel-dompdf

class CustomersExportController extends Controller
{
    /**
     * Export customers to Excel
     */
    public function customersexportExcel()
    {
        return Excel::download(new CustomersExport, 'customers.xlsx');
    }

    /**
     * Export customers to PDF
     */
    public function customersexportPDF()
    {
        // Fetch the data you want to include in the PDF (you can customize it as needed)
        $customers = Customer::all(); 
        
        // Load the view and pass the data to the PDF generator
        $pdf = PDF::loadView('customers.pdf', compact('customers'));

        // Download the generated PDF
        return $pdf->download('customers.pdf');
    }
}
