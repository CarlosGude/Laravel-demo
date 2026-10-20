<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_an_existing_task(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create([
            'user_id' => $user->id,
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
        $this->actingAsUser();

        $response = $this->putJson('/api/tasks/999', [
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $task = TaskModel::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_it_returns_a_404_when_updating_another_users_task(): void
    {
        $this->actingAsUser();
        $othersTask = TaskModel::factory()->create();

        $response = $this->putJson("/api/tasks/{$othersTask->id}", [
            'title' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);

        $response->assertStatus(404);
    }

    public function test_an_admin_can_update_another_users_task(): void
    {
        $this->actingAsUser(UserRole::Admin);
        $othersTask = TaskModel::factory()->create(['completed' => false]);

        $response = $this->putJson("/api/tasks/{$othersTask->id}", [
            'title' => $othersTask->name,
            'description' => $othersTask->description,
            'completed' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $othersTask->id, 'completed' => true]);
    }
}
