<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First drop the foreign key constraint from policies table
        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
        });

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