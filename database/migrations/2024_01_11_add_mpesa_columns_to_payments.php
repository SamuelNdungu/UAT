<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add MPESA specific columns
            $table->string('phone_number')->nullable()->after('payment_reference');
            $table->string('merchant_request_id')->nullable()->after('phone_number');
            $table->string('checkout_request_id')->nullable()->after('merchant_request_id');
            $table->string('mpesa_receipt_number')->nullable()->after('checkout_request_id');
            $table->string('failure_reason')->nullable()->after('payment_status');
            $table->timestamp('transaction_date')->nullable()->after('payment_date');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'merchant_request_id',
                'checkout_request_id',
                'mpesa_receipt_number',
                'failure_reason',
                'transaction_date'
            ]);
        });
    }
};