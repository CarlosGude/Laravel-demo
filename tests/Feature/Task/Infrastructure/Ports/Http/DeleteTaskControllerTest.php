<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const MISSING_UUID = '00000000-0000-0000-0000-000000000000';

    public function test_it_deletes_an_existing_task(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->uuid}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_it_removes_the_tasks_category_associations(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id]);
        $category = CategoryModel::factory()->create(['user_id' => $user->id]);
        $task->categories()->sync([$category->id]);

        $response = $this->deleteJson("/api/tasks/{$task->uuid}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('category_task', ['task_id' => $task->id]);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $this->actingAsUser();

        $response = $this->deleteJson('/api/tasks/'.self::MISSING_UUID);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $task = TaskModel::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->uuid}");

        $response->assertStatus(401);
    }

    public function test_it_returns_a_404_when_deleting_another_users_task(): void
    {
        $this->actingAsUser();
        $othersTask = TaskModel::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$othersTask->uuid}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('tasks', ['id' => $othersTask->id]);
    }

    public function test_an_admin_can_delete_another_users_task(): void
    {
        $this->actingAsUser(UserRole::Admin);
        $othersTask = TaskModel::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$othersTask->uuid}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tasks', ['id' => $othersTask->id]);
    }
}
