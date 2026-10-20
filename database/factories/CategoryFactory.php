<?php

namespace Database\Factories;

use App\Task\Infrastructure\Models\CategoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryModel>
 */
class CategoryFactory extends Factory
{
    protected $model = CategoryModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
