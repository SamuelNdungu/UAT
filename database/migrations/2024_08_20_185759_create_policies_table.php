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
        Schema::table('policies', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
            $table->dropColumn('outstanding_amount');
        });
    }
    
};
