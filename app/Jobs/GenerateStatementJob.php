<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\StatementService;
use Illuminate\Support\Facades\Log;

class GenerateStatementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $customerCode;
    public $sendEmail;
    public $emailTo;
    public $requestedBy;

    // seconds
    public $timeout = 120;

    public function __construct(string $customerCode, bool $sendEmail = true, ?string $emailTo = null, $requestedBy = null)
    {
        $this->customerCode = $customerCode;
        $this->sendEmail = $sendEmail;
        $this->emailTo = $emailTo;
        $this->requestedBy = $requestedBy;
    }

    public function handle()
    {
        try {
            Log::info('GenerateStatementJob: started', ['customer' => $this->customerCode, 'sendEmail' => $this->sendEmail, 'email' => $this->emailTo]);

            $svc = new StatementService();

            $pdfPath = $svc->generatePdfForCustomer($this->customerCode);
            if (!$pdfPath) {
                Log::error('GenerateStatementJob: failed to generate PDF', ['customer' => $this->customerCode]);
                return;
            }

            $ok = $svc->attachPdfToCustomer($this->customerCode, $pdfPath, $this->sendEmail, $this->emailTo);
            if ($ok) {
                Log::info('GenerateStatementJob: finished successfully', ['customer' => $this->customerCode, 'path' => $pdfPath]);
            } else {
                Log::error('GenerateStatementJob: attach/send failed', ['customer' => $this->customerCode, 'path' => $pdfPath]);
            }
        } catch (\Throwable $ex) {
            Log::error('GenerateStatementJob: exception', ['error' => $ex->getMessage(), 'customer' => $this->customerCode]);
            // Let the job fail and be retried by the queue worker according to your queue config
            throw $ex;
        }
    }
}
