<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only create the table if it does not already exist
        if (!Schema::hasTable('mpesa_transactions')) {
            Schema::create('mpesa_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('provider')->nullable();
                $table->string('transaction_code')->nullable()->index();
                $table->decimal('amount', 13, 2)->nullable();
                $table->string('phone_number')->nullable();
                $table->string('status')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the table if it exists
        if (Schema::hasTable('mpesa_transactions')) {
            Schema::dropIfExists('mpesa_transactions');
        }
    }
};
