<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListOneTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_matching_task(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Buy milk']);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $task->id, 'title' => 'Buy milk']);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/tasks/999');

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Task not found']);
    }

    public function test_it_requires_authentication(): void
    {
        $task = TaskModel::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(401);
    }

    public function test_it_returns_a_404_when_another_users_task_is_requested(): void
    {
        $this->actingAsUser();
        $othersTask = TaskModel::factory()->create();

        $response = $this->getJson("/api/tasks/{$othersTask->id}");

        $response->assertStatus(404);
    }

    public function test_an_admin_can_see_another_users_task(): void
    {
        $this->actingAsUser(UserRole::Admin);
        $othersTask = TaskModel::factory()->create(['name' => 'Buy milk']);

        $response = $this->getJson("/api/tasks/{$othersTask->id}");

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Buy milk']);
    }
}
