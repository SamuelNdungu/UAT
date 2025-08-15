<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDocumentsColumnToJsonFormat extends Migration
{
    public function up()
    {
        // Ensure the documents column is of text type before altering
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text");

        // Convert existing data to JSON format
        DB::statement("UPDATE policies SET documents = '[]' WHERE documents IS NULL");
        DB::statement("UPDATE policies SET documents = json_agg(json_build_object('name', unnest(string_to_array(documents, ',')), 'description', NULL)) WHERE documents IS NOT NULL AND documents != '[]'");

        // Alter the documents column to json type with the correct conversion method
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE json USING documents::json");
    }

    public function down()
    {
        // Revert the documents column to text type if needed
        DB::statement("ALTER TABLE policies ALTER COLUMN documents TYPE text");

        // Optionally, revert the data back to the original format if needed
        // For example, converting JSON back to a comma-separated string of file names
        DB::statement("UPDATE policies SET documents = (SELECT string_agg(name, ',') FROM jsonb_array_elements(documents) AS element(name text, description text)) WHERE documents IS NOT NULL AND documents != '[]'");
        DB::statement("UPDATE policies SET documents = NULL WHERE documents = '[]'");
    }
}
