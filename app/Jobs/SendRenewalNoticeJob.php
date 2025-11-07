<?php

namespace App\Jobs;

use App\Models\Policy;
use App\Mail\RenewalHtmlMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRenewalNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Policy $policy;

    /**
     * Create a new job instance.
     *
     * @param Policy $policy
     */
    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer = $this->policy->customer;

        if (!$customer || !$customer->email) {
            Log::warning('SendRenewalNoticeJob: Skipping policy due to missing customer or email.', [
                'policy_id' => $this->policy->id,
            ]);
            return;
        }

        try {
            Mail::to($customer->email)->send(new RenewalHtmlMail($customer, $this->policy));
            Log::info('SendRenewalNoticeJob: Successfully sent renewal notice.', [
                'policy_id' => $this->policy->id,
                'customer_id' => $customer->id,
                'recipient' => $customer->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendRenewalNoticeJob: Failed to send renewal notice.', [
                'policy_id' => $this->policy->id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception to allow the queue to handle retries/failures.
            throw $e;
        }
    }
}
