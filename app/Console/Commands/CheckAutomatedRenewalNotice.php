<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CheckAutomatedRenewalNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renewal:check
                            {--to= : override recipient email for test send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for a well-implemented Automated Renewal Notice system.';

    public function handle()
    {
        $this->info('Starting Automated Renewal Notice implementation checks...');
        $this->comment('This command checks for common implementation patterns. Manual verification is still recommended.');

        // Section 1: Mail & Queue Config
        $this->section('1. Mailer & Queue Configuration', function () {
            $mailDriver = config('mail.default') ?? config('mail.driver') ?? 'n/a';
            $queueDriver = config('queue.default') ?? 'n/a';
            $from = config('mail.from.address') ?? 'n/a';
            $fromName = config('mail.from.name') ?? null;

            $this->line("Mail driver: <info>{$mailDriver}</info>");
            $this->line("Queue driver: <info>{$queueDriver}</info>");
            $this->line("From address: <info>{$from}" . ($fromName ? " ({$fromName})" : '') . "</info>");
            Log::info('renewal:check start', ['mail_driver' => $mailDriver, 'queue_driver' => $queueDriver, 'from' => $from]);
        });

        // Section 2: Job Queue Status
        $this->section('2. Job Queue Status', function () {
            if (Schema::hasTable('failed_jobs')) {
                $failed = DB::table('failed_jobs')->count();
                $this->line("Failed jobs count: " . ($failed > 0 ? "<error>{$failed}</error>" : "<info>{$failed}</info>"));
                Log::info('renewal:check failed_jobs', ['count' => $failed]);
            } else {
                $this->line('No failed_jobs table found');
            }

            if (Schema::hasTable('jobs')) {
                $pending = DB::table('jobs')->count();
                $this->line("Pending jobs in queue table: <info>{$pending}</info>");
                Log::info('renewal:check pending_jobs', ['count' => $pending]);
            } else {
                $this->line('No jobs table found (queues may be sync or use another driver)');
            }
        });

        // Section 3: Scheduled Tasks & Commands
        $this->section('3. Scheduled Tasks & Commands', function () {
            $this->line('Checking for renewal-related Artisan commands...');
            $allCommands = array_keys(Artisan::all());
            $renewalCommands = array_filter($allCommands, function ($command) {
                return str_contains($command, 'renewal') || str_contains($command, 'policy:expire') || str_contains($command, 'policies:check');
            });

            if (count($renewalCommands)) {
                $this->info('Found potential renewal commands:');
                foreach ($renewalCommands as $command) {
                    $this->line(" - {$command}");
                }
                $this->line('Please ensure one of these is scheduled in <comment>app/Console/Kernel.php</comment> and that your server cron is running <comment>`php artisan schedule:run`</comment> every minute.');
            } else {
                $this->warn('No Artisan commands found with "renewal" or "policy" in their signature. Automated notices are often triggered by a scheduled command.');
            }
        });

        // Section 4: Renewal Jobs
        $this->section('4. Renewal-related Jobs', function () {
            $this->line('Scanning app/Jobs for renewal-related job classes...');
            $jobPath = app_path('Jobs');
            $foundJobs = [];
            if (File::isDirectory($jobPath)) {
                $files = File::allFiles($jobPath);
                foreach ($files as $file) {
                    if (str_contains($file->getFilename(), 'Renewal')) {
                        $foundJobs[] = 'App\\Jobs\\' . str_replace('.php', '', $file->getFilename());
                    }
                }
            }

            if (count($foundJobs)) {
                $this->info('Found potential renewal jobs:');
                foreach ($foundJobs as $job) {
                    $this->line(" - {$job}");
                }
                $this->line('These jobs are likely dispatched by a scheduled command to send notices.');
            } else {
                $this->warn('No job classes found in app/Jobs with "Renewal" in the name.');
            }
        });

        // Section 5: Mailables & Notifications
        $mailableCandidates = [];
        $this->section('5. Mailables & Notifications', function () use (&$mailableCandidates) {
            $this->line('Looking for common renewal mailable / notification classes...');
            $candidates = [
                \App\Mail\AutomatedRenewalNotice::class,
                \App\Mail\RenewalNoticeMail::class,
                \App\Mail\RenewalReminderMail::class,
                \App\Notifications\AutomatedRenewalNotice::class,
                \App\Notifications\RenewalNotice::class,
                \App\Notifications\RenewalReminder::class,
            ];

            foreach ($candidates as $class) {
                if (class_exists($class)) {
                    $mailableCandidates[] = $class;
                }
            }

            if (count($mailableCandidates)) {
                $this->info('Detected renewal-related classes:');
                foreach ($mailableCandidates as $f) {
                    $this->line(" - {$f}");
                }
                Log::info('renewal:check detected_classes', ['classes' => $mailableCandidates]);
            } else {
                $this->warn('No common renewal mailable/notification classes detected.');
                Log::warning('renewal:check no common classes found');
            }
        });

        // Section 6: Live Test Send
        $this->section('6. Live Test Send', function () use ($mailableCandidates) {
            $from = config('mail.from.address') ?? 'n/a';
            $fromName = config('mail.from.name') ?? null;
            $testRecipient = $this->option('to') ?? config('mail.test_recipient') ?? $from;
            $this->line("Test recipient: <info>{$testRecipient}</info>");

            if ($testRecipient === 'n/a' || !$testRecipient) {
                $this->error('Cannot run test send: no recipient specified. Use --to=email@example.com or set a MAIL_FROM_ADDRESS.');
                return;
            }

            // Attempt a simple test send (raw mail) to confirm mailer works
            try {
                Mail::raw("Automated Renewal Notice - test message sent at " . Carbon::now()->toDateTimeString(), function ($msg) use ($testRecipient, $from, $fromName) {
                    $msg->to($testRecipient);
                    if ($from && $from !== 'n/a') {
                        $msg->from($from, $fromName ?? null);
                    }
                    $msg->subject('Automated Renewal Notice - TEST');
                });
                $this->info("Sent raw test email to {$testRecipient} (check inbox/spam).");
                Log::info('renewal:check raw_test_sent', ['to' => $testRecipient]);
            } catch (\Throwable $e) {
                $this->error('Failed to send raw test email: ' . $e->getMessage());
                Log::error('renewal:check raw_test_failed', ['error' => $e->getMessage()]);
            }

            // If a renewal mailable class exists, attempt to instantiate and send
            if (count($mailableCandidates) && Schema::hasTable((new Customer)->getTable())) {
                $customer = Customer::whereNotNull('email')->where('email', '!=', '')->first();
                if (!$customer) {
                    $this->warn('No customer with an email found to test renewal mailable.');
                    return;
                }

                $this->line("Using customer id={$customer->id} to test detected mailable(s).");
                $policy = null;
                if (class_exists(\App\Models\Policy::class) && method_exists($customer, 'policies')) {
                    $policy = $customer->policies()->first();
                    if ($policy) {
                        $this->line("Found policy id={$policy->id} for customer.");
                    }
                }

                foreach ($mailableCandidates as $class) {
                    try {
                        $instance = null;
                        // Heuristics: try constructors with policy, then without, then empty
                        if ($policy) {
                            try { $instance = new $class($customer, $policy); } catch (\Throwable $_) {}
                        }
                        if (!$instance) {
                            try { $instance = new $class($customer); } catch (\Throwable $_) {}
                        }
                        if (!$instance) {
                            try { $instance = new $class(); } catch (\Throwable $_) {}
                        }

                        if (!$instance) {
                            $this->line("Could not auto-instantiate <comment>{$class}</comment> (constructor mismatch). Skipped.");
                            Log::warning('renewal:check instantiate_failed', ['class' => $class]);
                            continue;
                        }

                        // Try to send synchronously
                        Mail::to($testRecipient)->send($instance);
                        $this->info("Successfully sent <comment>{$class}</comment> instance to {$testRecipient}.");
                        Log::info('renewal:check mailable_sent', ['class' => $class, 'to' => $testRecipient, 'customer_id' => $customer->id]);
                    } catch (\Throwable $e) {
                        $this->error("Sending {$class} failed: " . $e->getMessage());
                        Log::error('renewal:check mailable_send_failed', ['class' => $class, 'error' => $e->getMessage()]);
                    }
                }
            }
        });

        $this->info("\nAutomated Renewal Notice checks completed.");
        $this->comment("To run a live simulation, use the `php artisan renewal:simulate` command.");
        return 0;
    }

    /**
     * Helper to format output sections.
     *
     * @param string $title
     * @param \Closure $callback
     * @return void
     */
    protected function section(string $title, \Closure $callback)
    {
        $this->line('');
        $this->getOutput()->writeln("<fg=yellow;options=bold>-- {$title} --</>");
        $callback();
    }
}
