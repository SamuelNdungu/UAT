<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Canonical endorsements table: union of historically used columns so migrations are consistent
        if (!Schema::hasTable('endorsements')) {
            Schema::create('endorsements', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('policy_id');

                // User who created the endorsement (nullable for legacy rows)
                $table->unsignedBigInteger('user_id')->nullable();

                // Both legacy and canonical type columns (some code references one or the other)
                $table->string('endorsement_type')->nullable();
                $table->string('type')->nullable(); // canonical 'type'
                $table->text('reason')->nullable();

                // Dates
                $table->date('effective_date')->nullable();
                $table->timestamp('effective_timestamp')->nullable();

                // Document
                $table->string('document_path')->nullable();

                // Financial fields (both absolute and delta fields included)
                $table->decimal('sum_insured', 18, 2)->nullable();
                $table->decimal('rate', 8, 2)->nullable();
                $table->decimal('premium', 18, 2)->nullable();
                $table->decimal('premium_impact', 15, 2)->nullable();
                $table->decimal('c_rate', 8, 2)->nullable();
                $table->decimal('commission', 18, 2)->nullable();
                $table->decimal('wht', 18, 2)->nullable();
                $table->decimal('s_duty', 18, 2)->nullable();
                $table->decimal('t_levy', 18, 2)->nullable();
                $table->decimal('pcf_levy', 18, 2)->nullable();
                $table->decimal('policy_charge', 18, 2)->nullable();
                $table->decimal('aa_charges', 18, 2)->nullable();
                $table->decimal('other_charges', 18, 2)->nullable();
                $table->decimal('gross_premium', 18, 2)->nullable();
                $table->decimal('net_premium', 18, 2)->nullable();
                $table->decimal('excess', 18, 2)->nullable();
                $table->decimal('courtesy_car', 18, 2)->nullable();
                $table->decimal('ppl', 18, 2)->nullable();
                $table->decimal('road_rescue', 18, 2)->nullable();

                // Delta fields used by some schema variants
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

                $table->unsignedBigInteger('created_by')->nullable();

                $table->timestamps();

                // Foreign keys
                $table->foreign('policy_id')->references('id')->on('policies')->onDelete('cascade');
                // Note: user foreign key omitted to avoid locking existing data; add in a later migration if desired.
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
