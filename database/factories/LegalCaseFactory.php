<?php

namespace Database\Factories;

use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LegalCase>
 */
class LegalCaseFactory extends Factory
{
    protected $model = LegalCase::class;

    public function definition(): array
    {
        return [
            'case_number' => 'CASE-' . fake()->unique()->numberBetween(100000, 99999999),
            'internal_number' => 'INT-' . fake()->numberBetween(1000, 9999),
            'title' => 'قضية ' . fake()->randomElement(['تجاري', 'عمالي', 'مدني', 'جنائي', 'أحوال شخصية']) . ' - ' . fake()->word(),
            'court_name' => fake()->randomElement(['المحكمة العامة', 'المحكمة التجارية', 'المحكمة العمالية', 'محكمة التنفيذ']),
            'circuit' => 'الدائرة ' . fake()->numberBetween(1, 20),
            'case_type' => fake()->randomElement(['تجاري', 'عمالي', 'مدني', 'جنائي']),
            'status' => fake()->randomElement(['active', 'suspended', 'won', 'lost', 'closed']),
        ];
    }
}
