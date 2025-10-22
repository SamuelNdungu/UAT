<?php
namespace App\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\CompanyData;
use App\Models\Policy;
use App\Models\Customer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Mail\StatementMail;
use App\Mail\StatementHtmlMail;
use App\Mail\RenewalHtmlMail;
use App\Models\RenewalNotice;

class StatementService
{
    /**
     * Generate a branded statement PDF for a customer code.
     * If $policies is null it will query policies for the customer.
     * Returns the storage path (relative) on success, or null on failure.
     */
    public function generatePdfForCustomer(string $customerCode, ?array $policies = null): ?string
    {
        try {
            $company = CompanyData::first();
            $customer = Customer::where('customer_code', $customerCode)->first();

            if (!$customer) {
                Log::error('StatementService: customer not found', ['customer_code' => $customerCode]);
                return null;
            }

            if ($policies === null) {
                $policies = Policy::where('customer_code', $customerCode)->orderBy('start_date', 'desc')->get()->map(function($p){
                    return [
                        'file_number' => $p->fileno,
                        'policy_no' => $p->policy_no ?? $p->policy_no,
                        'coverage' => $p->coverage,
                        'start_date' => $p->start_date,
                        'end_date' => $p->end_date,
                        'premium' => $p->premium,
                        'paid_amount' => $p->paid_amount ?? 0,
                        'outstanding' => $p->outstanding_amount ?? $p->balance ?? 0,
                        'status' => $p->status,
                    ];
                })->toArray();
            }

            $data = [
                'company' => $company,
                // allow view to use company->logo_path directly; remote images will be allowed by Dompdf
                'company_logo_local' => null,
                'customer' => $customer,
                'policies' => $policies,
                'generated_at' => now()->toDateTimeString(),
            ];
            // Render Blade HTML
            $html = View::make('statements.statement', $data)->render();

            // Dompdf options (allow remote images to support company logo URLs)
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isFontSubsettingEnabled', true);
            $options->set('dpi', 96);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'statements/' . $customerCode . '-statement-' . date('Y-m-d_His') . '.pdf';
            $output = $dompdf->output();

            // Ensure directory
            Storage::disk('public')->put($filename, $output);

            return 'storage/' . $filename;
        } catch (\Throwable $ex) {
            Log::error('StatementService: failed to generate PDF', ['error' => $ex->getMessage()]);
            return null;
        }
    }

    /**
     * Attach a generated PDF path to the customer's documents JSON array and optionally send email.
     * $pdfPublicPath is the 'storage/...' path returned by generatePdfForCustomer
     */
    public function attachPdfToCustomer(string $customerCode, string $pdfPublicPath, bool $sendEmail = false, string $emailTo = null): bool
    {
        try {
            $customer = Customer::where('customer_code', $customerCode)->first();
            if (!$customer) return false;

            // Normalize and append to documents field (store as JSON array of {name,path})
            $existing = [];
            if (!empty($customer->documents)) {
                $existing = json_decode($customer->documents, true) ?: [];
            }

            $basename = basename($pdfPublicPath);
            $entry = ['name' => $basename, 'path' => $pdfPublicPath, 'uploaded_at' => now()->toDateTimeString()];
            $existing[] = $entry;

            $customer->documents = json_encode($existing);
            $customer->save();

            if ($sendEmail && $emailTo) {
                $subject = 'Statement of Account — ' . ($customer->corporate_name ?? $customer->customer_code);
                $body = "Hello,\n\nPlease find attached the Statement of Account for " . ($customer->corporate_name ?? $customer->customer_code) . ".\n\nRegards.";

                // For backward compatibility keep PDF attach behavior
                $m = new StatementMail($subject, $body);
                $m->attach(public_path($pdfPublicPath));
                Log::info('StatementService: attempting to send statement email (with PDF)', ['to' => $emailTo, 'attachment' => $pdfPublicPath]);
                try {
                    Mail::to($emailTo)->send($m);
                    Log::info('StatementService: statement email sent (with PDF)', ['to' => $emailTo, 'attachment' => $pdfPublicPath]);
                } catch (\Throwable $ex) {
                    Log::error('StatementService: failed to send statement email (with PDF)', ['to' => $emailTo, 'error' => $ex->getMessage()]);
                    throw $ex;
                }
            }

            return true;
        } catch (\Throwable $ex) {
            Log::error('StatementService.attachPdfToCustomer failed', ['error' => $ex->getMessage()]);
            return false;
        }
    }

