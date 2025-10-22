<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('company_data') && !Schema::hasColumn('company_data', 'logo_path')) {
            Schema::table('company_data', function (Blueprint $table) {
                $table->string('logo_path')->nullable()->after('website');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('company_data') && Schema::hasColumn('company_data', 'logo_path')) {
            Schema::table('company_data', function (Blueprint $table) {
                $table->dropColumn('logo_path');
            });
        }
    }
};
