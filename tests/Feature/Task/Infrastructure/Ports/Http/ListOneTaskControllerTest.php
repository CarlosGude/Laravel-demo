<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListOneTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_matching_task(): void
    {
        $task = TaskModel::factory()->create(['name' => 'Buy milk']);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $task->id, 'title' => 'Buy milk']);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $response = $this->getJson('/api/tasks/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Task not found']);
    }
}
