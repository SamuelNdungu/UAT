<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    protected $claims;

    public function __construct($claims)
    {
        $this->claims = $claims;
    }

    public function headings(): array
    {
        return [
            'Claim Number',
            'File Number',
            'Claim Amount',
            'Date of Incident',
            'Incident Type',
            'Client Name',
            'Customer Name',
            'Policy Type',
            'Date Reported',
            'Amount Paid',
            'Claim Status',
            'Claim Created At',
            'Claim Updated At',
            'Customer Code',
            'Document Path',
        ];
    }

    public function collection()
    {
        return $this->claims->map(function ($claim) {
            // Determine the customer name based on customer_type
            $customerName = '';
            if ($claim->customer && $claim->customer->customer_type === 'Individual') {
                $customerName = $claim->customer->first_name . ' ' . $claim->customer->last_name . ' ' . $claim->customer->surname;
            } elseif ($claim->customer && $claim->customer->customer_type === 'Corporate') {
                $customerName = $claim->customer->corporate_name;
            }

            return [
                'Claim Number' => $claim->claim_number,
                'File Number' => $claim->fileno,
                'Claim Amount' => $claim->amount_claimed,
                'Date of Incident' => $this->formatDate($claim->loss_date),
                'Incident Type' => $claim->type_of_loss,
                'Client Name' => $claim->claimant_name,
                'Customer Name' => $customerName ?? 'N/A',
                'Policy Type' => $claim->policy->type_of_policy ?? 'N/A',
                'Date Reported' => $this->formatDate($claim->reported_date),
                'Amount Paid' => $claim->amount_paid,
                'Claim Status' => $claim->status,
                'Claim Created At' => $this->formatDateTime($claim->created_at),
                'Claim Updated At' => $this->formatDateTime($claim->updated_at),
                'Customer Code' => $claim->customer_code,
                'Document Path' => $claim->upload_file,
            ];
        });
    }

    private function formatDate($date)
    {
        return $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date;
    }

    private function formatDateTime($dateTime)
    {
        return $dateTime instanceof \Carbon\Carbon ? $dateTime->format('Y-m-d H:i:s') : $dateTime;
    }
 
}
