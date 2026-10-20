<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_an_existing_task(): void
    {
        $task = TaskModel::factory()->create([
            'name' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Buy milk and bread',
            'description' => 'Whole milk and sourdough bread',
            'completed' => true,
            'category_names' => ['Urgent'],
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $task->id,
            'title' => 'Buy milk and bread',
            'completed' => true,
            'categories' => ['Urgent'],
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Buy milk and bread',
            'completed' => true,
        ]);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $response = $this->putJson('/api/tasks/999', [
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);

        $response->assertStatus(404);
    }
}
