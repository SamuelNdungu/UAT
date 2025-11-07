<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        // If the table does not exist, create it.
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->date('payment_date');
                $table->decimal('payment_amount', 15, 2);
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->string('payment_status')->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();

                // Add indexes/foreign keys if needed
                // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            });
            return;
        }

        // If the table already exists, ensure required columns are present.
        // This prevents duplicate-table errors and aligns schema with expected columns.
        $added = false;
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) use (&$added) {
                if (! Schema::hasColumn('payments', 'payment_date')) {
                    $table->date('payment_date')->nullable();
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'payment_amount')) {
                    $table->decimal('payment_amount', 15, 2)->nullable();
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'payment_reference')) {
                    $table->string('payment_reference')->nullable();
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'payment_status')) {
                    $table->string('payment_status')->default('pending');
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'notes')) {
                    $table->text('notes')->nullable();
                    $added = true;
                }
                if (! Schema::hasColumn('payments', 'created_at') || ! Schema::hasColumn('payments', 'updated_at')) {
                    $table->timestamps();
                    $added = true;
                }
            });
        }
    }

    public function down()
    {
        // Use dropIfExists to avoid errors during rollback if table was never created by this migration
        Schema::dropIfExists('payments');
    }
}
