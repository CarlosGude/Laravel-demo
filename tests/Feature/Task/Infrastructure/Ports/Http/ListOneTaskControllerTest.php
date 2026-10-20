<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListOneTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TASK_TITLE = 'Buy milk';
    private const MISSING_UUID = '00000000-0000-0000-0000-000000000000';

    public function test_it_returns_the_matching_task(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id, 'name' => self::TASK_TITLE]);

        $response = $this->getJson("/api/tasks/{$task->uuid}");

        $response->assertOk();
        $response->assertJsonFragment(['id' => $task->uuid, 'title' => self::TASK_TITLE]);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/tasks/'.self::MISSING_UUID);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Task not found']);
    }

    public function test_it_requires_authentication(): void
    {
        $task = TaskModel::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->uuid}");

        $response->assertStatus(401);
    }

    public function test_it_returns_a_404_when_another_users_task_is_requested(): void
    {
        $this->actingAsUser();
        $othersTask = TaskModel::factory()->create();

        $response = $this->getJson("/api/tasks/{$othersTask->uuid}");

        $response->assertStatus(404);
    }

    public function test_an_admin_can_see_another_users_task(): void
    {
        $this->actingAsUser(UserRole::Admin);
        $othersTask = TaskModel::factory()->create(['name' => self::TASK_TITLE]);

        $response = $this->getJson("/api/tasks/{$othersTask->uuid}");

        $response->assertOk();
        $response->assertJsonFragment(['title' => self::TASK_TITLE]);
    }
}
