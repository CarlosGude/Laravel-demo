<?php

namespace Tests\Unit\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
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
        $work = Category::create(1, 1, 'Work');
        $createdAt = new \DateTimeImmutable('2026-01-01T10:00:00+00:00');
        $updatedAt = new \DateTimeImmutable('2026-01-02T10:00:00+00:00');

        $task = Task::create(
            id: 1,
            uuid: 'b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a',
            userId: 1,
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

        $response = $useCase->execute(new RequestingUser(id: 1, isAdmin: false));

        $this->assertCount(1, $response->tasks);
        $this->assertSame('b6b8a9b0-6a6a-4a6a-8a6a-6a6a6a6a6a6a', $response->tasks[0]->uuid);
        $this->assertSame('Buy milk', $response->tasks[0]->title);
        $this->assertSame('Whole milk, 2 liters', $response->tasks[0]->description);
        $this->assertFalse($response->tasks[0]->completed);
        $this->assertSame(['Work'], $response->tasks[0]->categories);
        $this->assertNull($response->page);
        $this->assertNull($response->perPage);
    }

    public function test_execute_returns_an_empty_array_when_there_are_no_tasks(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $this->assertSame([], $useCase->execute(new RequestingUser(id: 1, isAdmin: false))->tasks);
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

        $this->assertSame([], $useCase->execute(new RequestingUser(id: 1, isAdmin: false), $filterRequest)->tasks);
    }

    public function test_execute_scopes_the_filter_to_the_requesting_user_when_not_admin(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')
            ->withArgs(fn (TaskFilter $filter): bool => $filter->ownerId === 42)
            ->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $useCase->execute(new RequestingUser(id: 42, isAdmin: false));
    }

    public function test_execute_does_not_scope_the_filter_for_an_admin(): void
    {
        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')
            ->withArgs(fn (TaskFilter $filter): bool => $filter->ownerId === null)
            ->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $useCase->execute(new RequestingUser(id: 42, isAdmin: true));
    }

    public function test_execute_paginates_when_a_page_is_requested(): void
    {
        $filterRequest = new TaskFilterRequest(page: 2, perPage: 5);

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')
            ->withArgs(fn (TaskFilter $filter): bool => $filter->page === 2 && $filter->perPage === 5)
            ->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $response = $useCase->execute(new RequestingUser(id: 1, isAdmin: false), $filterRequest);

        $this->assertSame(2, $response->page);
        $this->assertSame(5, $response->perPage);
    }

    public function test_execute_defaults_per_page_to_fifteen_when_only_page_is_given(): void
    {
        $filterRequest = new TaskFilterRequest(page: 1);

        $taskRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $taskRepository->expects('findAll')
            ->withArgs(fn (TaskFilter $filter): bool => $filter->page === 1 && $filter->perPage === 15)
            ->andReturn(new TaskCollection());

        $useCase = new ListAllTaskUseCase($taskRepository);

        $response = $useCase->execute(new RequestingUser(id: 1, isAdmin: false), $filterRequest);

        $this->assertSame(15, $response->perPage);
    }
}
