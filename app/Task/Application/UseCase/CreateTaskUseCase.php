<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Application\Exceptions\TaskWithIdCantBeCreated;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class CreateTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {

    }

    public function execute(TaskRequest $taskRequest, RequestingUser $requestingUser): TaskResponse
    {
        if ($taskRequest->uuid) {
            throw new TaskWithIdCantBeCreated();
        }

        $categories = $this->categoryRepository->findOrCreateByNames(
            $requestingUser->id,
            $taskRequest->categoryNames
        );

        $newTask = Task::createNew(
            userId: $requestingUser->id,
            name: $taskRequest->title,
            description: $taskRequest->description,
            categories: $categories,
        );

        $task = $this->taskRepository->create($newTask);

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
