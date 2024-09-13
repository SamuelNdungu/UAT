<?php 

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Report::create([
            'name' => 'Test Report',
            'file_path' => 'reports/test_report.pdf',
        ]);
    }
}
