<?php

namespace Whilesmart\Expenses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Whilesmart\Expenses\Enums\ExpenseStatus;
use Whilesmart\Expenses\Models\Expense;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $amount = $this->faker->numberBetween(500, 500000);
        $tax = (int) round($amount * 0.18);

        return [
            'number' => 'EXP-'.$this->faker->unique()->numberBetween(1000, 9999),
            'vendor_name' => $this->faker->company(),
            'category' => $this->faker->randomElement(['software', 'travel', 'office', 'marketing', 'utilities']),
            'description' => $this->faker->sentence(),
            'amount_cents' => $amount,
            'tax_cents' => $tax,
            'total_cents' => $amount + $tax,
            'currency' => 'USD',
            'status' => ExpenseStatus::Draft->value,
            'incurred_at' => $this->faker->dateTimeBetween('-90 days', 'now'),
        ];
    }
}
