<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Policy;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if customers exist to assign policies to.
        if (Customer::count() == 0) {
            $this->command->warn('No customers found. Please seed customers first before seeding policies.');
            return;
        }

        // To prevent foreign key errors, we'll disable checks,
        // clear the related tables, and then re-enable checks.
        Schema::disableForeignKeyConstraints();

        // Clear out any existing records to start fresh.
        // We truncate renewals first because it depends on policies.
        DB::table('renewals')->truncate();
        DB::table('policies')->truncate();

        Schema::enableForeignKeyConstraints();

        $this->command->info('Cleared existing policies and renewals.');

        // Create 30 active policies (their end_date will be in the future).
        Policy::factory()->count(30)->create();

        // Create 20 expired policies using the 'expired' state from the factory.
        Policy::factory()->count(20)->expired()->create();

        $this->command->info('Successfully seeded 50 policies (30 active, 20 expired).');
    }
}
