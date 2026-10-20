<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Models\TaskModel;
use App\User\Domain\Entity\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private const TASKS_ENDPOINT = '/api/tasks';
    private const DATE_JAN = '2026-01-01 10:00:00';
    private const DATE_FEB = '2026-02-01 10:00:00';

    public function test_it_lists_the_authenticated_users_tasks(): void
    {
        $user = $this->actingAsUser();
        TaskModel::factory()->count(2)->create(['user_id' => $user->id]);
        TaskModel::factory()->count(3)->create();

        $response = $this->getJson(self::TASKS_ENDPOINT);

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    public function test_it_returns_an_empty_list_when_there_are_no_tasks(): void
    {
        $this->actingAsUser();

        $response = $this->getJson(self::TASKS_ENDPOINT);

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function test_it_requires_authentication(): void
    {
        $response = $this->getJson(self::TASKS_ENDPOINT);

        $response->assertStatus(401);
    }

    public function test_an_admin_sees_every_users_tasks(): void
    {
        $this->actingAsUser(UserRole::Admin);
        TaskModel::factory()->count(3)->create();

        $response = $this->getJson(self::TASKS_ENDPOINT);

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_it_filters_by_completed_status(): void
    {
        $user = $this->actingAsUser();
        TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Done task', 'completed' => true]);
        TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Pending task', 'completed' => false]);

        $response = $this->getJson('/api/tasks?completed=true');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Done task']);
    }

    public function test_it_filters_by_an_exact_date(): void
    {
        $user = $this->actingAsUser();
        TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => 'In range',
            'created_at' => '2026-02-15 10:00:00',
        ]);
        TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => 'Out of range',
            'created_at' => '2026-02-16 10:00:00',
        ]);

        $response = $this->getJson('/api/tasks?date=2026-02-15');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'In range']);
    }

    public function test_it_filters_by_a_date_range(): void
    {
        $user = $this->actingAsUser();
        TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => 'Before range',
            'created_at' => self::DATE_JAN,
        ]);
        TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => 'Inside range',
            'created_at' => self::DATE_FEB,
        ]);
        TaskModel::factory()->create([
            'user_id' => $user->id,
            'name' => 'After range',
            'created_at' => '2026-03-01 10:00:00',
        ]);

        $response = $this->getJson('/api/tasks?created_from=2026-01-15&created_to=2026-02-15');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Inside range']);
    }

    public function test_it_filters_by_category(): void
    {
        $user = $this->actingAsUser();
        $work = CategoryModel::factory()->create(['user_id' => $user->id, 'name' => 'Work']);
        $taskWithCategory = TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Work task']);
        $taskWithCategory->categories()->sync([$work->id]);
        TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Uncategorized task']);

        $response = $this->getJson('/api/tasks?category=Work');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Work task']);
    }

    public function test_it_sorts_ascending_by_created_at(): void
    {
        $user = $this->actingAsUser();
        TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Second', 'created_at' => self::DATE_FEB]);
        TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'First', 'created_at' => self::DATE_JAN]);

        $response = $this->getJson('/api/tasks?sort_by=created_at&sort_direction=asc');

        $response->assertOk();
        $response->assertJsonPath('0.title', 'First');
        $response->assertJsonPath('1.title', 'Second');
    }

    public function test_it_sorts_descending_by_updated_at(): void
    {
        $user = $this->actingAsUser();
        $older = TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Older update']);
        $older->update(['updated_at' => self::DATE_JAN]);
        $newer = TaskModel::factory()->create(['user_id' => $user->id, 'name' => 'Newer update']);
        $newer->update(['updated_at' => self::DATE_FEB]);

        $response = $this->getJson('/api/tasks?sort_by=updated_at&sort_direction=desc');

        $response->assertOk();
        $response->assertJsonPath('0.title', 'Newer update');
        $response->assertJsonPath('1.title', 'Older update');
    }

    public function test_it_returns_a_validation_error_for_an_invalid_sort_field(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/tasks?sort_by=invalid');

        $response->assertStatus(422);
    }
}
