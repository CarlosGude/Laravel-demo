<?php

namespace Tests\Feature\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Models\TaskModel;
use App\Task\Infrastructure\Repository\MySqlTaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlTaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MySqlTaskRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MySqlTaskRepository();
    }

    public function test_create_persists_the_task_and_returns_it_with_an_id(): void
    {
        $task = $this->repository->create(Task::createNew('Buy milk', 'Whole milk, 2 liters'));

        $this->assertNotNull($task->getId());
        $this->assertSame('Buy milk', $task->getName());
        $this->assertSame('Whole milk, 2 liters', $task->getDescription());
        $this->assertFalse($task->isCompleted());
        $this->assertDatabaseHas('tasks', [
            'id' => $task->getId(),
            'name' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);
    }

    public function test_find_by_id_returns_null_when_the_task_does_not_exist(): void
    {
        $this->assertNull($this->repository->findById(999));
    }

    public function test_find_by_id_returns_the_matching_task(): void
    {
        $model = TaskModel::factory()->create(['name' => 'Buy milk']);

        $task = $this->repository->findById($model->id);

        $this->assertNotNull($task);
        $this->assertSame($model->id, $task->getId());
        $this->assertSame('Buy milk', $task->getName());
    }

    public function test_find_all_returns_every_persisted_task(): void
    {
        TaskModel::factory()->count(3)->create();

        $tasks = $this->repository->findAll();

        $this->assertCount(3, $tasks);
        $this->assertContainsOnlyInstancesOf(Task::class, $tasks);
    }

    public function test_update_persists_the_changes_on_an_existing_task(): void
    {
        $model = TaskModel::factory()->create([
            'name' => 'Buy milk',
            'description' => 'Whole milk, 2 liters',
            'completed' => false,
        ]);
        $existingTask = $model->toEntity();

        $updatedTask = $existingTask->withChanges(
            name: 'Buy milk and bread',
            description: 'Whole milk and sourdough bread',
            completed: true,
            updatedAt: new \DateTimeImmutable(),
        );

        $task = $this->repository->update($updatedTask);

        $this->assertSame('Buy milk and bread', $task->getName());
        $this->assertTrue($task->isCompleted());
        $this->assertDatabaseHas('tasks', [
            'id' => $model->id,
            'name' => 'Buy milk and bread',
            'description' => 'Whole milk and sourdough bread',
            'completed' => true,
        ]);
    }

    public function test_update_throws_task_not_found_when_the_task_does_not_exist(): void
    {
        $this->expectException(TaskNotFoundException::class);

        $this->repository->update(Task::create(
            id: 999,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        ));
    }

    public function test_create_persists_the_categories_on_the_pivot_table(): void
    {
        $work = CategoryModel::factory()->create(['name' => 'Work']);

        $task = $this->repository->create(
            Task::createNew('Buy milk', 'Whole milk, 2 liters', new CategoryCollection($work->toEntity()))
        );

        $this->assertSame(['Work'], $task->getCategories()->names());
        $this->assertDatabaseHas('category_task', [
            'task_id' => $task->getId(),
            'category_id' => $work->id,
        ]);
    }

    public function test_update_replaces_the_existing_categories(): void
    {
        $work = CategoryModel::factory()->create(['name' => 'Work']);
        $urgent = CategoryModel::factory()->create(['name' => 'Urgent']);

        $model = TaskModel::factory()->create();
        $model->categories()->sync([$work->id]);
        $existingTask = $model->toEntity();

        $updatedTask = $existingTask->withChanges(
            name: $existingTask->getName(),
            description: $existingTask->getDescription(),
            completed: $existingTask->isCompleted(),
            updatedAt: new \DateTimeImmutable(),
            categories: new CategoryCollection($urgent->toEntity()),
        );

        $task = $this->repository->update($updatedTask);

        $this->assertSame(['Urgent'], $task->getCategories()->names());
        $this->assertDatabaseMissing('category_task', [
            'task_id' => $task->getId(),
            'category_id' => $work->id,
        ]);
    }
}
