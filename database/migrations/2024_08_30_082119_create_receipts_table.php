<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiptsTable extends Migration
{
    public function up()
    {
        // If the table does not exist, create it.
        if (! Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id')->nullable();
                $table->date('receipt_date');
                $table->string('receipt_number');
                $table->decimal('allocated_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                // Optionally add indexes/foreign keys:
                // $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            });
            return;
        }

        // If table exists, ensure expected columns are present (idempotent).
        if (Schema::hasTable('receipts')) {
            Schema::table('receipts', function (Blueprint $table) {
                if (! Schema::hasColumn('receipts', 'payment_id')) {
                    $table->unsignedBigInteger('payment_id')->nullable();
                }
                if (! Schema::hasColumn('receipts', 'receipt_date')) {
                    $table->date('receipt_date')->nullable();
                }
                if (! Schema::hasColumn('receipts', 'receipt_number')) {
                    $table->string('receipt_number')->nullable();
                }
                if (! Schema::hasColumn('receipts', 'allocated_amount')) {
                    $table->decimal('allocated_amount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('receipts', 'remaining_amount')) {
                    $table->decimal('remaining_amount', 15, 2)->default(0);
                }
                if (! Schema::hasColumn('receipts', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (! Schema::hasColumn('receipts', 'created_at') || ! Schema::hasColumn('receipts', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('receipts');
    }
}
