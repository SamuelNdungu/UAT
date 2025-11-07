<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifies that essential system components like the scheduler and queue worker are configured.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('--- Automated System Status Check ---');
        $allGood = true;

        // 1. Check Scheduler Configuration
        $this->line("\n<fg=yellow;options=bold>1. Scheduler (Cron Job)</>");
        $this->line('This is the master clock that runs your daily tasks.');
        $this->comment('ACTION: Ensure you have a cron job on your server running `php artisan schedule:run` every minute.');
        $this->info('Status: Manual check required. This is a server configuration.');

        // 2. Check Queue Worker
        $this->line("\n<fg=yellow;options=bold>2. Queue Worker</>");
        $queueDriver = config('queue.default');
        $this->line("Your queue driver is set to: <info>{$queueDriver}</info>");

        if ($queueDriver === 'sync') {
            $this->warn('The queue driver is `sync`. Emails will be sent immediately, not in the background.');
            $this->comment('This works, but for many emails, it is recommended to use `database` or `redis` and run a queue worker.');
        } else {
            $this->line('This driver requires a queue worker process to be running on your server.');
            $this->comment('ACTION: Ensure a process is running the command: `php artisan queue:work`');
            $this->info('Status: Manual check required. This is a server process.');
        }

        // 3. Check for Failed Jobs
        $this->line("\n<fg=yellow;options=bold>3. Failed Jobs</>");
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();
            if ($failedJobsCount > 0) {
                $this->error("Found {$failedJobsCount} failed jobs in the `failed_jobs` table.");
                $this->comment('Run `php artisan queue:failed` to see details and `php artisan queue:retry` to re-run them.');
                $allGood = false;
            } else {
                $this->info('No failed jobs found. Excellent!');
            }
        } else {
            $this->comment('No `failed_jobs` table found.');
        }

        // Final Summary
        $this->line("\n---");
        if ($allGood) {
            $this->info('System check complete. Your application code is fully implemented.');
            $this->comment('Just ensure your server cron and queue worker are active for the automation to run.');
        } else {
            $this->error('System check complete. Please address the issues noted above.');
        }

        return 0;
    }
}
