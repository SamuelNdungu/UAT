<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterPoliciesTableChangeDocumentsToJson extends Migration
{
    public function up()
    {
        // Ensure the documents column is of text type before altering
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text");

        // Create a temporary column to store the JSON data
        DB::statement("ALTER TABLE policies ADD COLUMN documents_temp json");

        // Convert existing data to JSON format
        DB::statement("UPDATE policies SET documents_temp = '[]' WHERE documents IS NULL");
        DB::statement("
    UPDATE policies 
    SET documents_temp = json_agg(json_build_object('name', trim(value), 'description', NULL)) 
    FROM jsonb_array_elements(
        text_to_jsonb('[' || replace(documents, ',', '","') || ']')::jsonb
    ) AS element(value) 
    WHERE documents IS NOT NULL AND documents != '[]'
");

        // Drop the original documents column
        DB::statement("ALTER TABLE policies DROP COLUMN documents");

        // Rename the temporary column to documents
        DB::statement("ALTER TABLE policies ALTER COLUMN documents_temp RENAME TO documents");
    }

    public function down()
    {
        // Revert the documents column to text type if needed
        DB::statement("ALTER TABLE policies ADD COLUMN documents_temp text");

        // Convert existing JSON data back to text format
        DB::statement("UPDATE policies SET documents_temp = (SELECT string_agg(name, ',') FROM jsonb_array_elements(documents) AS element(name text, description text)) WHERE documents IS NOT NULL AND documents != '[]'");
        DB::statement("UPDATE policies SET documents_temp = NULL WHERE documents = '[]'");

        // Drop the original documents column
        DB::statement("ALTER TABLE policies DROP COLUMN documents");

        // Rename the temporary column to documents
        DB::statement("ALTER TABLE policies ALTER COLUMN documents_temp RENAME TO documents");
    }
}
