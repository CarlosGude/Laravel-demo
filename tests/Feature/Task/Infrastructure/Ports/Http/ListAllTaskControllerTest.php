<?php

namespace Tests\Feature\Task\Infrastructure\Ports\Http;

use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_every_persisted_task(): void
    {
        TaskModel::factory()->count(2)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(2);
    }

    public function test_it_returns_an_empty_list_when_there_are_no_tasks(): void
    {
        $response = $this->getJson('/api/tasks');

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function test_it_filters_by_completed_status(): void
    {
        TaskModel::factory()->create(['name' => 'Done task', 'completed' => true]);
        TaskModel::factory()->create(['name' => 'Pending task', 'completed' => false]);

        $response = $this->getJson('/api/tasks?completed=true');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Done task']);
    }

    public function test_it_filters_by_an_exact_date(): void
    {
        TaskModel::factory()->create(['name' => 'In range', 'created_at' => '2026-02-15 10:00:00']);
        TaskModel::factory()->create(['name' => 'Out of range', 'created_at' => '2026-02-16 10:00:00']);

        $response = $this->getJson('/api/tasks?date=2026-02-15');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'In range']);
    }

    public function test_it_filters_by_a_date_range(): void
    {
        TaskModel::factory()->create(['name' => 'Before range', 'created_at' => '2026-01-01 10:00:00']);
        TaskModel::factory()->create(['name' => 'Inside range', 'created_at' => '2026-02-01 10:00:00']);
        TaskModel::factory()->create(['name' => 'After range', 'created_at' => '2026-03-01 10:00:00']);

        $response = $this->getJson('/api/tasks?created_from=2026-01-15&created_to=2026-02-15');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Inside range']);
    }

    public function test_it_filters_by_category(): void
    {
        $work = CategoryModel::factory()->create(['name' => 'Work']);
        $taskWithCategory = TaskModel::factory()->create(['name' => 'Work task']);
        $taskWithCategory->categories()->sync([$work->id]);
        TaskModel::factory()->create(['name' => 'Uncategorized task']);

        $response = $this->getJson('/api/tasks?category=Work');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Work task']);
    }

    public function test_it_sorts_ascending_by_created_at(): void
    {
        TaskModel::factory()->create(['name' => 'Second', 'created_at' => '2026-02-01 10:00:00']);
        TaskModel::factory()->create(['name' => 'First', 'created_at' => '2026-01-01 10:00:00']);

        $response = $this->getJson('/api/tasks?sort_by=created_at&sort_direction=asc');

        $response->assertOk();
        $response->assertJsonPath('0.title', 'First');
        $response->assertJsonPath('1.title', 'Second');
    }

    public function test_it_sorts_descending_by_updated_at(): void
    {
        $older = TaskModel::factory()->create(['name' => 'Older update']);
        $older->update(['updated_at' => '2026-01-01 10:00:00']);
        $newer = TaskModel::factory()->create(['name' => 'Newer update']);
        $newer->update(['updated_at' => '2026-02-01 10:00:00']);

        $response = $this->getJson('/api/tasks?sort_by=updated_at&sort_direction=desc');

        $response->assertOk();
        $response->assertJsonPath('0.title', 'Newer update');
        $response->assertJsonPath('1.title', 'Older update');
    }

    public function test_it_returns_a_validation_error_for_an_invalid_sort_field(): void
    {
        $response = $this->getJson('/api/tasks?sort_by=invalid');

        $response->assertStatus(422);
    }
}
