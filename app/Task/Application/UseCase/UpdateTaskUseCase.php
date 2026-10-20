<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class UpdateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {

    }

    public function execute(TaskRequest $taskRequest, RequestingUser $requestingUser): TaskResponse
    {
        if (!$taskRequest->id) {
            throw new \RuntimeException('Task id is required');
        }
        $existingTask = $this->taskRepository->findById($taskRequest->id);

        if (!$existingTask) {
            throw TaskNotFoundException::withId($taskRequest->id);
        }

        if (!$requestingUser->isAdmin && !$existingTask->isOwnedBy($requestingUser->id)) {
            throw TaskNotFoundException::withId($taskRequest->id);
        }

        $categories = $this->categoryRepository->findOrCreateByNames(
            $existingTask->getUserId(),
            $taskRequest->categoryNames
        );

        $taskToUpdate = $existingTask->withChanges(
            name: $taskRequest->title,
            description: $taskRequest->description,
            completed: $taskRequest->completed,
            updatedAt: new \DateTimeImmutable(),
            categories: $categories,
        );

        $task = $this->taskRepository->update($taskToUpdate);

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
