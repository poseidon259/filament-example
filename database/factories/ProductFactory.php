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
            'name' => fake()->name,
            'product_code' => fake()->unique()->randomNumber(5),
            'product_type' => fake()->userName(),
            'qty' => fake()->numberBetween(1, 100),
            'price' => fake()->randomFloat(2, 1, 1000),
        ];
    }
}
