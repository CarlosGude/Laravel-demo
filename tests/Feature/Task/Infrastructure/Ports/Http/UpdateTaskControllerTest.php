<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TASK_TITLE = 'Buy milk';
    private const TASK_DESCRIPTION = 'Whole milk, 2 liters';
    private const UPDATED_TASK_TITLE = 'Buy milk and bread';
    private const MISSING_UUID = '00000000-0000-0000-0000-000000000000';

    public function test_it_updates_an_existing_task(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'title' => self::UPDATED_TASK_TITLE,
            'description' => 'Whole milk and sourdough bread',
            'completed' => true,
            'category_names' => ['Urgent'],
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $task->uuid,
            'title' => self::UPDATED_TASK_TITLE,
            'completed' => true,
            'categories' => ['Urgent'],
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => self::UPDATED_TASK_TITLE,
            'completed' => true,
        ]);
    }

    public function test_it_returns_a_404_when_the_task_does_not_exist(): void
    {
        $this->actingAsUser();

        $response = $this->putJson('/api/tasks/'.self::MISSING_UUID, [
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response->assertStatus(404);
    }

    public function test_it_requires_authentication(): void
    {
        $task = TaskModel::factory()->create();

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_it_returns_a_404_when_updating_another_users_task(): void
    {
        $this->actingAsUser();
        $othersTask = TaskModel::factory()->create();

        $response = $this->putJson("/api/tasks/{$othersTask->uuid}", [
            'title' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response->assertStatus(404);
    }

    public function test_an_admin_can_update_another_users_task(): void
    {
        $this->actingAsUser(UserRole::Admin);
        $othersTask = TaskModel::factory()->create(['completed' => false]);

        $response = $this->putJson("/api/tasks/{$othersTask->uuid}", [
            'title' => $othersTask->name,
            'description' => $othersTask->description,
            'completed' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $othersTask->id, 'completed' => true]);
    }

    public function test_it_rejects_an_empty_title(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'title' => '',
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_it_rejects_a_title_longer_than_255_characters(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id]);

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'title' => str_repeat('a', 256),
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    public function test_it_rejects_an_update_that_omits_completed(): void
    {
        $user = $this->actingAsUser();
        $task = TaskModel::factory()->create(['user_id' => $user->id, 'completed' => true]);

        $response = $this->putJson("/api/tasks/{$task->uuid}", [
            'title' => self::UPDATED_TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['completed']);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'completed' => true]);
    }
}
