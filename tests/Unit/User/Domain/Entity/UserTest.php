<?php

namespace Tests\Unit\User\Domain\Entity;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserRole;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_create_new_builds_a_regular_user_without_an_id(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password');

        $this->assertNull($user->getId());
        $this->assertSame('Jane Doe', $user->getName());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertSame('hashed-password', $user->getPasswordHash());
        $this->assertSame(UserRole::Regular, $user->getRole());
        $this->assertFalse($user->isAdmin());
    }

    public function test_create_with_id_keeps_the_rest_of_the_data_unchanged(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password');

        $userWithId = $user->createWithId(5);

        $this->assertSame(5, $userWithId->getId());
        $this->assertSame($user->getName(), $userWithId->getName());
        $this->assertSame($user->getEmail(), $userWithId->getEmail());
        $this->assertSame($user->getRole(), $userWithId->getRole());
    }

    public function test_promote_to_admin_changes_the_role_but_keeps_the_rest_of_the_data(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password')->createWithId(1);

        $admin = $user->promoteToAdmin();

        $this->assertTrue($admin->isAdmin());
        $this->assertSame(UserRole::Admin, $admin->getRole());
        $this->assertSame($user->getId(), $admin->getId());
        $this->assertSame($user->getName(), $admin->getName());
        $this->assertSame($user->getEmail(), $admin->getEmail());
    }

    public function test_get_persisted_id_throws_when_the_user_has_not_been_persisted(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('User has not been persisted yet.');

        $user->getPersistedId();
    }
}
