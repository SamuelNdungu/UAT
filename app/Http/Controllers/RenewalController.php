<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\Policy;
use App\Notifications\RenewalSmsNotification;

class RenewalController extends Controller
{
    protected function findPolicyForCustomer($id)
    {
        // Try to find a policy by policy id, lead_id or user_id - prefer soonest expiry
        return Policy::where('id', $id)
            ->orWhere('lead_id', $id)
            ->orWhere('user_id', $id)
            ->orderBy('end_date', 'asc')
            ->first();
    }

    public function sendEmail($id)
    {
        $policy = $this->findPolicyForCustomer($id);

        if (! $policy) {
            return redirect()->back()->with('error', 'No policy found for that customer.');
        }

        $to = $policy->email ?? ($policy->customer->email ?? null);

        if (! $to) {
            return redirect()->back()->with('error', 'Customer email not available.');
        }

        try {
            Mail::send('emails.renewal', ['policy' => $policy], function ($m) use ($policy, $to) {
                $m->to($to);
                $m->subject('Policy Renewal Notice - ' . ($policy->fileno ?? $policy->policy_no));
            });

            return redirect()->back()->with('success', 'Renewal email sent successfully.');
        } catch (\Exception $e) {
            Log::error('Renewal email error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to send renewal email.');
        }
    }

    public function sendSms($id)
    {
        $policy = $this->findPolicyForCustomer($id);

        if (! $policy) {
            return redirect()->back()->with('error', 'No policy found for that customer.');
        }

        $phone = $policy->phone ?? ($policy->customer->phone ?? null);

        if (! $phone) {
            return redirect()->back()->with('error', 'Customer phone number not available.');
        }

        try {
            $message = view('texts.renewal_sms', compact('policy'))->render();

            // If Nexmo/Vonage configured -> send via notification channel, else fallback to logging
            if (config('services.nexmo.key') || config('services.vonage.key')) {
                Notification::route('nexmo', $phone)
                    ->notify(new RenewalSmsNotification($message));
            } else {
                Log::info("SIMULATED SMS to {$phone}: " . strip_tags($message));
            }

            return redirect()->back()->with('success', 'Renewal SMS sent successfully.');
        } catch (\Exception $e) {
            Log::error('Renewal SMS error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to send renewal SMS.');
        }
    }

    public function renew($id)
    {
        $policy = Policy::findOrFail($id);
        if ($policy->isCancelled()) {
            return redirect()->route('policies.show', $policy->id)
                ->with('error', 'Canceled policies cannot be renewed.');
        }
        // ...existing renewal logic...
    }
}