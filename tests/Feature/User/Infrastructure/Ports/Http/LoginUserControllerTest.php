<?php

namespace Tests\Feature\User\Infrastructure\Ports\Http;

use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_with_valid_credentials(): void
    {
        UserModel::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertOk();
        $response->assertJsonFragment(['email' => 'jane@example.com']);
        $response->assertJsonStructure(['user' => ['id', 'name', 'email', 'role'], 'token']);
    }

    public function test_it_rejects_an_unknown_email(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertStatus(401);
    }

    public function test_it_rejects_an_invalid_password(): void
    {
        UserModel::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('secret1234'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }
}
