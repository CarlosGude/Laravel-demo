<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Exceptions\TaskWithIdCantBeCreated;
use App\Task\Application\UseCase\CreateTaskUseCase;
use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class CreateTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_creates_a_new_task_and_returns_a_response(): void
    {
        $work = Category::createNew(1, 'Work')->createWithId(1);
        $persistedTask = Task::createNew(1, 'Buy milk', 'Whole milk, 2 liters', new CategoryCollection($work))
            ->createWithId(1);

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('create')
            ->with(\Mockery::on(function (Task $task) {
                return $task->getId() === null
                    && $task->getUserId() === 1
                    && $task->getName() === 'Buy milk'
                    && $task->getDescription() === 'Whole milk, 2 liters'
                    && $task->isCompleted() === false
                    && $task->getCategories()->names() === ['Work'];
            }))
            ->andReturn($persistedTask);

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')
            ->with(1, ['Work'])
            ->andReturn(new CategoryCollection($work));

        $useCase = new CreateTaskUseCase($taskRepository, $categoryRepository);

        $response = $useCase->execute(
            new TaskRequest(
                uuid: null,
                title: 'Buy milk',
                description: 'Whole milk, 2 liters',
                completed: false,
                categoryNames: ['Work'],
            ),
            new RequestingUser(id: 1, isAdmin: false),
        );

        $this->assertSame($persistedTask->getUuid(), $response->uuid);
        $this->assertSame('Buy milk', $response->title);
        $this->assertSame('Whole milk, 2 liters', $response->description);
        $this->assertFalse($response->completed);
        $this->assertSame(['Work'], $response->categories);
    }

    public function test_execute_throws_when_the_request_already_has_an_id(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('create')->never();

        $categoryRepository = \Mockery::mock(CategoryRepositoryInterface::class);
        $categoryRepository->expects('findOrCreateByNames')->never();

        $useCase = new CreateTaskUseCase($taskRepository, $categoryRepository);

        $this->expectException(TaskWithIdCantBeCreated::class);

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
