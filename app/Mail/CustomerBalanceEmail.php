<?php 

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerBalanceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName; // To hold the customer's name
    public $balances;     // To hold the balance information

    /**
     * Create a new message instance.
     *
     * @param string $customerName
     * @param array $balances
     */
    public function __construct($customerName, $balances)
    {
        $this->customerName = $customerName;
        $this->balances = $balances;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Statement of Account')
                    ->view('emails.customer_balance'); // Your email view
    }
}
