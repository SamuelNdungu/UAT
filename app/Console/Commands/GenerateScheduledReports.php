<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Claim;
use App\Models\Agent;
use App\Models\Policy;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate 
                            {--frequency=weekly : Frequency of the report (weekly/monthly)}
                            {--agent= : Process report for a specific agent ID}
                            {--email= : (Deprecated) Use agent-specific emails instead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and email scheduled reports for claims and agent performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = $this->option('frequency');
        $specificAgentId = $this->option('agent');
        
        if (!in_array($frequency, ['weekly', 'monthly'])) {
            $this->error('Invalid frequency. Use either "weekly" or "monthly".');
            return 1;
        }

        $this->info("Generating {$frequency} agent reports...");
        
        try {
            // Set date range - for testing, include last 30 days to get some data
            $startDate = now()->subDays(30);
            $endDate = now();
            
            $this->info("Looking for policies between {$startDate} and {$endDate}");
            
            // For testing, we'll use the admin email address
            $adminEmail = 's2ndungu@gmail.com';
            $this->info("Sending test report to admin email: " . $adminEmail);
            
            // Create a test agent object with admin email
            $testAgent = new \App\Models\Agent([
                'id' => 0,
                'name' => 'Test Agent',
                'email' => $adminEmail,
                'status' => 'active',
                'agent_code' => 'ADMIN',
            ]);
            
            // Create a test policy for the report
            $testPolicies = collect([
                (object)[
                    'id' => 1,
                    'policy_no' => 'POL-TEST-' . time(),
                    'customer_id' => 1,
                    'customer' => (object)['name' => 'Test Customer'],
                    'policy_type' => (object)['type_name' => 'Test Policy'],
                    'insurer' => (object)['name' => 'Test Insurer'],
                    'gross_premium' => 5000.00,
                    'commission' => 500.00,
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'status' => 'active',
                ]
            ]);
            
            $agents = collect([$testAgent]);
            
            $totalEmailsSent = 0;
            
            foreach ($agents as $agent) {
                try {
                    $this->info("Processing report for agent: {$agent->name} ({$agent->email})");
                    
                    // Use test policies for the demo
                    $policies = $testPolicies;
                    $this->info("Using test policies for the demo report.");
                    
                    // Generate agent-specific reports
                    $claimsReport = $this->generateAgentClaimsSummary($agent, $frequency);
                    $agentReport = [
                        'period' => $frequency === 'weekly' ? 'Weekly' : 'Monthly',
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'agent_name' => $agent->name,
                        'agent_code' => $agent->agent_code ?? 'N/A',
                        'total_policies' => $policies->count(),
                        'total_premium' => $policies->sum('gross_premium'),
                        'total_commission' => $policies->sum('commission'),
                        'renewal_rate' => 0,
                        'policies_by_type' => $policies->groupBy('policy_type.type_name')->map->count(),
                    ];
                    
                    // Send email to agent
                    $this->sendAgentEmailReport($agent, $frequency, $claimsReport, $agentReport, $policies, $startDate, $endDate);
                    
                    $totalEmailsSent++;
                    $this->info("Report sent to {$agent->email} successfully!");
                    
                } catch (\Exception $e) {
                    $this->error("Failed to process report for agent {$agent->name}: " . $e->getMessage());
                    Log::error("Failed to process report for agent {$agent->id}: " . $e->getMessage());
                }
                
                // Small delay between sending emails to avoid rate limiting
                sleep(2);
            }
            
            $this->info("\nCompleted! Sent {$totalEmailsSent} agent reports successfully.");
            return 0;
            
        } catch (\Exception $e) {
            Log::error("Failed to generate reports: " . $e->getMessage());
            $this->error("Failed to generate reports: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate agent-specific claims summary
     */
    private function generateAgentClaimsSummary($agent, $frequency)
    {
        $startDate = $frequency === 'weekly' 
            ? now()->startOfWeek() 
            : now()->startOfMonth();
            
        $endDate = $frequency === 'weekly' 
            ? now()->endOfWeek() 
            : now()->endOfMonth();

        // Get claims for this agent's policies
        $totalClaims = Claim::whereHas('policy', function($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $pendingClaims = Claim::whereHas('policy', function($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $approvedClaims = Claim::whereHas('policy', function($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $rejectedClaims = Claim::whereHas('policy', function($query) use ($agent) {
                $query->where('agent_id', $agent->id);
            })
            ->where('status', 'rejected')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'period' => $frequency === 'weekly' ? 'Weekly' : 'Monthly',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_claims' => $totalClaims,
            'pending_claims' => $pendingClaims,
            'approved_claims' => $approvedClaims,
            'rejected_claims' => $rejectedClaims,
            'approval_rate' => $totalClaims > 0 ? round(($approvedClaims / $totalClaims) * 100, 2) : 0,
            'claims_by_status' => [
                'Pending' => $pendingClaims,
                'Approved' => $approvedClaims,
                'Rejected' => $rejectedClaims,
            ],
        ];
    }

    /**
     * Generate claims summary report (for backward compatibility)
     */
    private function generateClaimsSummary($frequency)
    {
        $startDate = $frequency === 'weekly' 
            ? now()->startOfWeek() 
            : now()->startOfMonth();
            
        $endDate = $frequency === 'weekly' 
            ? now()->endOfWeek() 
            : now()->endOfMonth();

        $totalClaims = Claim::whereBetween('created_at', [$startDate, $endDate])->count();
        $pendingClaims = Claim::where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $approvedClaims = Claim::where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $rejectedClaims = Claim::where('status', 'rejected')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        // Calculate average processing time in hours (database-agnostic way)
        $claims = Claim::where('status', '!=', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
            
        $totalHours = 0;
        $processedCount = 0;
        
        foreach ($claims as $claim) {
            if ($claim->updated_at && $claim->created_at) {
                $diffInHours = $claim->updated_at->diffInHours($claim->created_at);
                $totalHours += $diffInHours;
                $processedCount++;
            }
        }
        
        $averageProcessingTime = $processedCount > 0 ? $totalHours / $processedCount : 0;

        return [
            'period' => $frequency === 'weekly' ? 'Weekly' : 'Monthly',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_claims' => $totalClaims,
            'pending_claims' => $pendingClaims,
            'approved_claims' => $approvedClaims,
            'rejected_claims' => $rejectedClaims,
            'approval_rate' => $totalClaims > 0 ? round(($approvedClaims / $totalClaims) * 100, 2) : 0,
            'average_processing_hours' => round($averageProcessingTime ?? 0, 2),
            'claims_by_status' => [
                'Pending' => $pendingClaims,
                'Approved' => $approvedClaims,
                'Rejected' => $rejectedClaims,
            ],
        ];
    
    $startDate = $frequency === 'weekly' 
        ? now()->startOfWeek() 
        : now()->startOfMonth();
            
        // Get policies that were up for renewal in the previous period
        $renewablePolicies = $agent->policies()
            ->where('end_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate)
            ->count();
            
        if ($renewablePolicies === 0) {
            return 0;
        }
            
        // Count how many were actually renewed
        $renewedPolicies = 0;
        
        // Check if the renewals relationship exists
        if (method_exists(Policy::class, 'renewals')) {
            $renewedPolicies = $agent->policies()
                ->whereHas('renewals', function($query) use ($startDate, $endDate) {
                    $query->whereBetween('renewal_date', [
                        $startDate->copy()->addDay(),
                        $endDate->copy()->addDays(30) // 30-day renewal window
                    ]);
                })
                ->where('end_date', '>=', $startDate)
                ->where('end_date', '<=', $endDate)
                ->count();
        } else {
            $this->warn('Renewals relationship not found on Policy model. Skipping renewal rate calculation.');
        }
            
        return round(($renewedPolicies / $renewablePolicies) * 100, 2);
    }

    /**
     * Send agent-specific email report
     */
    private function sendAgentEmailReport($agent, $frequency, $claimsReport, $agentReport, $policies, $startDate, $endDate)
    {
        $subject = "Bima Connect " . ucfirst($frequency) . " Agent Report - " . now()->format('M j, Y');
        
        Mail::send('emails.agent-report', [
            'frequency' => $frequency,
            'agent' => $agent,
            'claimsReport' => $claimsReport,
            'agentReport' => $agentReport,
            'policies' => $policies,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPolicies' => $policies->count(),
            'totalPremium' => $policies->sum('gross_premium'),
            'totalCommission' => $policies->sum('commission'),
        ], function($message) use ($agent, $subject) {
            $message->to($agent->email, $agent->name)
                   ->subject($subject);
        });
    }
    
    /**
     * Send email with reports (for backward compatibility)
     */
    private function sendEmailReport($email, $frequency, $claimsReport, $agentReport, $policies, $startDate, $endDate)
    {
        $subject = "Bima Connect " . ucfirst($frequency) . " Policy Report - " . now()->format('M j, Y');
        
        Mail::send('emails.scheduled-reports', [
            'frequency' => $frequency,
            'claimsReport' => $claimsReport,
            'agentReport' => $agentReport,
            'policies' => $policies,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPolicies' => $policies->total(),
            'totalPremium' => $policies->sum('gross_premium'),
            'totalCommission' => $policies->sum('commission'),
        ], function($message) use ($email, $subject) {
            $message->to($email)
                   ->subject($subject);
        });
    }
}
