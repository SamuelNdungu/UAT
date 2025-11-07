<?php

namespace App\Console\Commands;

use App\Jobs\SendRenewalNoticeJob;
use App\Models\Policy;
use App\Mail\PolicyRenewalMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

// This entire file is an Artisan command. Think of it as a script you can run from the terminal.
// Laravel's scheduler (the "alarm clock" in app/Console/Kernel.php) is set up to run this script automatically every day.
class SendRenewalNotices extends Command
{
    /**
     * The signature now makes --days optional, for manual testing of a specific interval.
     * When run by the scheduler without options, it will process all required intervals.
     * @var string
     */
    protected $signature = 'app:send-renewal-notices {--days= : Comma-separated list of days before expiry to send notices (e.g., 15,30,60)}';

    /**
     * The console command description.
     *
     * A simple description that shows up when you run `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Finds policies expiring at key intervals and sends renewal notices.';

    /**
     * Execute the console command.
     *
     * This `handle` method is the main part of the script. When the scheduler runs this command,
     * all the code inside this method is executed.
     *
     * @return int
     */
    public function handle()
    {
        $intervals = $this->getIntervals();
        $this->info('Starting renewal notice process for intervals: ' . implode(', ', $intervals));
        Log::info('Starting renewal notice process', ['intervals' => $intervals]);

        $totalProcessed = 0;

        foreach ($intervals as $days) {
            $this->processInterval($days, $totalProcessed);
        }

        $this->info("\nRenewal notice process complete. Total notices processed: {$totalProcessed}");
        Log::info('Renewal notice process completed', ['total_processed' => $totalProcessed]);

        return 0;
    }

    protected function getIntervals()
    {
        $intervals = [];
        
        if ($this->option('days')) {
            $intervals = array_map('intval', explode(',', $this->option('days')));
            $this->info("Using custom intervals: " . implode(', ', $intervals));
        } else {
            $intervals = [60, 30, 15, 7, 1];
            $this->info("Using default intervals: " . implode(', ', $intervals));
        }

        return $intervals;
    }

    protected function processInterval($days, &$totalProcessed)
    {
        $this->line('');
        $this->info("Checking policies expiring in exactly {$days} days...");

        // Calculate the exact target expiry date.
        $targetDate = Carbon::today()->addDays($days);

        // Find policies expiring on that specific day.
        $policies = Policy::whereDate('end_date', $targetDate)->get();

        if ($policies->isEmpty()) {
            $this->line("No policies expiring on {$targetDate->toDateString()}.");
            return;
        }

        $this->line("Found {$policies->count()} policies. Filtering and dispatching jobs...");
        $bar = $this->output->createProgressBar($policies->count());
        $bar->start();

        $processedCount = 0;

        foreach ($policies as $policy) {
            // EXCLUSION RULE 1: Check if cancelled.
            if ($policy->isCancelled()) {
                Log::info('SendRenewalNotices: Skipped cancelled policy.', ['policy_id' => $policy->id]);
                $bar->advance();
                continue;
            }

            // EXCLUSION RULE 2: Check for total loss claim.
            if ($policy->hasTotalLossClaim()) {
                Log::info('SendRenewalNotices: Skipped policy with total loss claim.', ['policy_id' => $policy->id]);
                $bar->advance();
                continue;
            }

            try {
                // STATE TRACKING: Check if notice for this interval was already sent.
                if ($policy->hasBeenSentNoticeFor($days)) {
                    Log::debug('Skipping policy - notice already sent', [
                        'policy_id' => $policy->id,
                        'fileno' => $policy->fileno ?? 'N/A',
                        'days' => $days
                    ]);
                    $bar->advance();
                    continue;
                }

                // Send the renewal notice - try to get email from customers table first
                $email = null;
                
                // Check if customer_code exists and we can fetch from customers table
                if (!empty($policy->customer_code)) {
                    $customer = \DB::table('customers')
                        ->where('customer_code', $policy->customer_code)
                        ->select('email')
                        ->first();
                        
                    if ($customer && !empty($customer->email)) {
                        $email = $customer->email;
                    }
                }
                
                // Fallback to policy email if not found in customers table
                if (empty($email)) {
                    $email = $policy->email;
                }
                
                if (empty($email)) {
                    Log::warning('No email found for customer', [
                        'policy_id' => $policy->id,
                        'fileno' => $policy->fileno ?? 'N/A',
                        'customer_code' => $policy->customer_code ?? 'N/A',
                        'customer_name' => $policy->customer_name ?? 'N/A'
                    ]);
                    $bar->advance();
                    continue;
                }

                // Send email
                Mail::to($email)->send(new PolicyRenewalMail($policy, $days));
                
                // Mark as sent
                $policy->markNoticeSent($days);

                Log::info('Renewal notice sent', [
                    'policy_id' => $policy->id,
                    'fileno' => $policy->fileno ?? 'N/A',
                    'customer_name' => $policy->customer_name ?? 'N/A',
                    'customer_email' => $email,
                    'end_date' => $policy->end_date,
                    'days_until_expiry' => $days
                ]);

                $processedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to send renewal notice', [
                    'policy_id' => $policy->id,
                    'fileno' => $policy->fileno ?? 'N/A',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info("\nProcessed {$processedCount} policies expiring in {$days} days.");
        Log::info("Processed {$processedCount} policies for {$days}-day notice");
        
        $totalProcessed += $processedCount;
    }
}
