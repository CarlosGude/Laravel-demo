<?php

namespace App\Task\Application\UseCase;

use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Application\Dto\Response\TaskResponse;

final readonly class ListAllTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    )
    {

    }

    public function execute(): array
    {
        $tasks = $this->taskRepository->findAll();

        return $tasks->map(
            fn ($task) => new TaskResponse(
                id: $task->getId(),
                title: $task->getName(),
                description: $task->getDescription(),
                completed: $task->isCompleted(),
                createdAt: $task->getCreatedAt(),
                updatedAt: $task->getUpdatedAt()
            )
        )->all();
    }
}
