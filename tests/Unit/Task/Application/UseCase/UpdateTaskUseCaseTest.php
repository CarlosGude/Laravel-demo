<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\UseCase\UpdateTaskUseCase;
use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UpdateTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_loads_the_existing_task_applies_the_changes_and_persists_it(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $existingTask = Task::create(
            id: 1,
            uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
            userId: 1,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $urgent = Category::createNew(1, 'Urgent')->createWithId(2);

        $updatedTask = $existingTask->withChanges(
            name: 'Buy milk and bread',
            description: 'Whole milk and sourdough bread',
            completed: true,
            updatedAt: new \DateTimeImmutable(),
            categories: new CategoryCollection($urgent),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with('b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a')->andReturn($existingTask);
        $taskRepository->expects('update')
            ->with(\Mockery::on(function (Task $task) use ($createdAt) {
                return $task->getId() === 1
                    && $task->getName() === 'Buy milk and bread'
                    && $task->getDescription() === 'Whole milk and sourdough bread'
                    && $task->isCompleted() === true
                    && $task->getCreatedAt() == $createdAt
                    && $task->getCategories()->names() === ['Urgent'];
            }))
            ->andReturn($updatedTask);

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')
            ->with(1, ['Urgent'])
            ->andReturn(new CategoryCollection($urgent));

        $useCase = new UpdateTaskUseCase($taskRepository, $categoryRepository);

        $response = $useCase->execute(
            new TaskRequest(
                uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
                title: 'Buy milk and bread',
                description: 'Whole milk and sourdough bread',
                completed: true,
                categoryNames: ['Urgent'],
            ),
            new RequestingUser(id: 1, isAdmin: false),
        );

        $this->assertSame('b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a', $response->uuid);
        $this->assertSame('Buy milk and bread', $response->title);
        $this->assertTrue($response->completed);
        $this->assertSame(['Urgent'], $response->categories);
    }

    public function test_execute_throws_when_the_request_has_no_id(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->never();

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')->never();

        $useCase = new UpdateTaskUseCase($taskRepository, $categoryRepository);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task id is required');

        $useCase->execute(
            new TaskRequest(
                uuid: null,
                title: 'Buy milk',
                description: 'Whole milk, 2 liters',
                completed: false,
            ),
            new RequestingUser(id: 1, isAdmin: false),
        );
    }

    public function test_execute_throws_task_not_found_when_the_task_does_not_exist(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with('missing-uuid')->andReturn(null);
        $taskRepository->expects('update')->never();

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')->never();

        $useCase = new UpdateTaskUseCase($taskRepository, $categoryRepository);

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute(
            new TaskRequest(
                uuid: 'missing-uuid',
                title: 'Buy milk',
                description: 'Whole milk, 2 liters',
                completed: false,
            ),
            new RequestingUser(id: 1, isAdmin: false),
        );
    }

    public function test_execute_throws_task_not_found_when_a_non_admin_does_not_own_the_task(): void
    {
        $existingTask = Task::create(
            id: 1,
            uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
            userId: 2,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with('b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a')->andReturn($existingTask);
        $taskRepository->expects('update')->never();

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')->never();

        $useCase = new UpdateTaskUseCase($taskRepository, $categoryRepository);

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute(
            new TaskRequest(
                uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
                title: 'Buy milk',
                description: 'Whole milk, 2 liters',
                completed: false,
            ),
            new RequestingUser(id: 1, isAdmin: false),
        );
    }
}
