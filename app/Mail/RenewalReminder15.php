<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Policy;

class RenewalReminder15 extends Mailable
{
    use Queueable, SerializesModels;

    public Policy $policy;

    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    public function build()
    {
        return $this->subject('Policy Renewal Reminder — 15 days')
            ->view('emails.renewals.reminder15')
            ->with(['policy' => $this->policy]);
    }
}
