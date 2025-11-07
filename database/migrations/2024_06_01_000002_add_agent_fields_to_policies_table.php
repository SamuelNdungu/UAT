<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentFieldsToPoliciesTable extends Migration
{
    public function up()
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('customer_code')->constrained('agents')->onDelete('set null');
            $table->decimal('agent_commission', 12, 2)->nullable()->after('agent_id');
        });
    }

    public function down()
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn(['agent_id', 'agent_commission']);
        });
    }
}
