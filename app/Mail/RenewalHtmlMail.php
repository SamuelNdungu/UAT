<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Policy;
use App\Models\Customer;

class RenewalHtmlMail extends Mailable
{
    use Queueable, SerializesModels;

    public Policy $policy;
    public Customer $customer;

    /**
     * Create a new message instance.
     *
     * @param Customer $customer
     * @param Policy $policy
     */
    public function __construct(Customer $customer, Policy $policy)
    {
        $this->customer = $customer;
        $this->policy = $policy;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $companyName = config('app.name', 'Your Insurance Company');
        
        return $this->subject("Your Policy Renewal Notice ({$this->policy->policy_no})")
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->view('emails.renewal.notice'); // This line renders the email body from a view
    }
}
