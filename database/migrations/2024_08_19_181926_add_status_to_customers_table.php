<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Check if the column does not already exist before adding it.
            if (!Schema::hasColumn('customers', 'status')) {
                $table->boolean('status')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Check if the column exists before trying to drop it.
            if (Schema::hasColumn('customers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
