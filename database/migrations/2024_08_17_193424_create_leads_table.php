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
        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
        $table->id();
        $table->string('lead_type'); // Corporate or Individual
        $table->string('corporate_name')->nullable();
        $table->string('contact_name')->nullable();
        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('mobile');
        $table->string('email');
        $table->string('policy_type');
        $table->decimal('estimated_premium', 10, 2);
        $table->date('follow_up_date');
        $table->json('upload')->nullable(); // for file uploads
        $table->string('lead_source');
        $table->text('notes')->nullable();
        $table->timestamps();
            });
        }
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
