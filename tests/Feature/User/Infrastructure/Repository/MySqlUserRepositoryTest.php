<?php

namespace Tests\Feature\User\Infrastructure\Repository;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserRole;
use App\User\Infrastructure\Models\UserModel;
use App\User\Infrastructure\Repository\MySqlUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlUserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MySqlUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MySqlUserRepository();
    }

    public function test_create_persists_the_user_and_returns_it_with_an_id(): void
    {
        $user = $this->repository->create(User::createNew('Jane Doe', 'jane@example.com', 'hashed-password'));

        $this->assertNotNull($user->getId());
        $this->assertSame('Jane Doe', $user->getName());
        $this->assertSame('jane@example.com', $user->getEmail());
        $this->assertDatabaseHas('users', [
            'id' => $user->getId(),
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);
    }

    public function test_find_by_email_returns_null_when_the_user_does_not_exist(): void
    {
        $this->assertNull($this->repository->findByEmail('missing@example.com'));
    }

    public function test_find_by_email_returns_the_matching_user(): void
    {
        $model = UserModel::factory()->create(['email' => 'jane@example.com']);

        $user = $this->repository->findByEmail('jane@example.com');

        $this->assertNotNull($user);
        $this->assertSame($model->id, $user->getId());
    }

    public function test_update_persists_the_role_change(): void
    {
        $model = UserModel::factory()->create(['role' => UserRole::Regular]);
        $existingUser = $model->toEntity();

        $this->repository->update($existingUser->promoteToAdmin());

        $this->assertDatabaseHas('users', ['id' => $model->id, 'role' => 'admin']);
    }

    public function test_issue_api_token_returns_a_usable_plain_text_token(): void
    {
        $model = UserModel::factory()->create();

        $token = $this->repository->issueApiToken($model->toEntity());

        $this->assertNotEmpty($token);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
