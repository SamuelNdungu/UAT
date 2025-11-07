<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Customer;
use App\Models\Policy; // Assuming this model exists
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class SimulateRenewalNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewal:simulate 
                            {--policy= : Simulate for a specific policy ID} 
                            {--days=30 : Find policies expiring in the next X days} 
                            {--to= : Redirect all emails to this address for testing} 
                            {--dry-run : Log actions without sending emails}
                            {--mailable= : The FQCN of the Mailable class to use (e.g., "App\Mail\MyRenewalMail")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulates the automated renewal notice process for expiring policies.';

    public function handle()
    {
        $this->info('Starting renewal notice simulation...');

        $policyId = $this->option('policy');
        $days = (int) $this->option('days');
        $recipientOverride = $this->option('to');
        $isDryRun = $this->option('dry-run');
        $mailableClass = $this->option('mailable');

        if ($isDryRun) {
            $this->warn('*** DRY RUN MODE *** No emails will be sent.');
        }

        // Find the renewal mailable/notification class
        $renewalClass = $this->findAndValidateRenewalClass($mailableClass);
        if (!$renewalClass) {
            $this->error('Could not find or validate a Renewal Mailable class to send.');
            
            // If a specific (wrong) class was given, show suggestions.
            if ($mailableClass) {
                $this->suggestMailableCandidates();
            }

            $this->comment('Please specify a valid mailable class with the --mailable option, or ensure one exists with "Renewal" in its name for auto-detection.');
            $this->line('Example: <info>php artisan renewal:simulate --mailable="App\Mail\RenewalNotification"</info>');
            return 1;
        }
        $this->info("Using class: <comment>{$renewalClass}</comment>");

        // Get policies to notify
        $policies = $this->getPolicies($policyId, $days);
        if ($policies->isEmpty()) {
            $this->info('No policies found matching the criteria.');
            return 0;
        }

        $this->info("Found <comment>{$policies->count()}</comment> policies to process.");

        $sentCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($policies as $policy) {
            $customer = $policy->customer; // Assuming 'customer' relation exists on Policy model
            if (!$customer) {
                $this->warn("Policy ID {$policy->id} has no associated customer. Skipping.");
                $skippedCount++;
                continue;
            }

            $recipient = $recipientOverride ?? $customer->email;
            if (empty($recipient)) {
                $this->warn("Customer ID {$customer->id} has no email address. Skipping.");
                $skippedCount++;
                continue;
            }

            $this->line("Processing Policy ID: <info>{$policy->id}</info> for Customer: <info>{$customer->id} ({$customer->email})</info>");

            if ($isDryRun) {
                $this->line(" -> [DRY RUN] Would send renewal notice to <comment>{$recipient}</comment>.");
                $sentCount++;
                Log::info('renewal:simulate [DRY RUN]', [
                    'class' => $renewalClass,
                    'policy_id' => $policy->id,
                    'customer_id' => $customer->id,
                    'recipient' => $recipient,
                ]);
            } else {
                try {
                    $instance = $this->createMailableInstance($renewalClass, $customer, $policy);
                    if (!$instance) {
                        $this->error(" -> Failed to instantiate {$renewalClass}. Check its constructor.");
                        $failedCount++;
                        continue;
                    }

                    $sentMessage = Mail::to($recipient)->send($instance);
                    $messageId = method_exists($sentMessage, 'getMessageId') ? $sentMessage->getMessageId() : 'n/a';

                    $this->info(" -> Successfully sent renewal notice to <comment>{$recipient}</comment> (Message-ID: {$messageId}).");
                    $sentCount++;
                    Log::info('renewal:simulate [SENT]', [
                        'class' => $renewalClass,
                        'policy_id' => $policy->id,
                        'customer_id' => $customer->id,
                        'recipient' => $recipient,
                        'message_id' => $messageId,
                    ]);
                } catch (\Throwable $e) {
                    $this->error(" -> Failed to send email: " . $e->getMessage());
                    $failedCount++;
                    Log::error('renewal:simulate [FAILED]', [
                        'class' => $renewalClass,
                        'policy_id' => $policy->id,
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info('Simulation complete.');
        
        $this->line('');
        $this->getOutput()->writeln("<fg=yellow;options=bold>-- Simulation Summary --</>");
        $this->line("Policies Found: <info>{$policies->count()}</info>");
        $this->line("Notices Sent (or would be sent): <info>{$sentCount}</info>");
        $this->line("Skipped (no customer/email): <comment>{$skippedCount}</comment>");
        if ($failedCount > 0) {
            $this->line("Failures: <error>{$failedCount}</error>");
        } else {
            $this->line("Failures: <info>{$failedCount}</info>");
        }

        if ($isDryRun) {
            $this->line('');
            $this->comment('Dry run successful. To perform a live test, run the command without --dry-run.');
            $this->comment('It is recommended to redirect the test email to yourself using the --to option:');
            $this->line('<info>php artisan renewal:simulate --to=youremail@example.com</info>');
        }

        return 0;
    }

    /**
     * Find policies that are due for renewal.
     */
    private function getPolicies($policyId, $days)
    {
        if (!class_exists(\App\Models\Policy::class)) {
            $this->error('`App\Models\Policy` model not found. Cannot query for policies.');
            return collect();
        }

        if ($policyId) {
            return \App\Models\Policy::where('id', $policyId)->get();
        }

        $this->comment("Finding policies expiring between now and {$days} days from now...");
        
        // Use the new scope from the Policy model
        return \App\Models\Policy::expiringSoon($days)->get();
    }

    /**
     * Find the first available renewal mailable class, or validate the specified one.
     */
    private function findAndValidateRenewalClass($specifiedClass)
    {
        if ($specifiedClass) {
            if (class_exists($specifiedClass)) {
                return $specifiedClass;
            }
            $this->warn("The specified mailable class '{$specifiedClass}' does not exist.");
            return null;
        }

        // If no class is specified, try to find one automatically.
        $candidates = $this->findMailableCandidates();
        if (!empty($candidates)) {
            $this->comment("No mailable specified. Auto-selecting first candidate found: " . $candidates[0]);
            return $candidates[0]; // Use the first one found
        }

        return null;
    }

    /**
     * Create an instance of the mailable using common constructor patterns.
     */
    private function createMailableInstance(string $class, Customer $customer, Policy $policy)
    {
        try {
            // Try constructor with (customer, policy)
            return new $class($customer, $policy);
        } catch (\Throwable $_) {
            try {
                // Try constructor with (customer)
                return new $class($customer);
            } catch (\Throwable $_) {
                try {
                    // Try no-arg constructor
                    return new $class();
                } catch (\Throwable $_) {
                    return null;
                }
            }
        }
    }

    /**
     * Scans the app/Mail directory and suggests possible mailable classes.
     */
    private function suggestMailableCandidates()
    {
        $suggestions = $this->findMailableCandidates();

        if (!empty($suggestions)) {
            $this->line('');
            $this->warn('However, we found some possible candidates in your App\\Mail directory:');
            foreach ($suggestions as $suggestion) {
                $this->line(" - <comment>{$suggestion}</comment>");
            }
        }
    }

    /**
     * Scans the app/Mail directory and returns an array of possible mailable classes.
     */
    private function findMailableCandidates()
    {
        $mailPath = app_path('Mail');
        $suggestions = [];

        if (File::isDirectory($mailPath)) {
            $files = File::allFiles($mailPath);
            foreach ($files as $file) {
                $className = 'App\\Mail\\' . str_replace('.php', '', $file->getFilename());
                if (class_exists($className)) {
                    // Suggest any class with "Renewal" or "Policy" in its name
                    if (stripos($className, 'Renewal') !== false || stripos($className, 'Policy') !== false) {
                        $suggestions[] = $className;
                    }
                }
            }
        }
        return $suggestions;
    }
}
