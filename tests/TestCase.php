<?php

namespace Tests;

use App\User\Domain\Entity\UserRole;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsUser(UserRole $role = UserRole::Regular): UserModel
    {
        $user = UserModel::factory()->create(['role' => $role]);

        Sanctum::actingAs($user);

        return $user;
    }
}
