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



Route::get('/', function () {
    return view('welcome');
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

  Route::get('export/pdf', [PolicyExportController::class, 'exportPdf'])->name('export.pdf');
  Route::get('export/excel', [PolicyExportController::class, 'exportExcel'])->name('export.excel');
    
  Route::get('customers/export/pdf', [CustomerController::class, 'exportPdf'])->name('customers.export.pdf');
  Route::get('customers/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export.excel');

  Route::get('payments/export/pdf', [PaymentsController::class, 'exportPdf'])->name('payments.export.pdf');
  Route::get('payments/export/excel', [PaymentsController::class, 'exportExcel'])->name('payments.export.excel');

  Route::get('payments/{id}/print-receipt', [PaymentsController::class, 'printReceipt'])->name('payments.printReceipt');

  Route::get('/collection/export/pdf', [CollectionController::class, 'exportPdf'])->name('collection.export.pdf');
  Route::get('/collection/export/excel', [CollectionController::class, 'exportExcel'])->name('collection.export.excel');
   

  Route::get('/policies/{id}/print-debit-note', [PolicyController::class, 'printDebitNote'])->name('policies.printDebitNote');
  Route::post('/fetch-data', [DashboardController::class, 'fetchData']);
  Route::post('/home', [HomeController::class, 'index'])->name('home.filter');

  Route::get('/performance', [PerformanceController::class, 'index'])->name('performance');

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
});