    /**
     * Render the statement HTML and send it as an HTML email (no PDF generated).
     */
    public function sendStatementHtml(string $customerCode, string $emailTo, ?array $policies = null, ?string $startDate = null, ?string $endDate = null): bool
    {
        try {
            $company = CompanyData::first();
            $customer = Customer::where('customer_code', $customerCode)->first();
            if (!$customer) {
                Log::error('StatementService.sendStatementHtml: customer not found', ['customer' => $customerCode]);
                return false;
            }

            if ($policies === null) {
                $policies = Policy::where('customer_code', $customerCode)->orderBy('start_date', 'desc')->get()->map(function($p){
                    return [
                        'file_number' => $p->fileno,
                        'policy_no' => $p->policy_no ?? $p->policy_no,
                        'coverage' => $p->coverage,
                        'start_date' => $p->start_date,
                        'end_date' => $p->end_date,
                        'premium' => $p->premium,
                        'paid_amount' => $p->paid_amount ?? 0,
                        'outstanding' => $p->outstanding_amount ?? $p->balance ?? 0,
                        'status' => $p->status,
                    ];
                })->toArray();
            }

            // Build transactions same way as the PDF view expects
            $formatDate = function($d) {
                if (empty($d)) return '';
                try { return \Carbon\Carbon::parse($d)->toDateString(); } catch (\Throwable $ex) { return preg_replace('/[ T].*/', '', $d); }
            };

            $running = 0;
            $transactions = collect($policies)->map(function($p) use (&$running, $formatDate) {
                $debit = 0; $credit = 0;
                if (!empty($p['premium'])) $debit = (float) str_replace(',', '', $p['premium']);
                if (!empty($p['paid_amount'])) $credit = (float) str_replace(',', '', $p['paid_amount']);
                $running += ($debit - $credit);
                return (object)[
                    'date_formatted' => $formatDate($p['start_date'] ?? ($p['end_date'] ?? '')),
                    'description' => $p['coverage'] ?? 'Policy',
                    'policy_no' => $p['policy_no'] ?? ($p['fileno'] ?? ''),
                    'debit' => $debit > 0 ? $debit : null,
                    'credit' => $credit > 0 ? $credit : null,
                    'running' => $running,
                ];
            });

            $data = [
                'company' => $company,
                'customer' => $customer,
                'transactions' => $transactions,
                'generatedAt' => now()->toDateTimeString(),
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];

            $html = view('emails.statement_html', $data)->render();

            $subject = 'Statement of Account — ' . ($customer->corporate_name ?? $customer->customer_code);
            Log::info('StatementService: attempting to send statement HTML email', ['to' => $emailTo]);
            Mail::to($emailTo)->send(new StatementHtmlMail($subject, $html));
            Log::info('StatementService: statement HTML email sent', ['to' => $emailTo]);

            return true;
        } catch (\Throwable $ex) {
            Log::error('StatementService.sendStatementHtml failed', ['error' => $ex->getMessage(), 'customer' => $customerCode]);
            return false;
        }
    }

    /**
     * Send a policy renewal notice email listing upcoming renewals for a customer.
     */
    public function sendRenewalNotice(string $customerCode, string $emailTo, ?array $policyIds = null): bool
    {
        try {
            $customer = Customer::where('customer_code', $customerCode)->first();
            if (!$customer) {
                Log::error('StatementService.sendRenewalNotice: customer not found', ['customer' => $customerCode]);
                return false;
            }

            // Load policies due for renewal; if policyIds provided, filter those
            $query = Policy::where('customer_code', $customerCode)->orderBy('end_date', 'asc');
            if (is_array($policyIds) && count($policyIds) > 0) {
                $query->whereIn('id', $policyIds);
            } else {
                // default: policies expiring within next 60 days
                $query->whereBetween('end_date', [now()->toDateString(), now()->addDays(60)->toDateString()]);
            }

            $policies = $query->get(['fileno', 'policy_no', 'coverage', 'start_date', 'end_date'])->map(function($p){
                return [
                    'fileno' => $p->fileno,
                    'policy_no' => $p->policy_no,
                    'coverage' => $p->coverage,
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                ];
            })->toArray();

            $data = [
                'customer' => $customer,
                'policies' => $policies,
                'generatedAt' => now()->toDateTimeString(),
            ];

            $html = view('emails.renewal_notice', $data)->render();
            $subject = 'Policy Renewal Notice';

            Log::info('StatementService: attempting to send renewal notice', ['to' => $emailTo, 'customer' => $customerCode]);
            Mail::to($emailTo)->send(new RenewalHtmlMail($subject, $html));
            Log::info('StatementService: renewal notice sent', ['to' => $emailTo, 'customer' => $customerCode]);

            // Record renewal notice to prevent duplicates and for auditing
            try {
                if (!empty($policies)) {
                    foreach ($policies as $p) {
                        RenewalNotice::create([
                            'fileno' => $p['fileno'] ?? null,
                            'policy_id' => null,
                            'customer_code' => $customerCode,
                            'channel' => 'email',
                            'sent_at' => now(),
                            'sent_by' => auth()->id() ?? null,
                            'message_id' => null,
                            'meta' => ['subject' => $subject],
                        ]);
                    }
                } elseif (is_array($policyIds) && count($policyIds) > 0) {
                    // Try to resolve filenos for the provided policy IDs
                    $found = Policy::whereIn('id', $policyIds)->get(['id','fileno']);
                    if ($found->count() > 0) {
                        foreach ($found as $fp) {
                            RenewalNotice::create([
                                'fileno' => $fp->fileno ?? null,
                                'policy_id' => $fp->id,
                                'customer_code' => $customerCode,
                                'channel' => 'email',
                                'sent_at' => now(),
                                'sent_by' => auth()->id() ?? null,
                                'message_id' => null,
                                'meta' => ['subject' => $subject],
                            ]);
                        }
                    } else {
                        // fallback: create a single batch notice with policy ids in meta
                        RenewalNotice::create([
                            'fileno' => null,
                            'policy_id' => null,
                            'customer_code' => $customerCode,
                            'channel' => 'email',
                            'sent_at' => now(),
                            'sent_by' => auth()->id() ?? null,
                            'message_id' => null,
                            'meta' => ['subject' => $subject, 'policy_ids' => $policyIds],
                        ]);
                    }
                } else {
                    // no specific policies; create a generic notice indicating a send for the customer
                    RenewalNotice::create([
                        'fileno' => null,
                        'policy_id' => null,
                        'customer_code' => $customerCode,
                        'channel' => 'email',
                        'sent_at' => now(),
                        'sent_by' => auth()->id() ?? null,
                        'message_id' => null,
                        'meta' => ['subject' => $subject],
                    ]);
                }
            } catch (\Throwable $ex) {
                Log::error('StatementService: failed to record renewal notice', ['error' => $ex->getMessage()]);
            }

            return true;
        } catch (\Throwable $ex) {
            Log::error('StatementService.sendRenewalNotice failed', ['error' => $ex->getMessage(), 'customer' => $customerCode]);
            return false;
        }
    }
}
