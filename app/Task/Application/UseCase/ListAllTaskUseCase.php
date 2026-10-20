<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\TaskFilterRequest;
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

    /**
     * @return array<int, TaskResponse>
     */
    public function execute(?TaskFilterRequest $filterRequest = null): array
    {
        $filterRequest ??= new TaskFilterRequest();

        $filter = new TaskFilter(
            completed: $filterRequest->completed,
            createdFrom: $filterRequest->createdFrom,
            createdTo: $filterRequest->createdTo,
            categoryName: $filterRequest->categoryName,
            sortBy: TaskSortField::from($filterRequest->sortBy),
            sortDirection: SortDirection::from($filterRequest->sortDirection),
        );

        $tasks = $this->taskRepository->findAll($filter);

        return array_map(
            fn (Task $task): TaskResponse => new TaskResponse(
                id: $task->getPersistedId(),
                title: $task->getName(),
                description: $task->getDescription(),
                completed: $task->isCompleted(),
                createdAt: $task->getCreatedAt(),
                updatedAt: $task->getUpdatedAt(),
                categories: $task->getCategories()->names(),
            ),
            $tasks->toArray()
        );
    }
}
