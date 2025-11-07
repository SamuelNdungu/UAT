<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\MpesaTransaction;

class MpesaCallbackTest extends TestCase
{

    public function test_mpesa_callback_stores_transaction_and_attempts_reconciliation()
    {
        // Create minimal schema for customers, payments and receipts to avoid depending on full migrations
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('customer_code')->unique()->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('phone')->nullable();
                $table->string('corporate_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('customer_code')->nullable();
                $table->date('payment_date')->nullable();
                $table->decimal('payment_amount', 13, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->string('phone_number')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id');
                $table->date('receipt_date')->nullable();
                $table->string('receipt_number')->nullable();
                $table->decimal('allocated_amount', 13, 2)->default(0);
                $table->decimal('remaining_amount', 13, 2)->default(0);
                $table->timestamps();
            });
        }

        // Ensure clean tables: truncate if they already exist
        foreach (['mpesa_transactions', 'receipts', 'payments', 'customers'] as $tbl) {
            if (Schema::hasTable($tbl)) {
                DB::table($tbl)->truncate();
            }
        }

        // Create a customer and payment with receipt
        // Disable middleware so the callback route can be tested without auth
        $this->withoutMiddleware();

        // Ensure mpesa_transactions table exists in test DB
        if (!Schema::hasTable('mpesa_transactions')) {
            Schema::create('mpesa_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->nullable();
                $table->string('transaction_code')->nullable()->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->unsignedBigInteger('receipt_id')->nullable()->index();
                $table->decimal('amount', 13, 2)->nullable();
                $table->string('phone_number')->nullable();
                $table->string('status')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
        $customerCode = 'CUST' . rand(1000, 9999);
        $customer = Customer::create([
            'customer_code' => $customerCode,
            'phone' => '254712345678',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $payment = Payment::create([
            'customer_code' => $customerCode,
            'payment_date' => now()->toDateString(),
            'payment_amount' => 1000.00,
            'payment_method' => 'MPESA',
            'phone_number' => '254712345678',
        ]);

        $receipt = Receipt::create([
            'payment_id' => $payment->id,
            'receipt_date' => now()->toDateString(),
            'receipt_number' => 'RCPT1001',
            'allocated_amount' => 0,
            'remaining_amount' => 1000.00,
        ]);

        $callbackPayload = [
            'Body' => [
                'stkCallback' => [
                    'ResultCode' => 0,
                    'CheckoutRequestID' => 'RCPT1001',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 1000.00],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'RCPT1001'],
                            ['Name' => 'PhoneNumber', 'Value' => '254712345678'],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/payments/mpesa-callback', $callbackPayload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('mpesa_transactions', [
            'transaction_code' => 'RCPT1001',
            'amount' => 1000.00,
            'phone_number' => '254712345678',
        ]);

        $mpesa = MpesaTransaction::where('transaction_code', 'RCPT1001')->first();
        $this->assertNotNull($mpesa);
        $this->assertEquals('matched_receipt', $mpesa->status);
        $this->assertNotNull($mpesa->processed_at);
        $this->assertEquals($receipt->id, $mpesa->receipt_id);
        $this->assertEquals($payment->id, $mpesa->payment_id);
    }
}
