<?php

namespace Tests\Feature\Console;

use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoteUserToAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_an_existing_user_to_admin(): void
    {
        $user = UserModel::factory()->create(['email' => 'jane@example.com']);

        $this->artisan('user:make-admin', ['email' => 'jane@example.com'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
    }

    public function test_it_fails_when_the_user_does_not_exist(): void
    {
        $this->artisan('user:make-admin', ['email' => 'missing@example.com'])
            ->assertExitCode(1);
    }
}
