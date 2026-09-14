<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 50000);
        $tax = round($subtotal * 0.15, 2);
        $total = $subtotal + $tax;

        return [
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(100000, 99999999),
            'status' => fake()->randomElement(['posted', 'draft', 'partially_paid']),
            'subtotal' => $subtotal,
            'discount_amount' => 0.00,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => 0.00,
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
        ];
    }
}
