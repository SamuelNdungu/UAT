<?php  

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use Carbon\Carbon;

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
        } // <-- Missing closing brace added here
    }

    public function index(Request $request)
    {
        $currentDate = now();

        $balanceLessThan30 = Policy::all()->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(30), $currentDate);
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balance30To60 = Policy::all()->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(60), $currentDate->copy()->subDays(31));
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balance60To90 = Policy::all()->filter(function($policy) use ($currentDate) {
            return $this->calculateBalance($policy) > 0 && $policy->start_date->between($currentDate->copy()->subDays(90), $currentDate->copy()->subDays(61));
        })->sum(function($policy) {
            return $this->calculateBalance($policy);
        });

        $balanceMoreThan90 = Policy::all()->filter(function($policy) use ($currentDate) {
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
        $filteredPolicies = Policy::all()->map(function($policy) use ($filter, $currentDate) {
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
        })->filter();

        return view('collection.index', compact('metrics', 'filteredPolicies'));
    }
}
