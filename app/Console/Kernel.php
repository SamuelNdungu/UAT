<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ReconcileMpesaTransactions;
use App\Console\Commands\SendRenewalNotices;
use App\Console\Commands\DebugPolicy;
use Illuminate\Console\Scheduling\Schedule; // added import

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ReconcileMpesaTransactions::class,
        SendRenewalNotices::class,
        DebugPolicy::class,
        \App\Console\Commands\GenerateScheduledReports::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Dry-run reconciliation: list potential matches daily at 02:00 (no --force)
        $schedule->command('mpesa:reconcile --limit=200 --days=1')->dailyAt('02:00');

        // Schedule the renewal notices command to run daily at midnight (00:00)
        $schedule->command('app:send-renewal-notices --days=60,30,15,7,1')
                 ->dailyAt('00:00')
                 ->timezone('Africa/Nairobi') // Set to your timezone
                 ->appendOutputTo(storage_path('logs/renewal-notices.log'))
                 ->after(function () {
                     Log::info('Scheduled renewal notices completed at ' . now());
                 });

        // Schedule weekly reports to be sent every Monday at 9:00 AM
        $schedule->command('reports:generate --frequency=weekly')
                 ->weekly()
                 ->mondays()
                 ->at('09:00');

        // Schedule monthly reports to be sent on the 1st of each month at 10:00 AM
        $schedule->command('reports:generate --frequency=monthly')
                 ->monthlyOn(1, '10:00');

        // Schedule renewal list generation on the 28th of each month at 10:00 AM
        $schedule->command('renewals:generate')
                 ->monthlyOn(28, '10:00')
                 ->timezone('Africa/Nairobi')
                 ->emailOutputOnFailure(env('ADMIN_EMAIL', 'admin@example.com'));

        // If you prefer hourly dry-run instead, uncomment the line below
        // $schedule->command('mpesa:reconcile --limit=200 --days=1')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
