<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Policy;

class RenewalNotice30 extends Mailable
{
    use Queueable, SerializesModels;

    public Policy $policy;

    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    public function build()
    {
        return $this->subject('Policy Renewal Notice — 30 days')
            ->view('emails.renewals.notice30')
            ->with(['policy' => $this->policy]);
    }
}
