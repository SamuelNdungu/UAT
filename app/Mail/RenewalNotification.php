<?php

namespace App\Mail;

use App\Models\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RenewalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $policy;

    /**
     * Create a new message instance.
     *
     * @return void
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
        return $this->subject('Policy Renewal Notification')
                    ->view('emails.renewal');
    }
}
