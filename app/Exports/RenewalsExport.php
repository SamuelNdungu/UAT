<?php

namespace App\Exports;

use App\Models\Policy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class RenewalsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Policy::with(['policyType', 'insurer', 'customer'])
            ->orderBy('end_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'File No',
            'Customer Name',
            'Policy Type',
            'Policy Number',
            'Insurer',
            'Start Date',
            'End Date',
            'Days to Expiry',
            'Sum Insured',
            'Premium',
            'Status'
        ];
    }

    public function map($policy): array
    {
        $daysToExpiry = Carbon::parse($policy->end_date)->diffInDays(Carbon::now(), false);
        $status = $daysToExpiry < 0 ? 'Expired' : ($daysToExpiry <= 30 ? 'Due for Renewal' : 'Active');

        return [
            $policy->fileno,
            $policy->customer_name,
            $policy->policyType->type_name,
            $policy->policy_no,
            $policy->insurer->name,
            $policy->start_date,
            $policy->end_date,
            abs($daysToExpiry),
            number_format($policy->sum_insured, 2),
            number_format($policy->premium, 2),
            $status
        ];
    }
}