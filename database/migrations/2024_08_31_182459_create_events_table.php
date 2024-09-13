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
    Schema::create('claim_events', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('claim_id');
        $table->date('event_date');
        $table->string('event_type'); // e.g., "Documents Received", "Vehicle Repair", etc.
        $table->text('description')->nullable();
        $table->timestamps();

        // Foreign key constraint
        $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
    });
}
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_events');
    }
};
