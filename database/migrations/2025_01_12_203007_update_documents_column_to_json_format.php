<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- 1. Data Migration: Convert string to valid JSONB array ---
        
        // Handle NULL values by setting them to an empty JSON array '[]'
        DB::statement("UPDATE policies SET documents = '[]' WHERE documents IS NULL");

        // The corrected PostgreSQL query to convert comma-separated string to JSONB array.
        DB::statement("
            UPDATE policies p
            SET documents = sub.new_documents::text
            FROM (
                SELECT
                    id,
                    jsonb_agg(jsonb_build_object('name', doc_name, 'description', NULL)) AS new_documents
                FROM
                    policies,
                    unnest(string_to_array(documents, ',')) AS doc_name
                WHERE
                    documents IS NOT NULL AND documents != '[]' AND (documents !~ '^\s*$')
                GROUP BY 1
            ) AS sub
            WHERE
                p.id = sub.id;
        ");
        
        // --- 2. Schema Change: Alter column type using explicit CAST ---
        // This is the CRITICAL change, fixing the 'Datatype mismatch' error.
        // It uses a raw statement to include "USING documents::jsonb".
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE jsonb USING documents::jsonb");
        
        // If you need it to be NOT NULL, you can add this:
        // DB::statement("ALTER TABLE policies ALTER COLUMN documents SET NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Alter the documents column type back to TEXT with explicit CAST
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text USING documents::text");

        // 2. Data Reversion: Convert JSONB array back to comma-separated string.
        DB::statement("
            UPDATE policies 
            SET documents = (
                SELECT string_agg(element->>'name', ',')
                FROM jsonb_array_elements(documents) AS element
            )
            WHERE documents IS NOT NULL AND documents != '[]'
        ");
        
        // 3. Revert empty arrays to NULL
        DB::statement("UPDATE policies SET documents = NULL WHERE documents = '[]'");
    }
};