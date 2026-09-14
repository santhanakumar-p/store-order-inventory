<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'selling_price' => fake()->randomFloat(2, 10, 5000),
            'tax_rate' => fake()->randomElement([0, 5, 12, 18]),
            'qty' => fake()->numberBetween(20, 200),
            'min_qty_level' => fake()->numberBetween(5, 15),
        ];
    }

    /**
     * Indicate that the product is at or below its minimum quantity level.
     */
    public function lowStock(): static
    {
        return $this->state(function (array $attributes) {
            $minQtyLevel = $attributes['min_qty_level'] ?? 10;

            return [
                'min_qty_level' => $minQtyLevel,
                'qty' => fake()->numberBetween(0, $minQtyLevel),
            ];
        });
    }
}
