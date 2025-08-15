<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DebtorsListExport implements FromCollection, WithHeadings
{
    protected $filteredPolicies;
    protected $totals;

    public function __construct(Collection $filteredPolicies, array $totals)
    {
        $this->filteredPolicies = $filteredPolicies;
        $this->totals = $totals;
    }

    public function collection()
    {
        return $this->filteredPolicies->map(function($policy) {
            return [
                'File No.' => $policy->fileno,
                'Entry Date' => \Carbon\Carbon::parse($policy->buss_date)->format('Y-m-d'),
                'Name' => $policy->customer_name,
                'Policy Type' => $policy->policy_type_name ?? 'N/A',
                'Start Date' => \Carbon\Carbon::parse($policy->start_date)->format('Y-m-d'),
                'End Date' => \Carbon\Carbon::parse($policy->end_date)->format('Y-m-d'),
                'Reg.No' => $policy->reg_no,
                'Gross Premium' => number_format($policy->gross_premium, 2),
                'Paid Amount' => number_format($policy->paid_amount, 2),
                'Due Amount' => number_format($policy->gross_premium - $policy->paid_amount, 2),
            ];
        })->push([
            'File No.' => '',
            'Entry Date' => '',
            'Name' => 'Totals:',
            'Policy Type' => '',
            'Start Date' => '',
            'End Date' => '',
            'Reg.No' => '',
            'Gross Premium' => number_format($this->totals['gross_premium'], 2),
            'Paid Amount' => number_format($this->totals['paid_amount'], 2),
            'Due Amount' => number_format($this->totals['due_amount'], 2),
        ]);
    }

    public function headings(): array
    {
        return [
            'File No.',
            'Entry Date',
            'Name',
            'Policy Type',
            'Start Date',
            'End Date',
            'Reg.No',
            'Gross Premium',
            'Paid Amount',
            'Due Amount',
        ];
    }
}
