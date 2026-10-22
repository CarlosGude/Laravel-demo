<?php

namespace Tests\Feature\User\Infrastructure\Ports\Http;

use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_token_is_accepted_before_it_expires(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->travel(config('sanctum.expiration') - 1)->minutes();

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/auth/logout');

        $response->assertOk();
    }

    public function test_a_token_is_rejected_once_it_has_expired(): void
    {
        $user = UserModel::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $this->travel(config('sanctum.expiration') + 1)->minutes();

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }
}
