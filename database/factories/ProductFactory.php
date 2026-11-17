<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
    $title = fake()->unique()->sentence();
    return [
        'title' => $title,
        'slug' => str($title)->slug(),
        'category_id' => 28, // Apni cat id dein
        'brand_id' => 1,
        'price' => rand(10, 1000),
        'sku' => rand(1000, 10000),
        'track_quantity' => 'yes',
        'qty' => 10,
        'is_featured' => 'yes',
        'status' => 1
    ];
}
}
