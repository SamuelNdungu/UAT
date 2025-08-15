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
            // First drop the existing boolean status column
            $table->dropColumn('status');
            
            // Then add the new status column as string
            $table->string('status')->default('Active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // First drop the string status column
            $table->dropColumn('status');
            
            // Then recreate the boolean status column
            $table->boolean('status')->default(true);
        });
    }
};