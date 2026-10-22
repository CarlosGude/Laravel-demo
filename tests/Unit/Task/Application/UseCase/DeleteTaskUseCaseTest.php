<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\UseCase\DeleteTaskUseCase;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class DeleteTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const TASK_UUID = 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a';

    public function test_execute_loads_the_existing_task_and_deletes_it(): void
    {
        $existingTask = Task::create(
            id: 1,
            uuid: self::TASK_UUID,
            userId: 1,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with(self::TASK_UUID)->andReturn($existingTask);
        $taskRepository->expects('delete')->with($existingTask);

        $useCase = new DeleteTaskUseCase($taskRepository);

        $useCase->execute(self::TASK_UUID, new RequestingUser(id: 1, isAdmin: false));
    }

    public function test_execute_throws_task_not_found_when_the_task_does_not_exist(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with('missing-uuid')->andReturn(null);
        $taskRepository->expects('delete')->never();

        $useCase = new DeleteTaskUseCase($taskRepository);

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute('missing-uuid', new RequestingUser(id: 1, isAdmin: false));
    }

    public function test_execute_throws_task_not_found_when_a_non_admin_does_not_own_the_task(): void
    {
        $existingTask = Task::create(
            id: 1,
            uuid: self::TASK_UUID,
            userId: 2,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with(self::TASK_UUID)->andReturn($existingTask);
        $taskRepository->expects('delete')->never();

        $useCase = new DeleteTaskUseCase($taskRepository);

        $this->expectException(TaskNotFoundException::class);

        $useCase->execute(self::TASK_UUID, new RequestingUser(id: 1, isAdmin: false));
    }

    public function test_execute_allows_an_admin_to_delete_another_users_task(): void
    {
        $existingTask = Task::create(
            id: 1,
            uuid: self::TASK_UUID,
            userId: 2,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findByUuid')->with(self::TASK_UUID)->andReturn($existingTask);
        $taskRepository->expects('delete')->with($existingTask);

        $useCase = new DeleteTaskUseCase($taskRepository);

        $useCase->execute(self::TASK_UUID, new RequestingUser(id: 1, isAdmin: true));
    }
}
