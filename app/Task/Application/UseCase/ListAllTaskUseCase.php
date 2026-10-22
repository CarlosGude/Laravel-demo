<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Request\TaskFilterRequest;
use App\Task\Application\Dto\Response\TaskListResponse;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\SortDirection;
use App\Task\Domain\Repository\TaskFilter;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Domain\Repository\TaskSortField;

final readonly class ListAllTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {

    }

    public function execute(RequestingUser $requestingUser, ?TaskFilterRequest $filterRequest = null): TaskListResponse
    {
        $filterRequest ??= new TaskFilterRequest();

        $perPage = $filterRequest->page !== null ? ($filterRequest->perPage ?? 15) : null;

        $filter = new TaskFilter(
            ownerId: $requestingUser->isAdmin ? null : $requestingUser->id,
            completed: $filterRequest->completed,
            createdFrom: $filterRequest->createdFrom,
            createdTo: $filterRequest->createdTo,
            categoryName: $filterRequest->categoryName,
            sortBy: TaskSortField::from($filterRequest->sortBy),
            sortDirection: SortDirection::from($filterRequest->sortDirection),
            page: $filterRequest->page,
            perPage: $perPage,
        );

        $tasks = $this->taskRepository->findAll($filter);

        $responses = array_map(
            fn (Task $task): TaskResponse => new TaskResponse(
                uuid: $task->getUuid(),
                title: $task->getName(),
                description: $task->getDescription(),
                completed: $task->isCompleted(),
                createdAt: $task->getCreatedAt(),
                updatedAt: $task->getUpdatedAt(),
                categories: $task->getCategories()->names(),
            ),
            $tasks->toArray()
        );

        return new TaskListResponse($responses, $filterRequest->page, $perPage);
    }
}
