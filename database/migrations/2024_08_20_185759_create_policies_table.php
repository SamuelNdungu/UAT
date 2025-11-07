<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The error indicates the 'policies' table already exists and this migration
        // is trying to add a 'paid_amount' column that is also already there.
        // We will modify this to safely add the column only if it doesn't exist.
        Schema::table('policies', function (Blueprint $table) {
            if (!Schema::hasColumn('policies', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0);
            }
            // It's common for 'balance' to be added at the same time.
            // We'll add a check for it as well to prevent future errors.
            if (!Schema::hasColumn('policies', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0);
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
        Schema::table('policies', function (Blueprint $table) {
            // This will safely drop the columns if they exist.
            if (Schema::hasColumn('policies', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('policies', 'balance')) {
                $table->dropColumn('balance');
            }
        });
    }
}
