<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('customers', function (Blueprint $table) {
        // Add the 'status' column as a boolean and set default to true (active)
        $table->boolean('status')->default(true);  // true represents active
    });
}

public function down()
{
    Schema::table('customers', function (Blueprint $table) {
        // Drop the 'status' column
        $table->dropColumn('status');
    });
}

};
