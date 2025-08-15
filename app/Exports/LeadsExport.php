<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Lead::all();
    }

    public function headings(): array
    {
        return [
            'Lead Type',
            'Corporate Name',
            'First Name',
            'Last Name',
            'Mobile',
            'Email',
            'Policy Type',
            'Lead Source',
            'Follow Up Date',
            'Deal Size',
            'Probability',
            'Deal Stage',
            'Deal Status',
            'Date Initiated',
            'Closing Date',
            'Next Action',
            'Notes',
            'Created At'
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->lead_type,
            $lead->corporate_name,
            $lead->first_name,
            $lead->last_name,
            $lead->mobile,
            $lead->email,
            $lead->policy_type,
            $lead->lead_source,
            $lead->follow_up_date,
            $lead->deal_size,
            $lead->probability . '%',
            $lead->deal_stage,
            $lead->deal_status,
            $lead->date_initiated,
            $lead->closing_date,
            $lead->next_action,
            $lead->notes,
            $lead->created_at->format('Y-m-d')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}