<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\TaskFilterRequest;
use App\Task\Application\UseCase\ListAllTaskUseCase;
use App\Task\Domain\Entity\Category;
use App\Task\Domain\Entity\CategoryCollection;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;
use App\Task\Domain\Repository\SortDirection;
use App\Task\Domain\Repository\TaskFilter;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Domain\Repository\TaskSortField;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ListAllTaskUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_returns_a_response_for_every_persisted_task(): void
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
        $taskRepository->expects('findAll')->andReturn(new TaskCollection($task));

        $useCase = new ListAllTaskUseCase($taskRepository);

        $responses = $useCase->execute();

        $this->assertCount(1, $responses);
        $this->assertSame(1, $responses[0]->id);
        $this->assertSame('Buy milk', $responses[0]->title);
        $this->assertSame('Whole milk, 2 liters', $responses[0]->description);
        $this->assertFalse($responses[0]->completed);
        $this->assertSame(['Work'], $responses[0]->categories);
    }

    public function test_execute_returns_an_empty_array_when_there_are_no_tasks(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $this->assertSame([], $useCase->execute());
    }

    public function test_execute_translates_the_filter_request_into_a_domain_filter(): void
    {
        $filterRequest = new TaskFilterRequest(
            completed: true,
            categoryName: 'Work',
            sortBy: 'updated_at',
            sortDirection: 'asc',
        );

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')
            ->withArgs(function (TaskFilter $filter): bool {
                return $filter->completed === true
                    && $filter->categoryName === 'Work'
                    && $filter->sortBy === TaskSortField::UpdatedAt
                    && $filter->sortDirection === SortDirection::Ascending;
            })
            ->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $this->assertSame([], $useCase->execute($filterRequest));
    }
}
