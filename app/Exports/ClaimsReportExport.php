<?php 

namespace App\Exports;
use Illuminate\Http\Request;
use App\Models\Claim;
use App\Models\Policy;
use App\Models\Customer;
use App\Models\Report;
use App\Exports\ReportExport;
use App\Exports\ClaimsReportExport;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
 

class ClaimsReportExport implements FromCollection, WithHeadings
{
    protected $claims;

    public function __construct($claims)
    {
        $this->claims = $claims;
    }

    public function collection()
    {
        // Fetch claims with related customer and policy data
        return $this->claims->map(function ($claim) {
            // Determine the customer name based on customer_type
            $customerName = '';
            if ($claim->customer->customer_type === 'Individual') {
                $customerName = $claim->customer->first_name . ' ' . $claim->customer->last_name . ' ' . $claim->customer->surname;
            } elseif ($claim->customer->customer_type === 'Corporate') {
                $customerName = $claim->customer->corporate_name;
            }

            return [ 
                'Claim Number' => $claim->claim_number,
                'File Number' => $claim->fileno,
                'Claim Amount' => $claim->amount_claimed,
                'Date of Incident' => $claim->loss_date->format('Y-m-d'),
                'Incident Type' => $claim->type_of_loss,
                'Client Name' => $claim->claimant_name,
                'Customer Name' => $customerName, // Use the determined customer name
                'Policy Type' => $claim->policy->type_of_policy ?? 'N/A', // Assuming `policy` relationship and `type_of_policy` field exist
                'Date Reported' => $claim->reported_date->format('Y-m-d'),
                'Amount Paid' => $claim->amount_paid,
                'Claim Status' => $claim->status,
                'Claim Created At' => $claim->created_at->format('Y-m-d H:i:s'),
                'Claim Updated At' => $claim->updated_at->format('Y-m-d H:i:s'),
                'Customer Code' => $claim->customer_code,
                'Document Path' => $claim->upload_file,
            ];
        });
    }

    
}
