<?php

namespace App\Services;

use App\Models\Policy;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RenewalNotice30;
use App\Mail\RenewalReminder15;
use App\Mail\ExpirationAlert;

class RenewalNotifier
{
    /**
     * Run all three notice jobs.
     */
    public function sendAllNotices(): void
    {
        $today = Carbon::today();

        $this->send30DayNotices($today);
        $this->send15DayReminders($today);
        $this->sendExpiryAlerts($today);
    }

    /**
     * First Notice (30 Days Out) - send to all customers regardless of status.
     */
    protected function send30DayNotices(Carbon $today): void
    {
        $target = $today->copy()->addDays(30)->toDateString();

        $policies = Policy::with('customer')
            ->whereDate('end_date', $target)
            ->get();

        foreach ($policies as $policy) {
            $this->sendIfHasEmail($policy, new RenewalNotice30($policy), '30-day notice');
        }

        Log::info('RenewalNotifier: 30-day notices processed', ['count' => $policies->count(), 'date' => $target]);
    }

    /**
     * Second Notice (15 Days Out) - only send if policy is still Active.
     */
    protected function send15DayReminders(Carbon $today): void
    {
        $target = $today->copy()->addDays(15)->toDateString();

        $policies = Policy::with('customer')
            ->whereDate('end_date', $target)
            ->get();

        $sent = 0;
        foreach ($policies as $policy) {
            if ($this->isActive($policy)) {
                $this->sendIfHasEmail($policy, new RenewalReminder15($policy), '15-day reminder');
                $sent++;
            }
        }

        Log::info('RenewalNotifier: 15-day reminders processed', ['found' => $policies->count(), 'sent' => $sent, 'date' => $target]);
    }

    /**
     * Final Notice (Day of Expiry) - send only if Active and not Renewed or Canceled.
     */
    protected function sendExpiryAlerts(Carbon $today): void
    {
        $target = $today->toDateString();

        $policies = Policy::with('customer')
            ->whereDate('end_date', $target)
            ->get();

        $sent = 0;
        foreach ($policies as $policy) {
            if ($this->isActive($policy) && ! $this->isRenewed($policy) && ! $this->isCanceled($policy)) {
                $this->sendIfHasEmail($policy, new ExpirationAlert($policy), 'expiry alert');
                $sent++;
            }
        }

        Log::info('RenewalNotifier: expiry alerts processed', ['found' => $policies->count(), 'sent' => $sent, 'date' => $target]);
    }

    /**
     * Helper to send mail if a customer email exists.
     */
    protected function sendIfHasEmail($policy, $mailable, string $type): void
    {
        try {
            $customer = $policy->customer ?? null;
            $email = $customer->email ?? $policy->customer_email ?? null;

            if (empty($email)) {
                Log::warning("RenewalNotifier: skipping {$type} for policy {$policy->id} (no email)");
                return;
            }

            Mail::to($email)->send($mailable);
            Log::info("RenewalNotifier: sent {$type} for policy {$policy->id} to {$email}");
        } catch (\Throwable $e) {
            Log::error("RenewalNotifier: failed to send {$type} for policy {$policy->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Determine if policy represents Active status.
     * Accepts integer or textual representations.
     */
    protected function isActive($policy): bool
    {
        $status = $this->normalizeStatus($policy->status);
        return $status === '1' || $status === 'active' || $status === 'activated' || $status === true;
    }

    protected function isRenewed($policy): bool
    {
        $status = $this->normalizeStatus($policy->status);
        return $status === 'renewed';
    }

    protected function isCanceled($policy): bool
    {
        $status = $this->normalizeStatus($policy->status);
        return in_array($status, ['canceled', 'cancelled', 'cancel'], true);
    }

    /**
     * Normalize the policy status to a string for comparisons.
     */
    protected function normalizeStatus($raw): string
    {
        if (is_null($raw)) return '';
        if (is_bool($raw)) return $raw ? '1' : '0';
        if (is_numeric($raw)) return (string) intval($raw);
        return strtolower(trim((string) $raw));
    }
}
