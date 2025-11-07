<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First attempt to drop the foreign key constraint from policies table
        // only if the column exists. Wrap in a try/catch in case the constraint
        // name differs or was not created.
        if (Schema::hasTable('policies') && Schema::hasColumn('policies', 'lead_id')) {
            // Try to find any foreign key constraints referencing the policies.lead_id
            // column and drop them by name. This avoids executing a drop that
            // would fail and leave the transaction in an aborted state.
            try {
                $fks = \DB::select(
                    "SELECT conname FROM pg_constraint
                     JOIN pg_class ON conrelid = pg_class.oid
                     JOIN pg_attribute ON attrelid = conrelid AND attnum = ANY(conkey)
                     WHERE pg_class.relname = ? AND attname = ? AND contype = 'f'",
                    ['policies', 'lead_id']
                );

                foreach ($fks as $fk) {
                    $name = $fk->conname;
                    if ($name) {
                        \DB::statement("ALTER TABLE policies DROP CONSTRAINT IF EXISTS \"{$name}\"");
                    }
                }
            } catch (\Throwable $e) {
                // ignore any errors here; if we cannot drop the constraint
                // migration will continue and creation of leads will proceed.
            }
        }

        Schema::dropIfExists('leads');
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->decimal('deal_size', 15, 2)->nullable();
            $table->decimal('probability', 5, 2)->nullable()->comment('Probability of deal in percentage');
            $table->decimal('weighted_revenue_forecast', 15, 2)->nullable();
            $table->string('deal_stage')->nullable();
            $table->string('deal_status')->nullable();
            $table->date('date_initiated')->nullable();
            $table->date('closing_date')->nullable();
            $table->text('next_action')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email_address')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes for commonly searched columns
            $table->index('company_name');
            $table->index('deal_stage');
            $table->index('deal_status');
            $table->index('date_initiated');
            $table->index('closing_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leads');
    }
};