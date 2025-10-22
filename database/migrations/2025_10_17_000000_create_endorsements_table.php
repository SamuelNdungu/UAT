<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('endorsements')) {
            Schema::create('endorsements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('policy_id');
                $table->string('endorsement_type'); // addition, deletion, cancellation
                $table->date('effective_date')->nullable();
                $table->string('description')->nullable();
                $table->string('document_path')->nullable();
                // Financial fields
                $table->decimal('sum_insured', 18, 2)->nullable();
                $table->decimal('rate', 8, 2)->nullable();
                $table->decimal('premium', 18, 2)->nullable();
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
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                // Foreign key constraint
                $table->foreign('policy_id')->references('id')->on('policies')->onDelete('cascade');
            });
        } else {
            // Table exists (created by another migration); add any missing columns
            Schema::table('endorsements', function (Blueprint $table) {
                if (!Schema::hasColumn('endorsements', 'endorsement_type')) {
                    $table->string('endorsement_type')->nullable();
                }
                $cols = [
                    'effective_date' => function($t){ $t->date('effective_date')->nullable(); },
                    'description' => function($t){ $t->string('description')->nullable(); },
                    'document_path' => function($t){ $t->string('document_path')->nullable(); },
                    'sum_insured' => function($t){ $t->decimal('sum_insured', 18, 2)->nullable(); },
                    'rate' => function($t){ $t->decimal('rate', 8, 2)->nullable(); },
                    'premium' => function($t){ $t->decimal('premium', 18, 2)->nullable(); },
                    'c_rate' => function($t){ $t->decimal('c_rate', 8, 2)->nullable(); },
                    'commission' => function($t){ $t->decimal('commission', 18, 2)->nullable(); },
                    'wht' => function($t){ $t->decimal('wht', 18, 2)->nullable(); },
                    's_duty' => function($t){ $t->decimal('s_duty', 18, 2)->nullable(); },
                    't_levy' => function($t){ $t->decimal('t_levy', 18, 2)->nullable(); },
                    'pcf_levy' => function($t){ $t->decimal('pcf_levy', 18, 2)->nullable(); },
                    'policy_charge' => function($t){ $t->decimal('policy_charge', 18, 2)->nullable(); },
                    'aa_charges' => function($t){ $t->decimal('aa_charges', 18, 2)->nullable(); },
                    'other_charges' => function($t){ $t->decimal('other_charges', 18, 2)->nullable(); },
                    'gross_premium' => function($t){ $t->decimal('gross_premium', 18, 2)->nullable(); },
                    'net_premium' => function($t){ $t->decimal('net_premium', 18, 2)->nullable(); },
                    'excess' => function($t){ $t->decimal('excess', 18, 2)->nullable(); },
                    'courtesy_car' => function($t){ $t->decimal('courtesy_car', 18, 2)->nullable(); },
                    'ppl' => function($t){ $t->decimal('ppl', 18, 2)->nullable(); },
                    'road_rescue' => function($t){ $t->decimal('road_rescue', 18, 2)->nullable(); },
                    'created_by' => function($t){ $t->unsignedBigInteger('created_by')->nullable(); },
                ];

                foreach ($cols as $col => $callback) {
                    if (!Schema::hasColumn('endorsements', $col)) {
                        $callback($table);
                    }
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('endorsements');
    }
};
