<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCustomersStatusToTinyint extends Migration
{
    public function up()
    {
        // Note: changing column types requires doctrine/dbal package installed.
        // composer require doctrine/dbal
        Schema::table('customers', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->change();
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Revert to string if that was the previous type — adjust as needed
            $table->string('status')->nullable()->change();
        });
    }
}
