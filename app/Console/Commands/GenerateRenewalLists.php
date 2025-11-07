<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RenewalListExport;
use Illuminate\Support\Facades\Log;

class GenerateRenewalLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewals:generate {--test-email= : Send test email to this address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and distribute monthly policy renewal lists to agents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly renewal list generation...');
        
        try {
            // Calculate date range for next month
            $nextMonth = now()->addMonth();
            $startDate = $nextMonth->copy()->startOfMonth();
            $endDate = $nextMonth->copy()->endOfMonth();

            $this->info("Looking for policies expiring between {$startDate->format('Y-m-d')} and {$endDate->format('Y-m-d')}");

            // Get all active agents with email addresses
            $agents = Agent::where('status', 'active')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            if ($agents->isEmpty()) {
                $this->warn('No active agents with email addresses found.');
                return 1;
            }

            $testEmail = $this->option('test-email');
            $totalEmailsSent = 0;

            foreach ($agents as $agent) {
                try {
                    $this->info("Processing renewal list for agent: {$agent->name} ({$agent->email})");

                    // Debug: Log the date range being used
                    $this->info("Searching for policies between {$startDate->format('Y-m-d')} and {$endDate->format('Y-m-d')}");
                    
                    // Get policies expiring next month for this agent
                    $policies = Policy::with(['customer', 'policyType', 'insurer'])
                        ->where('agent_id', $agent->id)
                        ->whereBetween('end_date', [$startDate, $endDate])
                        ->where(function($query) {
                            $query->where('status', 'active')
                                  ->orWhereNull('status')
                                  ->orWhere('status', '');
                        })
                        ->orderBy('end_date')
                        ->get();
                        
                    // Debug: Log the raw SQL query
                    $query = Policy::with(['customer', 'policyType', 'insurer'])
                        ->where('agent_id', $agent->id)
                        ->whereBetween('end_date', [$startDate, $endDate])
                        ->where(function($q) {
                            $q->where('status', 'active')
                              ->orWhereNull('status')
                              ->orWhere('status', '');
                        })
                        ->orderBy('end_date')
                        ->toSql();
                    $this->info("SQL Query: " . $query);
                    
                    // Debug: Check all policies for this agent regardless of date
                    $allPolicies = Policy::where('agent_id', $agent->id)->get();
                    $this->info("Total policies for agent: " . $allPolicies->count());
                    foreach ($allPolicies as $policy) {
                        $this->info(sprintf("Policy ID: %d, End Date: %s, Status: %s", 
                            $policy->id, 
                            $policy->end_date, 
                            $policy->status
                        ));
                    }

                    if ($policies->isEmpty()) {
                        $this->info("No expiring policies found for agent {$agent->name}.");
                        continue;
                    }

                    $this->info("Found {$policies->count()} policies expiring next month for agent {$agent->name}");

                    // Generate Excel file
                    $fileName = 'renewal-list-' . strtolower(str_replace(' ', '-', $agent->name)) . '-' . now()->format('Y-m-d') . '.xlsx';
                    $filePath = storage_path('app/renewals/' . $fileName);
                    
                    // Ensure directory exists
                    if (!file_exists(dirname($filePath))) {
                        mkdir(dirname($filePath), 0755, true);
                    }

                    // Export to Excel
                    $export = new RenewalListExport($policies);
                    Excel::store($export, 'renewals/' . $fileName);

                    // Send email with attachment
                    $email = $testEmail ?: $agent->email;
                    
                    Mail::send('emails.renewal-list', [
                        'agent' => $agent,
                        'policies' => $policies,
                        'month' => $nextMonth->format('F Y')
                    ], function($message) use ($email, $agent, $filePath, $fileName) {
                        $message->to($email, $agent->name)
                                ->subject('Renewal List for ' . now()->addMonth()->format('F Y'))
                                ->attach($filePath, [
                                    'as' => $fileName,
                                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                ]);
                    });

                    $this->info("Renewal list sent to {$email}");
                    $totalEmailsSent++;

                    // Clean up the file
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                } catch (\Exception $e) {
                    $this->error("Failed to process agent {$agent->name}: " . $e->getMessage());
                    Log::error("Failed to process renewal list for agent {$agent->id}: " . $e->getMessage());
                }
            }

            $this->info("\nCompleted! Sent {$totalEmailsSent} renewal lists successfully.");
            return 0;

        } catch (\Exception $e) {
            $this->error("Error generating renewal lists: " . $e->getMessage());
            Log::error("Error in GenerateRenewalLists command: " . $e->getMessage());
            return 1;
        }
    }
}
