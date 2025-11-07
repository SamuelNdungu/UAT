<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Make claim_id nullable since documents can now belong to other models
            $table->foreignId('claim_id')->nullable()->change();

            // Add polymorphic columns only if they do not already exist
            if (!Schema::hasColumn('documents', 'documentable_type')) {
                $table->string('documentable_type')->nullable()->after('claim_id');
            }
            if (!Schema::hasColumn('documents', 'documentable_id')) {
                $table->unsignedBigInteger('documentable_id')->nullable()->after('documentable_type');
            }

            // Add description column for document descriptions
            if (!Schema::hasColumn('documents', 'description')) {
                $table->text('description')->nullable()->after('documentable_id');
            }

            // Add index for polymorphic relationship only if columns were just added
            // (Postgres will error if index already exists, so check for columns)
            if (!Schema::hasColumn('documents', 'documentable_type') || !Schema::hasColumn('documents', 'documentable_id')) {
                $table->index(['documentable_type', 'documentable_id']);
            }
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop index if columns exist
            if (Schema::hasColumn('documents', 'documentable_type') && Schema::hasColumn('documents', 'documentable_id')) {
                $table->dropIndex(['documentable_type', 'documentable_id']);
            }
            if (Schema::hasColumn('documents', 'documentable_type')) {
                $table->dropColumn('documentable_type');
            }
            if (Schema::hasColumn('documents', 'documentable_id')) {
                $table->dropColumn('documentable_id');
            }
            if (Schema::hasColumn('documents', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};