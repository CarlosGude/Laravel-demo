<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class ListAllTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {

    }

    /**
     * @return array<int, TaskResponse>
     */
    public function execute(): array
    {
        $tasks = $this->taskRepository->findAll();

        return array_map(
            fn (Task $task): TaskResponse => new TaskResponse(
                id: $task->getPersistedId(),
                title: $task->getName(),
                description: $task->getDescription(),
                completed: $task->isCompleted(),
                createdAt: $task->getCreatedAt(),
                updatedAt: $task->getUpdatedAt()
            ),
            $tasks->toArray()
        );
    }
}
