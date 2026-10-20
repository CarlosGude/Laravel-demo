<?php

namespace Database\Factories;

use App\User\Domain\Entity\UserRole;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

/**
 * @extends Factory<UserModel>
 */
class UserFactory extends Factory
{
    protected $model = UserModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Uuid::uuid4()->toString(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => UserRole::Regular,
        ];
    }

    /**
     * @return Factory<UserModel>
     */
    public function admin(): Factory
    {
        return $this->state(fn (array $attributes): array => [
            'role' => UserRole::Admin,
        ]);
    }
}
