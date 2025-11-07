<?php

namespace App\Exports;

use App\Models\Policy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * Exports a collection of Policy models to a formatted Excel file.
 * Requires policies to be eagerly loaded with 'customer', 'policyType', and 'insurer'.
 */
class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @var Collection
     */
    protected $policies;

    public function __construct(Collection $policies)
    {
        $this->policies = $policies;
    }

    /**
     * Returns the collection of policies to export.
     * @return Collection
     */
    public function collection()
    {
        // The controller passes the filtered and eagerly loaded collection
        return $this->policies;
    }

    /**
     * Defines the header row for the Excel file.
     * @return array
     */
    public function headings(): array
    {
        return [
            'Policy No',
            'Customer Name',
            'Policy Type',
            'Insurer',
            'Gross Premium (KSH)',
            'Commission (KSH)',
            'Start Date',
            'End Date',
            'Status',
            'Date Created',
        ];
    }

    /**
     * Maps a single policy model instance to a row in the Excel sheet.
     * @param mixed $policy
     * @return array
     */
    public function map($policy): array
    {
        return [
            $policy->policy_no,
            // Use the Customer model's name accessor if available
            $policy->customer?->name ?? $policy->customer_name ?? '-',
            $policy->policyType->type_name ?? '-',
            $policy->insurer->name ?? '-',
            // Return as float/numeric type so Excel can handle calculations
            (float)$policy->gross_premium,
            (float)$policy->commission,
            Carbon::parse($policy->start_date)->format('Y-m-d'),
            Carbon::parse($policy->end_date)->format('Y-m-d'),
            $policy->status ?? '-',
            Carbon::parse($policy->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
