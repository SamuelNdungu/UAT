<?php
namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;

class PaymentsExport implements FromCollection
{
    public function collection()
    {
        return Payment::all(); // Adjust according to your needs
    }
}
