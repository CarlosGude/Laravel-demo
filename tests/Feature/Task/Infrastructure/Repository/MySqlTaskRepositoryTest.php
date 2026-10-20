<?php

namespace Tests\Feature\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\SortDirection;
use App\Task\Domain\Repository\TaskFilter;
use App\Task\Domain\Repository\TaskSortField;
use App\Task\Infrastructure\Models\CategoryModel;
use App\Task\Infrastructure\Models\TaskModel;
use App\Task\Infrastructure\Repository\MySqlTaskRepository;
use App\User\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlTaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private const TASK_TITLE = 'Buy milk';
    private const TASK_DESCRIPTION = 'Whole milk, 2 liters';
    private const UPDATED_TASK_TITLE = 'Buy milk and bread';
    private const DATE_JAN = '2026-01-01 10:00:00';
    private const DATE_FEB = '2026-02-01 10:00:00';

    private MySqlTaskRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new MySqlTaskRepository();
    }

    public function test_create_persists_the_task_and_returns_it_with_an_id(): void
    {
        $user = UserModel::factory()->create();

        $task = $this->repository->create(Task::createNew($user->id, self::TASK_TITLE, self::TASK_DESCRIPTION));

        $this->assertNotNull($task->getId());
        $this->assertSame(self::TASK_TITLE, $task->getName());
        $this->assertSame(self::TASK_DESCRIPTION, $task->getDescription());
        $this->assertFalse($task->isCompleted());
        $this->assertDatabaseHas('tasks', [
            'id' => $task->getId(),
            'name' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);
    }

    public function test_find_by_uuid_returns_null_when_the_task_does_not_exist(): void
    {
        $this->assertNull($this->repository->findByUuid('missing-uuid'));
    }

    public function test_find_by_uuid_returns_the_matching_task(): void
    {
        $model = TaskModel::factory()->create(['name' => self::TASK_TITLE]);

        $task = $this->repository->findByUuid($model->uuid);

        $this->assertNotNull($task);
        $this->assertSame($model->id, $task->getId());
        $this->assertSame($model->uuid, $task->getUuid());
        $this->assertSame(self::TASK_TITLE, $task->getName());
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
            'name' => self::TASK_TITLE,
            'description' => self::TASK_DESCRIPTION,
            'completed' => false,
        ]);
        $existingTask = $model->toEntity();

        $updatedTask = $existingTask->withChanges(
            name: self::UPDATED_TASK_TITLE,
            description: 'Whole milk and sourdough bread',
            completed: true,
            updatedAt: new \DateTimeImmutable(),
        );

        $task = $this->repository->update($updatedTask);

        $this->assertSame(self::UPDATED_TASK_TITLE, $task->getName());
        $this->assertTrue($task->isCompleted());
        $this->assertDatabaseHas('tasks', [
            'id' => $model->id,
            'name' => self::UPDATED_TASK_TITLE,
            'description' => 'Whole milk and sourdough bread',
            'completed' => true,
        ]);
    }

    public function test_update_throws_task_not_found_when_the_task_does_not_exist(): void
    {
        $this->expectException(TaskNotFoundException::class);

        $user = UserModel::factory()->create();

        $this->repository->update(Task::create(
            id: 999,
            uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
            userId: $user->id,
            name: self::TASK_TITLE,
            description: self::TASK_DESCRIPTION,
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        ));
    }

    public function test_create_persists_the_categories_on_the_pivot_table(): void
    {
        $work = CategoryModel::factory()->create(['name' => 'Work']);

        $task = $this->repository->create(
            Task::createNew(
                $work->user_id,
                self::TASK_TITLE,
                self::TASK_DESCRIPTION,
                new CategoryCollection($work->toEntity())
            )
        );

        $this->assertSame(['Work'], $task->getCategories()->names());
        $this->assertDatabaseHas('category_task', [
            'task_id' => $task->getId(),
            'category_id' => $work->id,
        ]);
    }

    public function test_find_all_filters_by_completed_status(): void
    {
        TaskModel::factory()->create(['completed' => true]);
        TaskModel::factory()->create(['completed' => false]);

        $tasks = $this->repository->findAll(new TaskFilter(completed: true));

        $this->assertCount(1, $tasks);
        $this->assertTrue($tasks->toArray()[0]->isCompleted());
    }

    public function test_find_all_filters_by_created_at_range(): void
    {
        TaskModel::factory()->create(['created_at' => self::DATE_JAN]);
        $inRange = TaskModel::factory()->create(['created_at' => self::DATE_FEB]);
        TaskModel::factory()->create(['created_at' => '2026-03-01 10:00:00']);

        $tasks = $this->repository->findAll(new TaskFilter(
            createdFrom: new \DateTimeImmutable('2026-01-15'),
            createdTo: new \DateTimeImmutable('2026-02-15'),
        ));

        $this->assertCount(1, $tasks);
        $this->assertSame($inRange->id, $tasks->toArray()[0]->getId());
    }

    public function test_find_all_filters_by_category_name(): void
    {
        $work = CategoryModel::factory()->create(['name' => 'Work']);
        $taskWithCategory = TaskModel::factory()->create();
        $taskWithCategory->categories()->sync([$work->id]);
        TaskModel::factory()->create();

        $tasks = $this->repository->findAll(new TaskFilter(categoryName: 'Work'));

        $this->assertCount(1, $tasks);
        $this->assertSame($taskWithCategory->id, $tasks->toArray()[0]->getId());
    }

    public function test_find_all_sorts_ascending_by_created_at(): void
    {
        $second = TaskModel::factory()->create(['created_at' => self::DATE_FEB]);
        $first = TaskModel::factory()->create(['created_at' => self::DATE_JAN]);

        $tasks = $this->repository->findAll(new TaskFilter(
            sortBy: TaskSortField::CreatedAt,
            sortDirection: SortDirection::Ascending,
        ))->toArray();

        $this->assertSame($first->id, $tasks[0]->getId());
        $this->assertSame($second->id, $tasks[1]->getId());
    }

    public function test_find_all_sorts_descending_by_updated_at(): void
    {
        $older = TaskModel::factory()->create();
        $older->update(['updated_at' => self::DATE_JAN]);
        $newer = TaskModel::factory()->create();
        $newer->update(['updated_at' => self::DATE_FEB]);

        $tasks = $this->repository->findAll(new TaskFilter(
            sortBy: TaskSortField::UpdatedAt,
            sortDirection: SortDirection::Descending,
        ))->toArray();

        $this->assertSame($newer->id, $tasks[0]->getId());
        $this->assertSame($older->id, $tasks[1]->getId());
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
