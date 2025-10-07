<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10, 1000);
        
        return [
            'seller_id' => \App\Models\Seller::inRandomOrder()->first()->id ?? \App\Models\Seller::factory(),
            'amount' => $amount,
            'commission' => round($amount * 0.085, 2),
            'sale_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
