<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Policy;

class ExpirationAlert extends Mailable
{
    use Queueable, SerializesModels;

    public Policy $policy;

    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    public function build()
    {
        return $this->subject('Policy Expiration Alert — Today')
            ->view('emails.renewals.expiration_alert')
            ->with(['policy' => $this->policy]);
    }
}
