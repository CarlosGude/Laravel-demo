<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\UseCase\UpdateTaskUseCase;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
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
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: $createdAt,
            updatedAt: $createdAt,
        );

        $updatedTask = $existingTask->withChanges(
            name: 'Buy milk and bread',
            description: 'Whole milk and sourdough bread',
            completed: true,
            updatedAt: new \DateTimeImmutable(),
        );

        $repository = \Mockery::mock(TaskRepositoryInterface::class);
        $repository->expects('findById')->with(1)->andReturn($existingTask);
        $repository->expects('update')
            ->with(\Mockery::on(function (Task $task) use ($createdAt) {
                return $task->getId() === 1
                    && $task->getName() === 'Buy milk and bread'
                    && $task->getDescription() === 'Whole milk and sourdough bread'
                    && $task->isCompleted() === true
                    && $task->getCreatedAt() == $createdAt;
            }))
            ->andReturn($updatedTask);

        $useCase = new UpdateTaskUseCase($repository);

        $response = $useCase->execute(new TaskRequest(
            id: 1,
            title: 'Buy milk and bread',
            description: 'Whole milk and sourdough bread',
            completed: true,
        ));

        $this->assertSame(1, $response->id);
        $this->assertSame('Buy milk and bread', $response->title);
        $this->assertTrue($response->completed);
    }

    public function test_execute_throws_when_the_request_has_no_id(): void
    {
        $repository = \Mockery::mock(TaskRepositoryInterface::class);
        $repository->expects('findById')->never();

        $useCase = new UpdateTaskUseCase($repository);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task id is required');

        $useCase->execute(new TaskRequest(
            id: null,
            title: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
        ));
    }

    public function test_execute_throws_task_not_found_when_the_task_does_not_exist(): void
    {
        $repository = \Mockery::mock(TaskRepositoryInterface::class);
        $repository->expects('findById')->with(99)->andReturn(null);
        $repository->expects('update')->never();

        $useCase = new UpdateTaskUseCase($repository);

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute(new TaskRequest(
            id: 99,
            title: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
        ));
    }
}
