<?php

namespace Tests\Feature\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
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
}
