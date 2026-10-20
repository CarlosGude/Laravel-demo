<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Exceptions\TaskWithIdCantBeCreated;
use App\Task\Application\UseCase\CreateTaskUseCase;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class CreateTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_creates_a_new_task_and_returns_a_response(): void
    {
        $persistedTask = Task::createNew('Buy milk', 'Whole milk, 2 liters')->createWithId(1);

        $repository = \Mockery::mock(TaskRepositoryInterface::class);
        $repository->expects('create')
            ->with(\Mockery::on(function (Task $task) {
                return $task->getId() === null
                    && $task->getName() === 'Buy milk'
                    && $task->getDescription() === 'Whole milk, 2 liters'
                    && $task->isCompleted() === false;
            }))
            ->andReturn($persistedTask);

        $useCase = new CreateTaskUseCase($repository);

        $response = $useCase->execute(new TaskRequest(
            id: null,
            title: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
        ));

        $this->assertSame(1, $response->id);
        $this->assertSame('Buy milk', $response->title);
        $this->assertSame('Whole milk, 2 liters', $response->description);
        $this->assertFalse($response->completed);
    }

    public function test_execute_throws_when_the_request_already_has_an_id(): void
    {
        $repository = \Mockery::mock(TaskRepositoryInterface::class);
        $repository->expects('create')->never();

        $useCase = new CreateTaskUseCase($repository);

        $this->expectException(TaskWithIdCantBeCreated::class);

        $useCase->execute(new TaskRequest(
            id: 1,
            title: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
        ));
    }
}
