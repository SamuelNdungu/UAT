<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Policy;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function fetchData(Request $request)
    {
        // This is a placeholder method to handle the /fetch-data route
        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully',
            'data' => []
        ]);
    }

    public function monthlySalesCommission(Request $request)
    {
        try {
            // Get date range from request or default to current year
            $startDate = $request->input('start_date', Carbon::now()->startOfYear());
            $endDate = $request->input('end_date', Carbon::now()->endOfYear());

            // Parse dates if they're strings
            if (is_string($startDate)) {
                $startDate = Carbon::parse($startDate);
            }
            if (is_string($endDate)) {
                $endDate = Carbon::parse($endDate);
            }

            // Get monthly sales and commission data
            $salesData = Policy::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('EXTRACT(MONTH FROM created_at) as month, 
                             SUM(gross_premium) as total_sales,
                             SUM(commission) as total_commission')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $salesData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching monthly sales commission data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
