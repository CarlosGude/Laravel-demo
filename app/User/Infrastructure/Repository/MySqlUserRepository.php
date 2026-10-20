<?php

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Models\UserColumn;
use App\User\Infrastructure\Models\UserModel;

final class MySqlUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return UserModel::query()->find($id)?->toEntity();
    }

    public function findByEmail(string $email): ?User
    {
        return UserModel::query()->where(UserColumn::Email->value, $email)->first()?->toEntity();
    }

    public function create(User $user): User
    {
        $model = new UserModel();
        $model->fillFromEntity($user)->save();

        return $model->toEntity();
    }

    public function update(User $user): User
    {
        $model = UserModel::query()->findOrFail($user->getPersistedId());
        $model->fillFromEntity($user)->save();

        return $model->toEntity();
    }

    public function issueApiToken(User $user): string
    {
        $model = UserModel::query()->findOrFail($user->getPersistedId());

        return $model->createToken('api-token')->plainTextToken;
    }
}
