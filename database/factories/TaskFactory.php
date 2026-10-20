<?php

namespace Database\Factories;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

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
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => UserModel::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'completed' => false,
        ];
    }
}
