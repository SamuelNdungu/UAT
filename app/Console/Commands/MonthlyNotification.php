<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log; // Correctly import the Log facade

class MonthlyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monthly-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     */

    public function handle()
    {
        // Add your task logic here
        $this->info('Monthly task executed successfully!');

        // Example: Log to the system
        Log::info('Monthly task ran successfully on ' . now());
    }
}
