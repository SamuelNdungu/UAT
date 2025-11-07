<?php

namespace App\Exports;

use App\Models\Policy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PoliciesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Policy::with(['customer', 'policyType', 'insurer'])->get(); // Eager load related models
    }

    public function headings(): array
    {
        return [
            'File No.',
            'Buss Date',
            'Cust Code',
            'Name',
            'Policy Type',
            'Coverage',
            'Start Date', 
            'End Date',
            'Insurer',
            'Policy No',
            'Reg.No',
            'Make',
            'Model', 
            'Sum Insured',
            'P. Rate (%)',
            'Premium',
            'C. Rate (%)',
            'Comm.',
            'WHT',
            'Stamp Duty',
            'T.Levy',
            'PCF Levy',
            'Policy Charge',
            'AA Charges',
            'Other Charges',
            'Gross Premium',
            'Net Premium',
            'Paid Amount',
            'Balance',
            'Agent Name',
            'Agent Commision' 
        ];
    }

    public function map($policy): array
    {
        return [
            $policy->fileno,                     
            $policy->created_at ? $policy->created_at->format('Y-m-d') : null, 
            $policy->customer_code,                     
            $policy->customer ? $policy->customer->customer_name : 'N/A',  
            $policy->policyType ? $policy->policyType->type_name : 'N/A',              
            $policy->coverage,                    
            $policy->start_date ? $policy->start_date->format('Y-m-d') : null,                       
            $policy->end_date ? $policy->end_date->format('Y-m-d') : null,   
            $policy->insurer ? $policy->insurer->name : 'N/A',                      
            $policy->policy_no,                   
            $policy->reg_no,                      
            $policy->make,                        
            $policy->model,                        
            number_format($policy->sum_insured, 2), 
            $policy->p_rate,                      
            number_format($policy->premium, 2),   
            $policy->c_rate,                      
            number_format($policy->commission, 2), 
            number_format($policy->wht, 2),      
            number_format($policy->stamp_duty, 2),
            number_format($policy->t_levy, 2),     
            number_format($policy->pcf_levy, 2),   
            number_format($policy->policy_charge, 2), 
            number_format($policy->aa_charges, 2),    
            number_format($policy->other_charges, 2), 
            number_format($policy->gross_premium, 2), 
            number_format($policy->net_premium, 2),     
            number_format($policy->paid_amount, 2),    
            number_format($policy->balance, 2),        
            $policy->agent ? $policy->agent->name : '',
            $policy->agent_commission,
        ];
    }

    // Implementing the styles method from the WithStyles interface
    public function styles(Worksheet $sheet)
    {
        // Set styles for the header row
        return [
            1 => [ // Styles for the first row (header)
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => '581845'],  // Header background color (blue)
                ],
                'alignment' => [
                'wrapText' => false, // Set nowrap for header row
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,],
            ],
        ];
    }
}
