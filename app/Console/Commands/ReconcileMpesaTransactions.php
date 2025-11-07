<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MpesaTransaction;
use App\Models\Receipt;

class ReconcileMpesaTransactions extends Command
{
    /**
     * The name and signature of the console command.
     * --force will actually persist matches; otherwise the command runs in dry-run mode.
     */
    protected $signature = 'mpesa:reconcile {--force : Persist matches instead of dry-run} {--limit=100 : Max transactions to process} {--days=1 : Lookback days for fuzzy matching}';

    /**
     * The console command description.
     */
    protected $description = 'Reconcile unprocessed MPESA transactions in bulk (dry-run by default).';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');
        $force = (bool) $this->option('force');

        $this->info("Starting MPESA reconciliation (limit={$limit}, days={$days}, force=" . ($force ? 'yes' : 'no') . ")");

        $txs = MpesaTransaction::whereNull('processed_at')->limit($limit)->get();
        $this->info('Found ' . $txs->count() . ' unprocessed transactions.');

        $processed = 0;
        $cutoffStart = now()->subDays($days)->startOfDay();
        $cutoffEnd = now()->endOfDay();

        foreach ($txs as $tx) {
            $this->line("Txn #{$tx->id} code={$tx->transaction_code} amount={$tx->amount} phone={$tx->phone_number}");

            $matched = false;

            // Exact match by receipt number
            if ($tx->transaction_code) {
                $receipt = Receipt::where('receipt_number', $tx->transaction_code)->first();
                if ($receipt) {
                    $this->line(" -> Would match receipt #{$receipt->id} (receipt_number={$receipt->receipt_number})");
                    if ($force) {
                        DB::transaction(function () use ($tx, $receipt) {
                            $tx->receipt_id = $receipt->id;
                            $tx->payment_id = $receipt->payment_id;
                            $tx->status = 'matched_receipt';
                            $tx->processed_at = now();
                            $tx->save();
                        });
                        $this->info(' -> Matched and saved.');
                    }
                    $processed++;
                    continue;
                }
            }

            // Fuzzy match by normalized phone + amount tolerance within window
            if ($tx->phone_number && $tx->amount) {
                $start = $cutoffStart;
                $end = $cutoffEnd;
                $possible = Receipt::whereBetween('receipt_date', [$start, $end])->with(['payment.customer'])->get();

                $normTxPhone = \App\Services\MpesaService::normalizePhone($tx->phone_number);
                foreach ($possible as $r) {
                    $payment = $r->payment;
                    if (!$payment) continue;
                    $custPhone = data_get($payment, 'customer.phone');
                    $normCustPhone = \App\Services\MpesaService::normalizePhone($custPhone);

                    $amountMatch = \App\Services\MpesaService::amountsAreClose($r->remaining_amount ?? $payment->payment_amount ?? 0, $tx->amount);
                    $phoneMatch = $normTxPhone && $normCustPhone && ($normTxPhone === $normCustPhone || strpos($normCustPhone, $normTxPhone) !== false || strpos($normTxPhone, $normCustPhone) !== false);

                    if ($amountMatch && $phoneMatch) {
                        $this->line(" -> Would fuzzy-match receipt #{$r->id} (payment_id={$r->payment_id})");
                        if ($force) {
                            DB::transaction(function () use ($tx, $r) {
                                $tx->receipt_id = $r->id;
                                $tx->payment_id = $r->payment_id;
                                $tx->status = 'matched_fuzzy';
                                $tx->processed_at = now();
                                $tx->save();
                            });
                            $this->info(' -> Matched fuzzy and saved.');
                        }
                        $processed++;
                        $matched = true;
                        break;
                    }
                }
            }

            if (! $matched) {
                $this->line(' -> No match found.');
            }
        }

        $this->info("Reconciliation complete. Potential matches processed: {$processed}");
        return 0;
    }
}
