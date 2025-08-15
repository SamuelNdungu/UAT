<?php

namespace App\Mail;

use App\Models\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RenewalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $policy;

    /**
     * Create a new message instance.
     *
     * @param Policy $policy
     */
    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Policy Renewal Reminder')
            ->markdown('emails.renewal-notification')
            ->with([
                'policyNumber' => $this->policy->policy_no,
                'customerName' => $this->policy->customer_name,
                'policyType' => $this->policy->policyType->type_name,
                'expiryDate' => $this->policy->end_date,
                'premium' => number_format($this->policy->premium, 2),
                'insurer' => $this->policy->insurer->name
            ]);
    }
}