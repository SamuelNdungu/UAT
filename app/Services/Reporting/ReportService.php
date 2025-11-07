<?php
namespace App\Services\Reporting;

use App\Models\Policy;
use App\Models\Insurer;
use App\Models\Agent;
use App\Models\PolicyTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Generates the Sales Report query builder based on policy issuance date and filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function generateSalesReportQuery(array $filters): Builder
    {
        // 1. Start the base query, selecting all columns from the policies table
        $query = Policy::query()
            ->with(['insurer', 'agent', 'policyType', 'customer'])
            ->select('policies.*');

        // 2. Apply Date Range Filter (Mandatory)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();

            // Filter by the policy creation date ('created_at' is used for issuance/booking date)
            $query->whereBetween('policies.created_at', [$startDate, $endDate]);
        }
        
        // --- Core Filters ---
        
        // 3. Filter by Insurer
        if (!empty($filters['insurer_id'])) {
            $query->where('insurer_id', $filters['insurer_id']);
        }

        // 4. Filter by Agent
        if (!empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }

        // 5. Filter by Policy Type
        if (!empty($filters['policy_type_id'])) {
            $query->where('policy_type_id', $filters['policy_type_id']);
        }
        
        // 6. Filter by Policy Status
        if (isset($filters['policy_status']) && $filters['policy_status'] === 'Active') {
             // Assuming a 'status' field exists and you want to filter by it
             // $query->where('status', 'Active'); 
        }

        // The query is ordered later in the controller for the paginated table view
        
        // 7. Return the query builder
        return $query;
    }
}
