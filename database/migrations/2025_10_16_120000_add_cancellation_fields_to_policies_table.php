<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            if (!Schema::hasColumn('policies', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable();
            }
            if (!Schema::hasColumn('policies', 'cancellation_date')) {
                $table->date('cancellation_date')->nullable();
            }
            if (!Schema::hasColumn('policies', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_date', 'status']);
        });
    }
};
