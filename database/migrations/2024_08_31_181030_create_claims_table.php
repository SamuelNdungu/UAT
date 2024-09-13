<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('claims', function (Blueprint $table) {
            // Add any missing columns here
            $table->string('customer_code')->after('fileno'); // Example of adding a new column
            $table->string('upload_file')->nullable()->after('status'); // Example of adding another column
        });
    }

    public function down()
    {
        Schema::table('claims', function (Blueprint $table) {
            // Drop the columns if rolling back the migration
            $table->dropColumn('customer_code');
            $table->dropColumn('upload_file');
        });
    }
};
