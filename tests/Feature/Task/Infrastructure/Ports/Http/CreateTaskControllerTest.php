<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TASK_TITLE = 'Buy milk';
    private const TASK_DESCRIPTION = 'Whole milk, 2 liters';

    public function test_it_creates_a_task_and_returns_it(): void
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/tasks', [
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'category_names' => ['Work'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
            'categories' => ['Work'],
        ]);
        $this->assertDatabaseHas('tasks', ['name' => self::TASK_TITLE, 'user_id' => $user->id]);
        $this->assertDatabaseHas('categories', ['name' => 'Work', 'user_id' => $user->id]);
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
        ]);

        $response->assertStatus(401);
    }

    public function test_it_rejects_an_empty_title(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/tasks', [
            'title' => '',
            'description' => self::TASK_DESCRIPTION,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_it_rejects_a_title_longer_than_255_characters(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/tasks', [
            'title' => str_repeat('a', 256),
            'description' => self::TASK_DESCRIPTION,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }
}
