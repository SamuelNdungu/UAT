<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $customerType = $this->faker->randomElement(['Individual', 'Corporate']);

        $attributes = [
            'customer_code' => 'CUST-' . strtoupper(Str::random(6)),
            'customer_type' => $customerType,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => true,
        ];

        if ($customerType === 'Individual') {
            $attributes['first_name'] = $this->faker->firstName();
            $attributes['last_name'] = $this->faker->lastName();
            $attributes['surname'] = $this->faker->lastName();
        } else {
            $attributes['corporate_name'] = $this->faker->company();
        }

        return $attributes;
    }
}
