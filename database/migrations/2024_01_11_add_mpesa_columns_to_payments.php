<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Make migration idempotent: only add columns if they don't already exist.
        if (Schema::hasTable('payments')) {
            if (! Schema::hasColumn('payments', 'phone_number')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('phone_number')->nullable()->after('payment_reference');
                });
            }

            if (! Schema::hasColumn('payments', 'merchant_request_id')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('merchant_request_id')->nullable()->after('phone_number');
                });
            }

            if (! Schema::hasColumn('payments', 'checkout_request_id')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('checkout_request_id')->nullable()->after('merchant_request_id');
                });
            }

            if (! Schema::hasColumn('payments', 'mpesa_receipt_number')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('mpesa_receipt_number')->nullable()->after('checkout_request_id');
                });
            }

            if (! Schema::hasColumn('payments', 'failure_reason')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('failure_reason')->nullable()->after('payment_status');
                });
            }

            if (! Schema::hasColumn('payments', 'transaction_date')) {
                Schema::table('payments', function (Blueprint $table) {
                    $table->timestamp('transaction_date')->nullable()->after('payment_date');
                });
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('payments', 'phone_number')) {
                    $drop[] = 'phone_number';
                }
                if (Schema::hasColumn('payments', 'merchant_request_id')) {
                    $drop[] = 'merchant_request_id';
                }
                if (Schema::hasColumn('payments', 'checkout_request_id')) {
                    $drop[] = 'checkout_request_id';
                }
                if (Schema::hasColumn('payments', 'mpesa_receipt_number')) {
                    $drop[] = 'mpesa_receipt_number';
                }
                if (Schema::hasColumn('payments', 'failure_reason')) {
                    $drop[] = 'failure_reason';
                }
                if (Schema::hasColumn('payments', 'transaction_date')) {
                    $drop[] = 'transaction_date';
                }

                if (! empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }
    }
};