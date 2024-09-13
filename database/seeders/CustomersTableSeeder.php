<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('customers')->insert([
            [
                'customer_type' => 'Individual',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'surname' => 'Smith',
                'dob' => Carbon::create('1985', '03', '25'),
                'occupation' => 'Engineer',
                'company_name' => 'Doe Tech Ltd.',
                'business_no' => '123456789',
                'contact_person' => 'Jane Doe',
                'designation' => 'Manager',
                'industry_class' => 'Technology',
                'industry_segment' => 'IT Services',
                'email' => 'john.doe@example.com',
                'phone' => '0712345678',
                'address' => '123 Main St',
                'city' => 'Nairobi',
                'county' => 'Nairobi',
                'postal_code' => '00100',
                'country' => 'Kenya',
                'id_number' => '12345678',
                'kra_pin' => 'A001234567X',
                'documents' => 'file.pdf',
                'notes' => 'Test note for John Doe',
            ],
            [
                'customer_type' => 'Corporate',
                'first_name' => 'Mary',
                'last_name' => 'Jane',
                'surname' => 'Williams',
                'dob' => Carbon::create('1990', '07', '14'),
                'occupation' => 'CEO',
                'company_name' => 'Williams Enterprises',
                'business_no' => '987654321',
                'contact_person' => 'Tom Williams',
                'designation' => 'Director',
                'industry_class' => 'Finance',
                'industry_segment' => 'Banking',
                'email' => 'mary.jane@example.com',
                'phone' => '0723456789',
                'address' => '456 Queen St',
                'city' => 'Mombasa',
                'county' => 'Mombasa',
                'postal_code' => '00200',
                'country' => 'Kenya',
                'id_number' => '98765432',
                'kra_pin' => 'B009876543X',
                'documents' => 'file2.pdf',
                'notes' => 'Test note for Mary Jane',
            ]
            // Add more customers here as needed
        ]);
    }
}
