<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Policy;
use Illuminate\Support\Facades\Log;

class DebugPolicy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:policy {fileno}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output basic info for a policy by fileno (for debugging)';

    public function handle()
    {
        $fileno = $this->argument('fileno');

        $p = Policy::where('fileno', $fileno)->with('customer')->first();
        if (! $p) {
            $this->error("Policy {$fileno} not found");
            return 1;
        }

        $this->info(sprintf('id:%s fileno:%s policy_id:%s', $p->id, $p->fileno, $p->id));
        $this->line('customer_code: ' . ($p->customer_code ?? '')); 
        $this->line('customer_email: ' . ($p->customer->email ?? '<no-email>'));
    $this->line('end_date: ' . ($p->end_date ?? ''));
    $this->line('is_canceled: ' . ($p->is_canceled ? 'true' : 'false'));
    $this->line('has_renewals: ' . ($p->renewalsAsOriginal()->exists() ? 'yes' : 'no'));
    $this->line('days_from_now: ' . \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($p->end_date), false));

        return 0;
    }
}
