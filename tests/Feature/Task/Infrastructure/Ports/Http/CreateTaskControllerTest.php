<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_task_and_returns_it(): void
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'category_names' => ['Work'],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
            'categories' => ['Work'],
        ]);
        $this->assertDatabaseHas('tasks', ['name' => 'Buy milk']);
        $this->assertDatabaseHas('categories', ['name' => 'Work']);
    }
}
