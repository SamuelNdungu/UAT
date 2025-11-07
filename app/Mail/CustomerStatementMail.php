<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;

class CustomerStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public Customer $customer;
    public $pdfData;
    public $transactions;
    // renamed to avoid conflict with Mailable::$from
    public $fromDate;
    public $toDate;

    /**
     * Create a new message instance.
     *
     * @param Customer $customer
     * @param string|null $pdfData Raw PDF binary (optional)
     * @param \Illuminate\Support\Collection|array|null $transactions Optional transactions collection/array
     * @param string|null $fromDate Optional from date (Y-m-d or DateTime)
     * @param string|null $toDate Optional to date (Y-m-d or DateTime)
     */
    public function __construct(Customer $customer, $pdfData = null, $transactions = null, $fromDate = null, $toDate = null)
    {
        $this->customer = $customer;
        $this->pdfData = $pdfData;
        $this->transactions = $transactions;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function build()
    {
        // Log start of build so you can confirm mail is being composed
        Log::info('CustomerStatementMail: build() started', ['customer_id' => $this->customer->id ?? null]);

        $company = config('app.name', 'Company');
        $filename = 'Statement_' . ($this->customer->customer_code ?? $this->customer->id) . '.pdf';

        // Determine transactions: use provided, otherwise load from relation or fallback
        $transactions = $this->transactions;
        if ($transactions === null) {
            try {
                // determine date range first (use renamed properties)
                if ($this->fromDate || $this->toDate) {
                    $from = $this->fromDate ? Carbon::parse($this->fromDate) : Carbon::now()->subDays(30);
                    $to = $this->toDate ? Carbon::parse($this->toDate) : Carbon::now();
                } else {
                    $from = Carbon::now()->subDays(30);
                    $to = Carbon::now();
                }

                // Try multiple relation names commonly used in apps
                $relationCandidates = [
                    'transactions',
                    'policyTransactions',
                    'policy_transactions',
                    'payments',
                    'paymentsReceived',
                    'customerTransactions',
                ];

                $queried = false;
                foreach ($relationCandidates as $rel) {
                    if (method_exists($this->customer, $rel)) {
                        $query = $this->customer->{$rel}();
                        Log::info('CustomerStatementMail: loading transactions via relation', ['relation' => $rel, 'customer_id' => $this->customer->id ?? null]);
                        $queried = true;
                        break;
                    }
                }

                // If no relation found, try Transaction model or DB table
                if (! $queried) {
                    if (class_exists(Transaction::class)) {
                        $query = Transaction::where('customer_id', $this->customer->id);
                        Log::info('CustomerStatementMail: loading transactions via Transaction model', ['customer_id' => $this->customer->id ?? null]);
                        $queried = true;
                    } elseif (Schema::hasTable('transactions')) {
                        $query = DB::table('transactions')->where('customer_id', $this->customer->id);
                        Log::info('CustomerStatementMail: loading transactions via DB transactions table', ['customer_id' => $this->customer->id ?? null]);
                        $queried = true;
                    }
                }

                if (! $queried) {
                    // nothing we can query; return empty collection
                    Log::warning('CustomerStatementMail: no transaction relation/model/table found', ['customer_id' => $this->customer->id ?? null]);
                    $transactions = collect();
                } else {
                    // Try common date column names until one works
                    $dateColumns = ['date', 'transaction_date', 'trans_date', 'created_at', 'posted_at'];
                    $applied = false;
                    foreach ($dateColumns as $col) {
                        try {
                            // attempt whereBetween — both Eloquent and Query Builder support it
                            $results = $query->whereBetween($col, [$from->startOfDay(), $to->endOfDay()])->get();
                            // normalize to collection
                            if (! $results instanceof \Illuminate\Support\Collection) {
                                $results = collect($results);
                            }
                            $transactions = $results;
                            Log::info('CustomerStatementMail: applied date filter', ['date_column' => $col, 'count' => $transactions->count(), 'customer_id' => $this->customer->id ?? null]);
                            $applied = true;
                            break;
                        } catch (\Throwable $e) {
                            // try next candidate column
                            continue;
                        }
                    }
                    if (! $applied) {
                        // fallback: try to get all transactions for customer (no date filter)
                        try {
                            $results = $query->get();
                            if (! $results instanceof \Illuminate\Support\Collection) {
                                $results = collect($results);
                            }
                            $transactions = $results;
                            Log::info('CustomerStatementMail: fallback to unfiltered transactions', ['count' => $transactions->count(), 'customer_id' => $this->customer->id ?? null]);
                        } catch (\Throwable $e) {
                            Log::error('CustomerStatementMail: failed retrieving transactions', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id ?? null]);
                            $transactions = collect();
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error('CustomerStatementMail: failed loading transactions', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id ?? null]);
                $transactions = collect();
            }
        } else {
            // normalize if caller passed an array
            if (is_array($transactions)) {
                $transactions = collect($transactions);
            }
        }

        // Prepare date strings expected by the Blade (startDate / endDate) and generatedAt
        // Use the renamed properties
        $startDate = $this->fromDate ? Carbon::parse($this->fromDate)->format('Y-m-d') : Carbon::now()->subDays(30)->format('Y-m-d');
        $endDate = $this->toDate ? Carbon::parse($this->toDate)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $generatedAt = Carbon::now()->format('Y-m-d H:i');

        Log::info('CustomerStatementMail: transactions loaded', ['count' => $transactions->count(), 'customer_id' => $this->customer->id ?? null]);

        // After loading transactions, normalize and compute running balances
        $transactions = $this->normalizeTransactions($transactions);

        // If still empty, attempt to call common helper methods on Customer (fallback)
        if ($transactions->isEmpty()) {
            $customerFallbackMethods = ['getStatementTransactions', 'statementTransactions', 'generateStatementTransactions'];
            foreach ($customerFallbackMethods as $m) {
                if (method_exists($this->customer, $m)) {
                    try {
                        Log::info('CustomerStatementMail: attempting customer fallback method', ['method' => $m, 'customer_id' => $this->customer->id ?? null]);
                        $fallback = $this->customer->{$m}($this->fromDate ?? null, $this->toDate ?? null);
                        if (is_array($fallback)) {
                            $fallback = collect($fallback);
                        }
                        $fallback = $this->normalizeTransactions($fallback);
                        if ($fallback->isNotEmpty()) {
                            $transactions = $fallback;
                            Log::info('CustomerStatementMail: fallback method returned transactions', ['method' => $m, 'count' => $transactions->count(), 'customer_id' => $this->customer->id ?? null]);
                            break;
                        }
                    } catch (\Throwable $e) {
                        Log::warning('CustomerStatementMail: fallback method threw', ['method' => $m, 'error' => $e->getMessage(), 'customer_id' => $this->customer->id ?? null]);
                        continue;
                    }
                }
            }
        }

        // Log if still empty so you can compare with manual download
        if ($transactions->isEmpty()) {
            Log::warning('CustomerStatementMail: no transactions found after all fallbacks', ['customer_id' => $this->customer->id ?? null, 'from' => $this->fromDate ?? null, 'to' => $this->toDate ?? null]);
        }

        // If a PDF binary was not provided, try to generate one here using the correct view (customers.statement)
        $pdfData = $this->pdfData;
        if (empty($pdfData)) {
            $viewData = [
                'customer' => $this->customer,
                'transactions' => $transactions,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'generatedAt' => $generatedAt,
            ];

            try {
                if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customers.statement', $viewData);
                    $pdfData = $pdf->output();
                    Log::info('CustomerStatementMail: PDF generated using Barryvdh\DomPDF', ['customer_id' => $this->customer->id ?? null]);
                } elseif (class_exists('PDF')) {
                    $pdf = \PDF::loadView('customers.statement', $viewData);
                    $pdfData = $pdf->output();
                    Log::info('CustomerStatementMail: PDF generated using PDF alias', ['customer_id' => $this->customer->id ?? null]);
                } else {
                    // Fallback: render the view HTML and attach as HTML file
                    $html = view('customers.statement', $viewData)->render();
                    Log::warning('CustomerStatementMail: PDF generator not available, attaching HTML fallback', ['customer_id' => $this->customer->id ?? null]);

                    return $this->from(config('mail.from.address'), config('mail.from.name'))
                                ->subject("Your Customer Statement from {$company}")
                                ->view('emails.customer-statement-body', ['customer' => $this->customer, 'transactions' => $transactions, 'startDate' => $startDate, 'endDate' => $endDate])
                                ->attachData($html, str_replace('.pdf', '.html', $filename), [
                                    'mime' => 'text/html',
                                ]);
                }
            } catch (\Throwable $e) {
                // Log exception and attach a minimal HTML fallback so the mail still goes out and you can diagnose
                Log::error('CustomerStatementMail: PDF generation failed', ['error' => $e->getMessage(), 'customer_id' => $this->customer->id ?? null]);
                $html = view('customers.statement', $viewData)->render();

                return $this->from(config('mail.from.address'), config('mail.from.name'))
                            ->subject("Your Customer Statement from {$company} (PDF generation error)")
                            ->view('emails.customer-statement-body', ['customer' => $this->customer, 'transactions' => $transactions, 'startDate' => $startDate, 'endDate' => $endDate, 'generatedAt' => $generatedAt])
                            ->attachData($html, str_replace('.pdf', '.html', $filename), [
                                'mime' => 'text/html',
                            ]);
            }
        }

        // Build the email, pass transactions and date info into the body view, and attach the PDF
        Log::info('CustomerStatementMail: attaching PDF and finishing build', ['customer_id' => $this->customer->id ?? null, 'pdf_size' => is_string($pdfData) ? strlen($pdfData) : null]);

        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject("Your Customer Statement from {$company}")
                    ->view('emails.customer-statement-body', ['customer' => $this->customer, 'transactions' => $transactions, 'startDate' => $startDate, 'endDate' => $endDate, 'generatedAt' => $generatedAt])
                    ->attachData($pdfData, $filename, [
                        'mime' => 'application/pdf',
                    ]);
    }

    // New helper: normalize and compute fields the Blade expects
    private function normalizeTransactions($transactions)
    {
        if (is_null($transactions)) {
            return collect();
        }

        if (is_array($transactions)) {
            $transactions = collect($transactions);
        }

        // If it's an Eloquent Collection or generic collection, convert models/stdClass to arrays for safe access
        $transactions = $transactions->map(function ($t) {
            // Convert Eloquent model or stdClass to array-like object
            if (is_array($t)) {
                $rec = (object) $t;
            } elseif ($t instanceof \Illuminate\Database\Eloquent\Model) {
                $rec = (object) $t->toArray();
            } else {
                $rec = $t;
            }

            // Determine date value from common fields
            $dateFields = ['date', 'transaction_date', 'trans_date', 'created_at', 'posted_at'];
            $dateVal = null;
            foreach ($dateFields as $f) {
                if (isset($rec->{$f}) && !empty($rec->{$f})) {
                    $dateVal = $rec->{$f};
                    break;
                }
            }

            // Fallback: if no date, use now (ensures ordering)
            try {
                $dateParsed = $dateVal ? Carbon::parse($dateVal) : Carbon::now();
            } catch (\Throwable $e) {
                $dateParsed = Carbon::now();
            }

            // Format expected by the Blade (dd-mm-YYYY)
            $date_formatted = $dateParsed->format('d-m-Y');

            // Ensure numeric debit/credit fields
            $debit = floatval($rec->debit ?? ($rec->amount_debit ?? 0));
            $credit = floatval($rec->credit ?? ($rec->amount_credit ?? 0));

            // Description and policy_no fallbacks
            $description = $rec->description ?? $rec->details ?? '';
            $policy_no = $rec->policy_no ?? $rec->policy_number ?? ($rec->policy ?? '');

            return (object) [
                'raw' => $rec,
                'date' => $dateParsed,
                'date_formatted' => $date_formatted,
                'description' => $description,
                'policy_no' => $policy_no,
                'debit' => $debit,
                'credit' => $credit,
                'running' => 0, // will compute below
            ];
        });

        // Sort by date ascending so running balance is correct
        $transactions = $transactions->sortBy(function ($t) {
            return $t->date->getTimestamp();
        })->values();

        // Compute running balance (assumes debit increases outstanding, credit decreases)
        $running = 0.0;
        $transactions = $transactions->map(function ($t) use (&$running) {
            $running += ($t->debit - $t->credit);
            $t->running = $running;
            // Keep date_formatted as string for the blade
            $t->date_formatted = is_string($t->date_formatted) ? $t->date_formatted : $t->date->format('d-m-Y');
            return $t;
        });

        return $transactions;
    }
}
