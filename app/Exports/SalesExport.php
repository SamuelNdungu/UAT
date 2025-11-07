<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SalesExport implements FromView
{
    protected $policies;

    public function __construct($policies)
    {
        $this->policies = $policies;
    }

    public function view(): View
    {
        return view('exports.sales', [
            'policies' => $this->policies
        ]);
    }
}
