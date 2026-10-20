<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class ListOneTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {

    }

    public function execute(string $uuid, RequestingUser $requestingUser): ?TaskResponse
    {
        $task = $this->taskRepository->findByUuid($uuid);

        if (!$task) {
            return null;
        }

        if (!$requestingUser->isAdmin && !$task->isOwnedBy($requestingUser->id)) {
            return null;
        }

        return new TaskResponse(
            uuid: $task->getUuid(),
            title: $task->getName(),
            description: $task->getDescription(),
            completed: $task->isCompleted(),
            createdAt: $task->getCreatedAt(),
            updatedAt: $task->getUpdatedAt(),
            categories: $task->getCategories()->names(),
        );
    }
}
