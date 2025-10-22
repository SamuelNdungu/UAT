<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            // Use jsonb for PostgreSQL to store the array of attachment metadata
            $table->jsonb('attachments')->nullable()->after('loss_details'); // Adjust 'after' as needed
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
    }
};