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
        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable(); // Nullable to allow different entities like policies/claims
                $table->string('name');
                $table->string('path');
                $table->string('document_type'); // New column to store document type (customer, policy, claim, etc.)
                $table->timestamps();

                // Foreign key constraint for customers
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
