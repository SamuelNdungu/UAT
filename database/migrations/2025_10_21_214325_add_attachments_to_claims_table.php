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
        if (!Schema::hasColumn('claims', 'attachments')) {
            Schema::table('claims', function (Blueprint $table) {
                // Use jsonb for PostgreSQL to store the array of attachment metadata
                $table->jsonb('attachments')->nullable()->after('loss_details'); // Adjust 'after' as needed
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the column if it exists
        if (Schema::hasColumn('claims', 'attachments')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->dropColumn('attachments');
            });
        }
    }
};