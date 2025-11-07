<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RenewalListExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $policies;

    public function __construct($policies)
    {
        $this->policies = $policies;
    }

    public function collection()
    {
        return $this->policies;
    }

    public function headings(): array
    {
        return [
            'File No.',
            'Entry Date',
            'Cust Code',
            'Name', 
            'Phone',
            'Email',
            'Policy Type',
            'Start Date',
            'End Date',
            'Insurer',
            'Policy No',
            'Reg.No',
            'Make',
            'Model',
            'Sum Insured',
            'Gross Premium',
            'Status',
            'Notice Status',
            'Actions'
        ];
    }

    public function map($policy): array
    {
        // Get customer data, fall back to policy fields if customer relationship not loaded
        $customerCode = $policy->customer_code;
        $customerName = $policy->customer_name;
        $mobile = $policy->customer->mobile ?? '';
        $phone = $policy->customer->phone ?? '';
        
        // Format financial values, handle null/empty values
        $sumInsured = $this->formatFinancialValue($policy->sum_insured);
        $grossPremium = $this->formatFinancialValue($policy->gross_premium);
        
        // Create a row with proper data types
        $row = [
            $policy->fileno ?? $policy->id, // File No. (use fileno if available, otherwise use ID)
            $policy->created_at?->format('Y-m-d'), // Entry Date
            $customerCode, // Cust Code
            $customerName, // Name 
            $phone, // Phone
            $policy->customer->email ?? '', // Email
            $policy->policyType?->type_name ?? $policy->bus_type, // Policy Type
            $policy->start_date?->format('Y-m-d'), // Start Date
            $policy->end_date?->format('Y-m-d'), // End Date
            $policy->insurer?->name ?? $policy->insurer_id, // Insurer
            $policy->policy_no, // Policy No
            $policy->reg_no ?? 'N/A', // Reg.No
            $policy->make ?? 'N/A', // Make
            $policy->model ?? 'N/A', // Model
            // These will be formatted by the WithColumnFormatting concern
            $sumInsured, // Sum Insured (numeric value for Excel)
            $grossPremium, // Gross Premium (numeric value for Excel)
            $policy->status ?? 'Active', // Status
            'Pending', // Notice Status - default
            '' // Actions - empty for agent to fill
        ];
        
        return $row;
    }
    
    /**
     * Format financial value for Excel
     */
    protected function formatFinancialValue($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        
        // If it's a string with commas, remove them
        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }
        
        return is_numeric($value) ? (float)$value : 0;
    }

    public function title(): string
    {
        return 'Renewal List ' . now()->format('M Y');
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'O' => '#,##0.00',  // Sum Insured
            'P' => '#,##0.00',  // Gross Premium
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3']
                ]
            ],
            // Set header row height
            'A1:S1' => ['font' => ['bold' => true]],
            // Set number format for currency columns
            'O2:P' . (count($this->policies) + 1) => [
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ]
            ],
            // Set date format for date columns
            'B2:I' . (count($this->policies) + 1) => [
                'numberFormat' => [
                    'formatCode' => 'yyyy-mm-dd'
                ]
            ]
        ];
    }
}
