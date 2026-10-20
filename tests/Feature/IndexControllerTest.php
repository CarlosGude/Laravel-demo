<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->getJson('/');

        $response->assertOk()
            ->assertExactJson(['message' => 'Hello World!'])
        ;

        $response->assertStatus(200);
    }
}
