<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeDocumentsColumnTypeInPoliciesTable extends Migration
{
    public function up()
    {
        // Replace NULL values with an empty string or a default path
        DB::table('policies')
            ->whereNull('documents')
            ->update(['documents' => '']);

        // Now alter the column
        Schema::table('policies', function (Blueprint $table) {
            $table->string('documents')->change();
            $table->string('documents')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->json('documents')->change(); // Revert to json if needed
        });
    }
}
