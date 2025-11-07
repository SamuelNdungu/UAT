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
        // Only add columns if they do not already exist
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'documentable_id')) {
                $table->unsignedBigInteger('documentable_id')->nullable()->after('claim_id');
            }
            if (!Schema::hasColumn('documents', 'documentable_type')) {
                $table->string('documentable_type')->nullable()->after('documentable_id');
            }
            if (!Schema::hasColumn('documents', 'description')) {
                $table->string('description')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'documentable_id')) {
                $table->dropColumn('documentable_id');
            }
            if (Schema::hasColumn('documents', 'documentable_type')) {
                $table->dropColumn('documentable_type');
            }
            if (Schema::hasColumn('documents', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};