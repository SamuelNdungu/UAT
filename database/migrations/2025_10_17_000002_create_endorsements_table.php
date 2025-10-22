<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('endorsements')) {
            Schema::create('endorsements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('policy_id')->constrained('policies')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Assuming 'users' table exists
                $table->string('type'); // 'addition', 'deletion', 'cancellation'
                $table->text('reason')->nullable();
                $table->date('effective_date')->nullable();

                // Financial delta fields
                $table->decimal('delta_sum_insured', 10, 2)->nullable();
                $table->decimal('delta_premium', 10, 2)->nullable();
                $table->decimal('delta_commission', 10, 2)->nullable();
                $table->decimal('delta_wht', 10, 2)->nullable();
                $table->decimal('delta_s_duty', 10, 2)->nullable();
                $table->decimal('delta_t_levy', 10, 2)->nullable();
                $table->decimal('delta_pcf_levy', 10, 2)->nullable();
                $table->decimal('delta_policy_charge', 10, 2)->nullable();
                $table->decimal('delta_aa_charges', 10, 2)->nullable();
                $table->decimal('delta_other_charges', 10, 2)->nullable();
                $table->decimal('delta_gross_premium', 10, 2)->nullable();
                $table->decimal('delta_net_premium', 10, 2)->nullable();
                $table->decimal('delta_excess', 10, 2)->nullable();
                $table->decimal('delta_courtesy_car', 10, 2)->nullable();
                $table->decimal('delta_ppl', 10, 2)->nullable();
                $table->decimal('delta_road_rescue', 10, 2)->nullable();

                $table->timestamps();
            });
        } else {
            Schema::table('endorsements', function (Blueprint $table) {
                if (!Schema::hasColumn('endorsements', 'user_id')) {
                    // Add nullable user_id to avoid NOT NULL violations for existing rows
                    $table->unsignedBigInteger('user_id')->nullable();
                    // Optionally add a foreign key constraint if needed in a future migration
                    // $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
                }
                if (!Schema::hasColumn('endorsements', 'type')) {
                    $table->string('type')->nullable();
                }
                if (!Schema::hasColumn('endorsements', 'reason')) {
                    $table->text('reason')->nullable();
                }
                $deltaCols = [
                    'delta_sum_insured','delta_premium','delta_commission','delta_wht','delta_s_duty','delta_t_levy',
                    'delta_pcf_levy','delta_policy_charge','delta_aa_charges','delta_other_charges','delta_gross_premium',
                    'delta_net_premium','delta_excess','delta_courtesy_car','delta_ppl','delta_road_rescue'
                ];
                foreach ($deltaCols as $c) {
                    if (!Schema::hasColumn('endorsements', $c)) {
                        $table->decimal($c, 10, 2)->nullable();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
