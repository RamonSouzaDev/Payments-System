<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'cpf_cnpj' => $this->faker->numerify('###########'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'address_number' => $this->faker->buildingNumber(),
            'address_complement' => $this->faker->secondaryAddress(),
            'province' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'external_id' => 'cus_' . $this->faker->unique()->randomNumber(6),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}