<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRenewalsTable extends Migration
{
    public function up()
    {
        Schema::create('renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_policy_id')->constrained('policies')->onDelete('cascade');
            $table->foreignId('renewed_policy_id')->constrained('policies')->onDelete('cascade');
            $table->string('renewal_type')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('renewals');
    }
}
