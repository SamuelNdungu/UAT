<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use Carbon\Carbon;
use App\Exports\DebtorsListExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    private function calculateBalance(Policy $policy)
    {
        return $policy->gross_premium - $policy->paid_amount;
    }

    private function getAgingBand(Policy $policy, Carbon $currentDate)
    {
        $daysElapsed = $policy->start_date->diffInDays($currentDate);

        if ($daysElapsed <= 30) {
            return '< 30 Days';
        } elseif ($daysElapsed <= 60) {
            return '30-60 Days';
        } elseif ($daysElapsed <= 90) {
            return '60-90 Days';
        } else {
            return '> 90 Days';
        }
    }

    public function index(Request $request)
    {
        $currentDate = now();

        // Fetch all policies with policy type information
        $policies = Policy::with('policyType')->get();

        $balanceLessThan30 = $policies->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(30), $currentDate);
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balance30To60 = $policies->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(60), $currentDate->copy()->subDays(31));
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balance60To90 = $policies->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(90), $currentDate->copy()->subDays(61));
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balanceMoreThan90 = $policies->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date < $currentDate->copy()->subDays(91);
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $metrics = [
            'balanceLessThan30' => $balanceLessThan30,
            'balance30To60' => $balance30To60,
            'balance60To90' => $balance60To90,
            'balanceMoreThan90' => $balanceMoreThan90,
        ];

        $filter = $request->input('filter');
        $filteredPolicies = $policies->map(function($policy) use ($filter, $currentDate) {
            $policy->balance = $this->calculateBalance($policy);
            $policy->aging_band = $this->getAgingBand($policy, $currentDate);

            switch ($filter) {
                case 'less_than_30':
                    return $policy->balance > 0 && $policy->aging_band === '< 30 Days' ? $policy : null;
                case '30_to_60':
                    return $policy->balance > 0 && $policy->aging_band === '30-60 Days' ? $policy : null;
                case '60_to_90':
                    return $policy->balance > 0 && $policy->aging_band === '60-90 Days' ? $policy : null;
                case 'more_than_90':
                    return $policy->balance > 0 && $policy->aging_band === '> 90 Days' ? $policy : null;
                default:
                    return $policy->balance > 0 ? $policy : null;
            }
        })->filter()->values(); // Use values() to reindex the array

        return view('collection.index', compact('metrics', 'filteredPolicies'));
    }

    public function exportPdf(Request $request)
{
    // Fetch filtered policies with policy type information
    $filteredPolicies = $this->getFilteredPoliciesForExport($request);
    
    // Calculate totals
    $totals = [
        'gross_premium' => $filteredPolicies->sum('gross_premium'),
        'paid_amount' => $filteredPolicies->sum('paid_amount'),
        'due_amount' => $filteredPolicies->sum(function ($policy) {
            return $policy->gross_premium - $policy->paid_amount;
        }),
    ];
    
    $pdf = PDF::loadView('exports.debtors_list_pdf', compact('filteredPolicies', 'totals'));
    return $pdf->download('debtors_list.pdf');
}

public function exportExcel(Request $request)
{
    // Fetch filtered policies with policy type information
    $filteredPolicies = $this->getFilteredPoliciesForExport($request);
    
    // Calculate totals
    $totals = [
        'gross_premium' => $filteredPolicies->sum('gross_premium'),
        'paid_amount' => $filteredPolicies->sum('paid_amount'),
        'due_amount' => $filteredPolicies->sum(function ($policy) {
            return $policy->gross_premium - $policy->paid_amount;
        }),
    ];

    return Excel::download(new DebtorsListExport($filteredPolicies, $totals), 'debtors_list.xlsx');
}


    private function getFilteredPoliciesForExport(Request $request)
    {
        // Determine the filter based on the request query parameter
        $filter = $request->query('filter', 'all'); // Default to 'all'

        // Fetch policies with policy type information using Eloquent
        $policies = Policy::with('policyType')->get();

        // Debugging: Log the policies to check if they contain the policyType relationship
        \Log::info($policies->toArray());

        // Get the current date
        $currentDate = now();

        // Calculate balance and aging_band for each policy
        $filteredPolicies = $policies->map(function($policy) use ($filter, $currentDate) {
            $policy->balance = $this->calculateBalance($policy);
            $policy->aging_band = $this->getAgingBand($policy, $currentDate);

            switch ($filter) {
                case 'less_than_30':
                    return $policy->balance > 0 && $policy->aging_band === '< 30 Days' ? $policy : null;
                case '30_to_60':
                    return $policy->balance > 0 && $policy->aging_band === '30-60 Days' ? $policy : null;
                case '60_to_90':
                    return $policy->balance > 0 && $policy->aging_band === '60-90 Days' ? $policy : null;
                case 'more_than_90':
                    return $policy->balance > 0 && $policy->aging_band === '> 90 Days' ? $policy : null;
                default:
                    return $policy->balance > 0 ? $policy : null;
            }
        })->filter()->values(); // Use values() to reindex the array

        return $filteredPolicies;
    }
}
