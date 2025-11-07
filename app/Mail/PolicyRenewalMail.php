<?php

namespace App\Mail;

use App\Models\Policy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PolicyRenewalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $policy;
    public $days;

    public function __construct(Policy $policy, $days)
    {
        $this->policy = $policy;
        $this->days = $days;
    }

    public function build()
    {
        $subject = "Policy Renewal Reminder - " . ($this->policy->fileno ?? 'N/A');
        
        // Add policy type and reg no if it's a motor policy
        if (stripos($this->policy->bus_type ?? '', 'motor') !== false) {
            $subject .= " - " . ($this->policy->bus_type ?? 'Motor');
            if (!empty($this->policy->reg_no)) {
                $subject .= " (Reg: " . $this->policy->reg_no . ")";
            }
        }
        
        $subject .= " - Expires in {$this->days} days";
        
        return $this->subject($subject)
                   ->view('emails.policy-renewal')
                   ->with([
                       'policy' => $this->policy,
                       'days' => $this->days
                   ]);
    }
}
