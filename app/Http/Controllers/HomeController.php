<?php

 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Default date range
        $startDate = $request->input('start_date', Carbon::now()->startOfYear());
        $endDate = $request->input('end_date', Carbon::now()->endOfYear());

        // Format dates
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Calculate metrics
        $metrics = [
            'totalSales' => DB::table('policies')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('gross_premium'),
            'totalCommission' => DB::table('policies')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('commission'),
            'totalPayments' => DB::table('payments')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('payment_amount'),
            'balance' => DB::table('receipts')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('remaining_amount'),
            'totalAllocated' => DB::table('receipts')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('allocated_amount'),
            'totalPolicies' => DB::table('policies')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'totalClaims' => DB::table('claims')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'expiredPolicies' => DB::table('policies')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('end_date', '<', now())
                ->count(),
        ];

        // Fetch monthly sales data
        $monthlySales = DB::table('policies')
            ->select(DB::raw('SUM(gross_premium) as sales'), DB::raw('EXTRACT(MONTH FROM created_at) as month'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->orderBy(DB::raw('EXTRACT(MONTH FROM created_at)'))
            ->get();

        // Prepare sales data and labels
        $salesData = [];
        $salesLabels = [];

        for ($month = 1; $month <= 12; $month++) {
            $salesLabels[] = $startDate->copy()->startOfMonth()->month($month)->format('M');
            $matchingRecord = $monthlySales->firstWhere('month', $month);
            $salesData[] = $matchingRecord ? $matchingRecord->sales : 0;
        }

        // Fetch policy distribution data
        $policyDistribution = DB::table('policies')
            ->join('policy_types', 'policies.policy_type_id', '=', 'policy_types.id')
            ->select('policy_types.type_name as policy_type', DB::raw('COUNT(*) as count'))
            ->whereBetween('policies.created_at', [$startDate, $endDate])
            ->groupBy('policy_types.type_name')
            ->get();

        // Prepare policy labels and counts
        $policyLabels = $policyDistribution->pluck('policy_type')->toArray();
        $policyCounts = $policyDistribution->pluck('count')->toArray();

        // Calculate percentages for pie chart
        $totalPolicies = array_sum($policyCounts);
        $policyPercentages = array_map(function($count) use ($totalPolicies) {
            return ($totalPolicies > 0) ? ($count / $totalPolicies) * 100 : 0;
        }, $policyCounts);

        $expiringPolicies = \App\Models\Policy::whereBetween('end_date', [now(), now()->addDays(30)])
            ->orderBy('end_date', 'asc')
            ->take(10)
            ->get();

        // Pass all necessary variables to the view
        return view('home', compact('metrics', 'salesData', 'salesLabels', 'policyLabels', 'policyCounts', 'policyPercentages', 'startDate', 'endDate', 'expiringPolicies'));
    }
}
