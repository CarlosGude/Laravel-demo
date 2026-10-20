<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class ListOneTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {

    }

    public function execute(int $id): ?TaskResponse
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            return null;
        }

        return new TaskResponse(
            id: $task->getPersistedId(),
            title: $task->getName(),
            description: $task->getDescription(),
            completed: $task->isCompleted(),
            createdAt: $task->getCreatedAt(),
            updatedAt: $task->getUpdatedAt(),
            categories: $task->getCategories()->names(),
        );
    }
}
