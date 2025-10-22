<?php

// INSIDE: 2025_01_12_210032_alter_policies_table_change_documents_to_json.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Conditional check: Only run data migration if the column is NOT YET jsonb.
        // This is a safety measure if the migration failed and partially ran before.
        $columnType = DB::getSchemaBuilder()->getColumnType('policies', 'documents');

        if ($columnType === 'text' || $columnType === 'varchar') {
            
            // Handle NULL values by setting them to an empty JSON array string
            DB::statement("UPDATE policies SET documents = '[]' WHERE documents IS NULL");

            // 2. Data Migration: Convert "doc1,doc2" string to JSONB array format
            // CRITICAL FIX: Add explicit CAST (documents::text) to avoid ERROR: function string_to_array(jsonb, unknown) does not exist
            DB::statement("
                UPDATE policies p
                SET documents = sub.new_documents::text
                FROM (
                    SELECT
                        id,
                        jsonb_agg(jsonb_build_object('name', TRIM(doc_name), 'description', NULL)) AS new_documents
                    FROM
                        policies,
                        -- Explicitly cast 'documents' to TEXT here
                        unnest(string_to_array(documents::text, ',')) AS doc_name
                    WHERE
                        documents IS NOT NULL AND documents != '[]' AND documents !~ '^\s*$'
                    GROUP BY 1
                ) AS sub
                WHERE
                    p.id = sub.id;
            ");
            
            // 3. Schema Change: Alter column type to JSONB using the required explicit cast.
            DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE jsonb USING documents::jsonb");
        }
        
        // If the column is already jsonb, this entire block is skipped, and the migration succeeds.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Data Reversion: Convert JSONB array back to comma-separated string.
        // Use an IF EXISTS check to ensure the column is actually jsonb before trying to revert data
        $columnType = DB::getSchemaBuilder()->getColumnType('policies', 'documents');

        if ($columnType === 'jsonb' || $columnType === 'json') {
            DB::statement("
                UPDATE policies p
                SET documents = sub.new_documents
                FROM (
                    SELECT
                        id,
                        string_agg(element->>'name', ',' ORDER BY element->>'name') AS new_documents
                    FROM
                        policies,
                        jsonb_array_elements(documents) AS element
                    WHERE
                        jsonb_typeof(documents) = 'array'
                    GROUP BY 1
                ) AS sub
                WHERE
                    p.id = sub.id;
            ");

            // 2. Schema Change: Alter column type back to TEXT.
            DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text USING documents::text");
            
            // 3. Revert empty strings/arrays to NULL
            DB::statement("UPDATE policies SET documents = NULL WHERE documents = ''");
        }
    }
};