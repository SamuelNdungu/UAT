<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
           if (!Schema::hasTable('renewal_notices')) {
        Schema::create('renewal_notices', function (Blueprint $table) {
            $table->id();
            $table->string('fileno')->index()->nullable();
            $table->unsignedBigInteger('policy_id')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('channel')->default('email');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->string('message_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            // optionally add foreign key if policies table exists and you want cascade
            // $table->foreign('policy_id')->references('id')->on('policies')->onDelete('set null');
        });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('renewal_notices');
    }
};
