<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class RenewalsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    protected $policies;
    protected $filters;
    protected $totalPolicies;
    protected $notices; // To pass notice data

    public function __construct(Collection $policies, array $filters, int $totalPolicies, Collection $notices)
    {
        $this->policies = $policies;
        $this->filters = $filters;
        $this->totalPolicies = $totalPolicies;
        $this->notices = $notices;
    }

    public function collection()
    {
        return $this->policies;
    }

    public function headings(): array
    {
        return [
            ['Renewal Notices Report'],
            [''], // Empty row for spacing
            ['Filters:'],
            ['Start Date: ' . ($this->filters['Start Date'] ?? 'All')],
            ['End Date: ' . ($this->filters['End Date'] ?? 'All')],
            ['Insurer: ' . ($this->filters['Insurer'] ?? 'All')],
            ['Agent: ' . ($this->filters['Agent'] ?? 'All')],
            ['Policy Type: ' . ($this->filters['Policy Type'] ?? 'All')],
            [''], // Empty row for spacing
            ['Total Policies: ' . $this->totalPolicies],
            [''], // Empty row for spacing
            [
                'File No.',
                'Entry Date',
                'Cust Code',
                'Name',
                'Mobile',
                'Phone',
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
            ]
        ];
    }

    public function map($policy): array
    {
        $noticeStatus = 'Not Sent';
        $note = $this->notices->get($policy->id); // Changed from 'policy->fileno' to 'policy->id'

        if ($note) {
            $status = strtolower($note['status'] ?? $note->status ?? '');
            $channel = strtoupper($note['channel'] ?? $note['notice_type'] ?? 'EMAIL');
            
            if ($status === 'sent') {
                $noticeStatus = 'Sent (' . $channel . ')';
            } elseif ($status === 'skipped') {
                $noticeStatus = 'Skipped';
            } elseif ($status === 'failed') {
                $noticeStatus = 'Failed';
            } else {
                $noticeStatus = ucfirst($status ?: 'Unknown') . ' (' . $channel . ')';
            }
        }

        return [
            $policy->fileno,
            Carbon::parse($policy->created_at)->format('d-m-Y'),
            $policy->customer_code,
            $policy->customer_name,
            $policy->mobile ?? $policy->mobile_number ?? '-',
            $policy->phone ?? $policy->telephone ?? '-',
            $policy->policyType->type_name ?? '-',
            Carbon::parse($policy->start_date)->format('d-m-Y'),
            Carbon::parse($policy->end_date)->format('d-m-Y'),
            $policy->insurer->name ?? '-',
            $policy->policy_no,
            $policy->reg_no,
            $policy->make,
            $policy->model,
            $policy->sum_insured,
            number_format($policy->gross_premium, 2),
            $policy->status,
            $noticeStatus,
        ];
    }

    public function title(): string
    {
        return 'Renewal Notices';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:R1'); // Merge for the main title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3:A9')->getFont()->setBold(true); // Filters
        $sheet->getStyle('A11')->getFont()->setBold(true); // Total Policies
        $sheet->getStyle('A13:R13')->getFont()->setBold(true)->setSize(12); // Table headers
        $sheet->getStyle('A13:R13')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0'); // Light grey background for headers
    }
}