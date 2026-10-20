<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\UseCase\ListOneTaskUseCase;
use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ListOneTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_returns_a_response_when_the_task_exists(): void
    {
        $work = Category::create(1, 'Work');
        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $updatedAt = new \DateTimeImmutable('2026-01-02T10:00:00+00:00');

        $task = Task::create(
            id: 1,
            name: 'Buy milk',
            description: 'Whole milk, 2 liters',
            completed: false,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            categories: new CategoryCollection($work),
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findById')->with(1)->andReturn($task);

        $useCase = new ListOneTaskUseCase($taskRepository);

        $response = $useCase->execute(1);

        $this->assertNotNull($response);
        $this->assertSame(1, $response->id);
        $this->assertSame('Buy milk', $response->title);
        $this->assertSame(['Work'], $response->categories);
    }

    public function test_execute_returns_null_when_the_task_does_not_exist(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findById')->with(999)->andReturn(null);

        $useCase = new ListOneTaskUseCase($taskRepository);

        $this->assertNull($useCase->execute(999));
    }
}
