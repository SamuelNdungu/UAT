<?php

namespace App\Console\Commands;

use App\Models\Policy;
use App\Mail\PolicyRenewalMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestRenewalEmail extends Command
{
    protected $signature = 'renewal:test-email {fileno} {--days=7}';
    protected $description = 'Test sending a renewal email for a specific policy';

    public function handle()
    {
        $fileno = $this->argument('fileno');
        $days = (int)$this->option('days');

        $policy = Policy::where('fileno', $fileno)->first();

        if (!$policy) {
            $this->error("Policy with fileno {$fileno} not found");
            return 1;
        }

        if (!$policy->customer) {
            $this->error("No customer associated with policy {$fileno}");
            return 1;
        }

        $email = $policy->customer->email;

        if (!$email) {
            $this->error("No email found for customer with ID: {$policy->customer_id}");
            return 1;
        }

        $this->info("Sending test renewal notice to: {$email}");
        $this->info("Policy: {$policy->fileno}, Customer: {$policy->customer->name}");

        try {
            Mail::to($email)->send(new PolicyRenewalMail($policy, $days));

            $this->info("Test email sent successfully!");
            Log::info("Test renewal email sent", [
                'policy_id' => $policy->id,
                'fileno' => $policy->fileno,
                'customer_email' => $email,
                'days_until_expiry' => $days
            ]);

        } catch (\Exception $e) {
            $this->error("Error sending email: " . $e->getMessage());
            Log::error("Failed to send test renewal email", [
                'error' => $e->getMessage(),
                'policy_id' => $policy->id,
                'fileno' => $fileno,
                'email' => $email
            ]);
            return 1;
        }

        return 0;
    }
}
