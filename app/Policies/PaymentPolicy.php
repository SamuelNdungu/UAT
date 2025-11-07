<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can allocate the payment.
     */
    public function allocate(?User $user, Payment $payment)
    {
        // Temporary permissive rule: allow authenticated users to allocate
        return (bool) $user;
    }

    /**
     * Determine whether the user can unallocate the payment.
     */
    public function unallocate(?User $user, Payment $payment)
    {
        // Temporary permissive rule: allow authenticated users to unallocate
        return (bool) $user;
    }

    /**
     * Determine whether the user can print receipts for the payment.
     */
    public function print(?User $user, Payment $payment)
    {
        // Allow authenticated users to print receipts
        return (bool) $user;
    }
}
