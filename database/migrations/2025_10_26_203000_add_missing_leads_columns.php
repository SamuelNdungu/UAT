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
        // Add missing columns only if they don't already exist. This makes the
        // migration safe to run on environments with older/newer schemas.
        if (! Schema::hasTable('leads')) {
            // If the table doesn't exist, nothing to do here.
            return;
        }

        // add columns that some environments may lack
        if (! Schema::hasColumn('leads', 'lead_type')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('lead_type')->nullable()->after('id');
            });
        }

        $cols = [
            'corporate_name', 'contact_name', 'first_name', 'last_name',
            'mobile', 'phone', 'email', 'email_address', 'policy_type',
            'estimated_premium', 'follow_up_date', 'upload', 'lead_source',
            'notes', 'deal_size', 'probability', 'weighted_revenue_forecast',
            'deal_stage', 'deal_status', 'date_initiated', 'closing_date', 'next_action', 'company_name'
        ];

        foreach ($cols as $col) {
            if (! Schema::hasColumn('leads', $col)) {
                Schema::table('leads', function (Blueprint $table) use ($col) {
                    switch ($col) {
                        case 'estimated_premium':
                        case 'deal_size':
                        case 'weighted_revenue_forecast':
                            $table->decimal($col, 15, 2)->nullable();
                            break;
                        case 'probability':
                            $table->decimal($col, 5, 2)->nullable();
                            break;
                        case 'follow_up_date':
                        case 'date_initiated':
                        case 'closing_date':
                            $table->date($col)->nullable();
                            break;
                        case 'upload':
                            // store uploads as JSON if supported
                            $table->json('upload')->nullable();
                            break;
                        case 'notes':
                        case 'next_action':
                            $table->text($col)->nullable();
                            break;
                        default:
                            $table->string($col)->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        $cols = [
            'lead_type','corporate_name', 'contact_name', 'first_name', 'last_name',
            'mobile', 'phone', 'email', 'email_address', 'policy_type',
            'estimated_premium', 'follow_up_date', 'upload', 'lead_source',
            'notes', 'deal_size', 'probability', 'weighted_revenue_forecast',
            'deal_stage', 'deal_status', 'date_initiated', 'closing_date', 'next_action', 'company_name'
        ];

        foreach ($cols as $col) {
            if (Schema::hasColumn('leads', $col)) {
                Schema::table('leads', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
