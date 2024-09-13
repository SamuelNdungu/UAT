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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->onDelete('cascade');
        $table->date('payment_date');
        $table->decimal('payment_amount', 15, 2);
        $table->string('payment_method')->nullable();
        $table->string('payment_reference')->nullable();
        $table->string('payment_status')->default('pending');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
