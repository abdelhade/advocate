<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['individual', 'company', 'organization']),
            'name' => fake()->name(),
            'national_id_or_cr' => (string) fake()->numberBetween(1000000000, 9999999999),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'notes' => 'عميل تم إنشاؤه بواسطة البيانات التجريبية',
        ];
    }
}
