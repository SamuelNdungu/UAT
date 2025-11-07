<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\Auth\LoginController; 
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\RenewalsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PolicyExportController;
use App\Http\Controllers\MpesaPaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\CustomerStatementController;
use App\Http\Controllers\EndorsementController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return view('welcome');
});

// Graceful top-level GET redirect for /ai/ask to avoid MethodNotAllowed
Route::get('/ai/ask', function () {
  return redirect('/ai');
});

Auth::routes();
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Group routes that require authentication
Route::middleware(['auth'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Customer routes
    
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

    Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Lead routes
    Route::get('leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadsController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadsController::class, 'store'])->name('leads.store');
    Route::get('leads/{lead}', [LeadsController::class, 'show'])->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadsController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{lead}', [LeadsController::class, 'update'])->name('leads.update');
    Route::delete('leads/{lead}', [LeadsController::class, 'destroy'])->name('leads.destroy');
         
    //   policies routes 
    Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
    Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create'); // Correct method
    Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
    Route::get('/policies/{id}', [PolicyController::class, 'show'])->name('policies.show');
    Route::get('/policies/{id}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
    Route::put('/policies/{id}', [PolicyController::class, 'update'])->name('policies.update');
    Route::delete('/policies/{id}', [PolicyController::class, 'destroy'])->name('policies.destroy');

   
   // search-customer routes
   Route::get('/search', [PolicyController::class, 'search']);

   // get policy type       
   Route::get('/policies/create', [PolicyController::class, 'getCreatePolicyForm'])->name('policies.create');
    // Add this to your web.php routes file
    Route::get('/get-models/{make}', [PolicyController::class, 'getModels']);

    //   Renewals routes 
    Route::get('/renewals', [RenewalsController::class, 'index'])->name('renewals.index');
    Route::post('/renewals/{id}/store', [RenewalsController::class, 'store'])->name('renewals.store');
    Route::get('/renewals/{id}/renew', [RenewalsController::class, 'renew'])->name('renewals.renew');
    Route::put('/renewals/{id}', [RenewalsController::class, 'update'])->name('renewals.update');
    Route::get('/policies/search', [RenewalsController::class, 'search'])->name('policies.search');
    
    // Policy renewal history
    Route::get('/policies/{id}/history', [RenewalsController::class, 'history'])->name('policies.history');
    
     // Payments routes
    Route::get('/payments', [PaymentsController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
    Route::get('/payments/{id}/allocate', [PaymentsController::class, 'allocate'])->name('payments.allocate');
    Route::post('/payments/{id}/allocate', [PaymentsController::class, 'storeAllocation'])->name('payments.storeAllocation');
    Route::delete('/allocations/{allocation}', [PaymentsController::class, 'destroyAllocation'])->name('allocations.destroy');
    Route::delete('/allocations/unallocate-all/{payment}', [PaymentsController::class, 'unallocateAll'])->name('allocations.unallocateAll');
    Route::get('/search-policy', [PolicyController::class, 'search'])->name('search.policy');
    Route::get('/policy-details', [PolicyController::class, 'getPolicyDetails'])->name('get.policy.details');
    
    // collection routes 
    Route::get('/collection', [CollectionController::class, 'index'])->name('collection.index');
    // claims routes
    Route::post('/claims/store', [ClaimController::class, 'store'])->name('claims.store');
    Route::resource('claims', ClaimController::class); 
  // Secure attachment streaming for claims
  Route::get('/claims/{claim}/attachment/{idx}', [ClaimController::class, 'attachment'])->name('claims.attachment');
    Route::get('/api/search-policies', [ClaimController::class, 'searchPolicies']);
    Route::get('/api/get-policy-details', [ClaimController::class, 'getPolicyDetails']);

  // Report routes
  Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
  Route::get('/reports/generate', [ReportController::class, 'generateReport'])->name('reports.generate');
  Route::get('/reports/download/{id}', [ReportController::class, 'download'])->name('reports.download');
  Route::get('/reports/export/excel/{id}', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
  Route::get('/reports/export/pdf/{id}', [ReportController::class, 'exportPDF'])->name('reports.export.pdf');

  // Claims report routes
  Route::get('/reports/export/claims/excel', [ReportController::class, 'exportClaimsExcel'])->name('reports.export.claims.excel');
  Route::get('/reports/export/claims/pdf', [ReportController::class, 'exportClaimsPDF'])->name('reports.export.claims.pdf');

    //STK Push initiation
  Route::post('/payments/initiate-mpesa', [PaymentsController::class, 'initiateMpesaPayment'])->name('payments.initiate-mpesa');
  Route::post('/payments/mpesa-callback', [PaymentsController::class, 'handleMpesaCallback']);

  //new mpesa routes
  Route::post('/api/mpesa/initiate', [MpesaPaymentController::class, 'initiateMpesaPayment']);
//Route::post('/api/mpesa/callback', [MpesaPaymentController::class, 'handleMpesaCallback'])->name('mpesa.callback');
// Add this new route for MPESA callback
Route::post('/mpesa/callback', [MpesaPaymentController::class, 'handleMpesaCallback'])->name('mpesa.callback');
  
  //initiating payments
  //Route::post('/mpesa/pay', [MpesaPaymentController::class, 'initiateMpesaPayment'])->name('mpesa.pay');
  //Route::post('/mpesa/callback', [MpesaPaymentController::class, 'handleMpesaCallback'])->name('mpesa.callback');
  Route::get('/payments/create', [PaymentsController::class, 'create'])->name('payments.create');

  Route::post('/send-email/{customerId}', [NotificationController::class, 'sendEmail']);
  Route::post('/send-sms/{customerId}', [NotificationController::class, 'sendSMS']);

  Route::post('/customers/{id}/send-renewal-email', [NotificationController::class, 'sendRenewalEmail'])->name('customers.send.renewal.email');
  Route::post('/customers/{id}/send-renewal-sms', [NotificationController::class, 'sendRenewalSms'])->name('customers.send.renewal.sms');

  Route::get('customers/{id}/send-renewal-email', [RenewalController::class, 'sendEmail'])
    ->name('customers.sendRenewalEmail');

  Route::get('customers/{id}/send-renewal-sms', [RenewalController::class, 'sendSms'])
    ->name('customers.sendRenewalSms');

  Route::get('export/pdf', [PolicyExportController::class, 'exportPdf'])->name('export.pdf');
  Route::get('export/excel', [PolicyExportController::class, 'exportExcel'])->name('export.excel');
    
  Route::get('customers/export/pdf', [CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
  Route::get('customers/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export.excel');

  Route::get('payments/export/pdf', [PaymentsController::class, 'exportPdf'])->name('payments.export.pdf');
  Route::get('payments/export/excel', [PaymentsController::class, 'exportExcel'])->name('payments.export.excel');

  Route::get('payments/{id}/print-receipt', [PaymentsController::class, 'printReceipt'])->name('payments.printReceipt');

  // Debug route: show generated SQL for payments search (enabled only in debug)
  if (config('app.debug')) {
      Route::get('/debug/payments-search-sql', [PaymentsController::class, 'debugSearchSql'])->name('debug.payments.search.sql');
  }

  // MPESA transactions admin
  Route::get('/mpesa/transactions', [\App\Http\Controllers\MpesaTransactionController::class, 'index'])->name('mpesa.transactions.index');
  Route::get('/mpesa/transactions/{id}', [\App\Http\Controllers\MpesaTransactionController::class, 'show'])->name('mpesa.transactions.show');
  Route::post('/mpesa/transactions/{id}/apply', [\App\Http\Controllers\MpesaTransactionController::class, 'applyAllocation'])->name('mpesa.transactions.apply');

  Route::get('/collection/export/pdf', [CollectionController::class, 'exportPdf'])->name('collection.export.pdf');
  Route::get('/collection/export/excel', [CollectionController::class, 'exportExcel'])->name('collection.export.excel');
   

  Route::get('/policies/{id}/print-debit-note', [PolicyController::class, 'printDebitNote'])->name('policies.printDebitNote');
  Route::get('/policies/{id}/print-credit-note', [PolicyController::class, 'printCreditNote'])->name('policies.printCreditNote');
  Route::post('/fetch-data', [DashboardController::class, 'fetchData']);
  Route::post('/home', [HomeController::class, 'index'])->name('home.filter');

  Route::get('/performance', [PerformanceController::class, 'index'])->name('performance');

  // Dashboard routes
  Route::get('/dashboard/monthly-sales-commission', [DashboardController::class, 'monthlySalesCommission'])->name('dashboard.monthlySalesCommission');

  // Fees routes
  Route::get('/fees', [FeesController::class, 'index'])->name('fees.index');
  Route::get('/fees/create', [FeesController::class, 'create'])->name('fees.create');
  Route::post('/fees', [FeesController::class, 'store'])->name('fees.store');
  Route::get('/fees/{id}', [FeesController::class, 'show'])->name('fees.show');
  Route::get('/fees/{id}/edit', [FeesController::class, 'edit'])->name('fees.edit');
  Route::put('/fees/{id}', [FeesController::class, 'update'])->name('fees.update');
  Route::delete('/fees/{id}', [FeesController::class, 'destroy'])->name('fees.destroy');
  Route::get('/fees/{id}/print', [FeesController::class, 'print'])->name('fees.print');
  
  Route::get('/search', [FeesController::class, 'search'])->name('search.customers');

    // Settings route
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
  // Company Data (singleton) routes
  Route::get('/settings/company-data', [App\Http\Controllers\CompanyDataController::class, 'show'])->name('settings.company-data.show');
  Route::get('/settings/company-data/edit', [App\Http\Controllers\CompanyDataController::class, 'edit'])->name('settings.company-data.edit');
      Route::post('/fetch-data', [DashboardController::class, 'fetchData']);
  
    // AI assistant page (GET) - serves the UI
    Route::get('/ai', function () {
      return view('ai.ai');
    })->name('ai.index');

    // AI status endpoint used by the UI to check HTTP endpoint health
    Route::get('/ai/status', [AiController::class, 'status'])->name('ai.status');

    // Graceful GET handler for legacy or mistaken requests to /ai/ask
    // Redirects to the AI UI page instead of throwing MethodNotAllowed.
    Route::get('/ai/ask', function () {
      return redirect()->route('ai.index');
    });

    // AI assistant API endpoint (POST) - receives prompts
Route::post('/ai/ask', [AiController::class, 'generate'])->name('ai.ask');
    Route::post('/ai/stream', [AiController::class, 'stream']);


  Route::put('/settings/company-data', [App\Http\Controllers\CompanyDataController::class, 'update'])->name('settings.company-data.update');
  Route::resource('insurance_companies', App\Http\Controllers\InsuranceCompanyController::class);
  Route::resource('policy_types', App\Http\Controllers\PolicyTypeController::class);
  Route::resource('vehicle_types', App\Http\Controllers\VehicleTypeController::class);
  Route::resource('users', App\Http\Controllers\UserController::class);
    Route::resource('policies.endorsements', EndorsementController::class)->except(['edit', 'update', 'destroy']);
    // NEW: top-level endorsement print route so /endorsements/{id}/print and route('endorsements.print') work
    Route::get('/endorsements/{endorsement}/print', [EndorsementController::class, 'printNote'])->name('endorsements.print');

    // existing nested print route (keep for backward compatibility)
    Route::get('/policies/{policy}/endorsements/{endorsement}/print', [EndorsementController::class, 'printNote'])->name('policies.endorsements.print');
Route::get('policies/customer-agent', [PolicyController::class, 'getCustomerAgent'])->name('policies.customer.agent');

  });

Route::get('customers/{id}/statement', [CustomerStatementController::class, 'generate'])
    ->name('customers.statement');
    Route::get('/test-logo', function() {
    $company = \App\Models\CompanyData::first();
    return view('partials.company_logo', [
        'company' => $company,
        'as_data' => false, // Test web mode
        'max_width' => 200
    ]);
});

Route::get('/test-logo-pdf', function() {
    $company = \App\Models\CompanyData::first();
    return view('partials.company_logo', [
        'company' => $company,
        'as_data' => true, // Test PDF mode
        'max_width' => 200
    ]);
});

// NEW route: send customer statement via email
Route::get('customers/{customer}/email-statement', [CustomerController::class, 'emailStatement'])
    ->name('customers.emailStatement')
    ->middleware('auth'); // keep consistent with your other customer routes

// NEW: Download customer statement as PDF
Route::get('customers/{customer}/download-statement', [CustomerController::class, 'downloadStatement'])
    ->name('customers.downloadStatement')
    ->middleware('auth');


    // This is the new route for downloading documents
Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
     ->name('documents.download')
     ->middleware('auth'); // Protect the route
     // Document Routes (Used by customer.edit and customer.show)
Route::middleware(['auth'])->group(function () {
    // Route for downloading a document (used in customer.edit/show onclick)
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    
    // Route for deleting a document (used in the hidden form in customer.edit/show)
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});
// Temporary debug route - add to web.php
Route::get('/check-logo', function() {
    $company = \App\Models\CompanyData::first();
    
    if (!$company) return "No company data";
    
    return [
        'company_name' => $company->company_name,
        'logo_path_in_db' => $company->logo_path,
        'file_exists_in_storage' => \Storage::disk('public')->exists($company->logo_path) ? 'YES' : 'NO',
        'files_in_company_logos' => \Storage::disk('public')->files('company_logos'),
        'logo_url' => $company->getLogoUrl(),
        'direct_file_check' => file_exists(storage_path('app/public/' . $company->logo_path)) ? 'YES' : 'NO'
    ];
});

// Settings route
Route::middleware(['auth'])->prefix('settings')->name('settings.')->group(function () {
    // ...other settings routes...
    Route::resource('agents', \App\Http\Controllers\AgentController::class);
});

 

// Group for all reports
Route::group(['prefix' => 'reports', 'middleware' => ['auth']], function () {
    // Sales Report
    // =========================================================================
// REPORTS MODULE ROUTES
// Defines the named routes needed for the sidebar navigation links.
// =========================================================================
Route::middleware(['auth'])->prefix('reports')->group(function () {
    Route::get('sales', [ReportController::class, 'salesIndex'])->name('reports.sales');
    Route::get('debt-aging', [ReportController::class, 'debtAgingIndex'])->name('reports.debt_aging');
    Route::get('claims-analysis', [ReportController::class, 'claimsIndex'])->name('reports.claims');
    Route::get('renewals-tracking', [ReportController::class, 'renewalsIndex'])->name('reports.renewals');
    Route::get('commissions-payable', [ReportController::class, 'commissionsIndex'])->name('reports.commissions');
});
    Route::get('sales/export', [ReportController::class, 'exportSalesReport'])->name('reports.sales.export');
    
    // Add this route, making sure it uses the correct controller and method
Route::get('/reports/sales/export', [ReportController::class, 'salesExport'])->name('reports.sales.export');

 
});
Route::middleware(['auth'])->group(function () {
    // ... existing routes ...

    // SALES REPORTS
    Route::get('reports/sales', [ReportController::class, 'salesIndex'])->name('reports.sales.index');

    // EXPORT ROUTES
    // Ensure both Excel and PDF routes are defined with the correct names
    Route::get('reports/sales/export-excel', [ReportController::class, 'salesExport'])->name('reports.sales.export.excel');
    Route::get('reports/sales/export-pdf', [ReportController::class, 'salesPdfExport'])->name('reports.sales.export.pdf'); // <-- THIS WAS MISSING!
});

// Add route for printing/downloading invoice PDF from policy show page
Route::middleware(['auth'])->get('/policies/{policy}/invoice', [App\Http\Controllers\PolicyController::class, 'printInvoice'])->name('policies.printInvoice');

// Test email route (temporary - remove after testing)
Route::get('/test-email', function () {
    try {
        $email = 's2ndungu@gmail.com';
        \Illuminate\Support\Facades\Mail::send('emails.test', [], function($message) use ($email) {
            $message->to($email)
                   ->subject('Bima Connect - Test Email');
        });
        return "Test email sent to {$email}";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

// Renewal Notices Routes
Route::get('/renewals', [App\Http\Controllers\RenewalController::class, 'index'])->name('renewals.index');
Route::get('/renewals/export/excel', [App\Http\Controllers\RenewalController::class, 'exportExcel'])->name('renewals.export.excel');
Route::get('/renewals/export/pdf', [App\Http\Controllers\RenewalController::class, 'exportPdf'])->name('renewals.export.pdf');
Route::get('/renewals/{policy}/renew', [App\Http\Controllers\RenewalController::class, 'renew'])->name('renewals.renew'); // Assuming this route already exists