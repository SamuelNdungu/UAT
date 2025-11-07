<?php

namespace Database\Factories;

use App\Models\Policy;
use App\Models\Customer;
use App\Models\Insurer;
use App\Models\PolicyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PolicyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Policy::class;

    /**
     * The file number sequence.
     *
     * @var int
     */
    protected static $fileNumber;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Initialize the file number counter once by finding the last file number in the database.
        if (is_null(static::$fileNumber)) {
            $lastPolicy = Policy::orderBy('fileno', 'desc')->first();
            // If a policy exists, get its number. Otherwise, start from 5 as requested.
            static::$fileNumber = $lastPolicy ? (int)str_replace('FN-', '', $lastPolicy->fileno) : 5;
        }

        // Get random existing records to link the policy to.
        // This will fail if you have no customers, insurers, or policy types in your database.
        $customer = Customer::inRandomOrder()->first();
        $insurer = Insurer::inRandomOrder()->first();
        $policyType = PolicyType::inRandomOrder()->first();

        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = (clone $startDate)->modify('+1 year');

        $premium = $this->faker->numberBetween(5000, 50000);

        return [
            'fileno' => 'FN-' . str_pad(++static::$fileNumber, 5, '0', STR_PAD_LEFT),
            'customer_code' => $customer->customer_code,
            'customer_name' => $customer->customer_type === 'Corporate' ? $customer->corporate_name : ($customer->first_name . ' ' . $customer->last_name),
            'policy_type_id' => $policyType->id,
            'insurer_id' => $insurer->id,
            'policy_no' => 'POL-' . strtoupper(Str::random(8)),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sum_insured' => $this->faker->numberBetween(100000, 5000000),
            'premium' => $premium,
            'gross_premium' => $premium * 1.1, // Example calculation
            'net_premium' => $premium,
            'status' => 'active',
            'insured' => 'Vehicle: ' . $this->faker->company . ' ' . $this->faker->word,
            'reg_no' => strtoupper(Str::random(3)) . ' ' . $this->faker->numberBetween(100, 999) . strtoupper(Str::random(1)),
        ];
    }

    /**
     * Indicate that the policy is expired.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function expired()
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-2 years', '-1 year');
            $endDate = (clone $startDate)->modify('+1 year')->modify('-1 day'); // Ensures the date is in the past

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'expired',
            ];
        });
    }
}
