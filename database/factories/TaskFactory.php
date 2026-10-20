<?php

namespace Database\Factories;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskModel>
 */
class TaskFactory extends Factory
{
    protected $model = TaskModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserModel::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'completed' => false,
        ];
    }
}
