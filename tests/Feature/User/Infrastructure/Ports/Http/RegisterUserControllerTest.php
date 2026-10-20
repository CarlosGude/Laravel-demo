<?php

namespace Tests\Feature\User\Infrastructure\Ports\Http;

use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ENDPOINT = '/api/auth/register';
    private const NAME = 'Jane Doe';
    private const EMAIL = 'jane@example.com';

    public function test_it_registers_a_new_user_and_returns_a_token(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => self::NAME,
            'email' => self::EMAIL,
            'password' => 'secret1234',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => self::NAME,
            'email' => self::EMAIL,
            'role' => 'user',
        ]);
        $response->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token']);
        $this->assertDatabaseHas('users', ['email' => self::EMAIL, 'role' => 'user']);
    }

    public function test_it_registers_the_user_as_a_regular_user_even_if_a_role_is_sent(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => self::NAME,
            'email' => self::EMAIL,
            'password' => 'secret1234',
            'role' => 'admin',
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['role' => 'user']);
        $this->assertDatabaseHas('users', ['email' => self::EMAIL, 'role' => 'user']);
    }

    public function test_it_rejects_a_duplicate_email(): void
    {
        UserModel::factory()->create(['email' => self::EMAIL]);

        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => self::NAME,
            'email' => self::EMAIL,
            'password' => 'secret1234',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_rejects_a_short_password(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => self::NAME,
            'email' => self::EMAIL,
            'password' => 'short',
        ]);

        $response->assertStatus(422);
    }
}
