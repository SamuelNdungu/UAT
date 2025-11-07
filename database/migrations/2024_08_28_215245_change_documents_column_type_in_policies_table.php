<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeDocumentsColumnTypeInPoliciesTable extends Migration
{
    public function up()
    {
        // Normalize existing values to valid JSON before changing column type.
        // This avoids Postgres "invalid input syntax for type json" when empty strings are present.
        DB::table('policies')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $doc = $row->documents;

                // Determine normalized JSON value
                if (is_null($doc) || (is_string($doc) && trim($doc) === '')) {
                    $normalized = json_encode([]);
                } elseif (is_string($doc)) {
                    $decoded = json_decode($doc, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // Keep valid JSON (re-encode to ensure consistent formatting)
                        $normalized = json_encode($decoded);
                    } else {
                        // Invalid JSON -> reset to empty array and log for later inspection
                        \Log::warning("Migration: resetting invalid JSON 'documents' for policy id {$row->id}");
                        $normalized = json_encode([]);
                    }
                } else {
                    // Non-string (already JSON-able), ensure it is JSON text
                    $normalized = json_encode($doc);
                }

                // Update only when needed
                if ((string)$doc !== (string)$normalized) {
                    DB::table('policies')->where('id', $row->id)->update(['documents' => $normalized]);
                }
            }
        });

        // Change column type to JSON / JSONB depending on DB driver
        $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Postgres: convert to jsonb using USING cast
            DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE jsonb USING documents::jsonb");
        } else {
            // For MySQL / others, use schema change to JSON if supported
            Schema::table('policies', function (Blueprint $table) {
                if (Schema::hasColumn('policies', 'documents')) {
                    $table->json('documents')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        // Revert column back to text for rollback.
        $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            // Cast jsonb back to text
            DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text USING documents::text");
        } else {
            Schema::table('policies', function (Blueprint $table) {
                if (Schema::hasColumn('policies', 'documents')) {
                    $table->text('documents')->nullable()->change();
                }
            });
        }
    }
}
