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
        // Only add the column if it does not already exist
        if (!Schema::hasColumn('mpesa_transactions', 'payment_id')) {
            Schema::table('mpesa_transactions', function (Blueprint $table) {
                $table->bigInteger('payment_id')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the column if it exists
        if (Schema::hasColumn('mpesa_transactions', 'payment_id')) {
            Schema::table('mpesa_transactions', function (Blueprint $table) {
                $table->dropColumn('payment_id');
            });
        }
    }
};
