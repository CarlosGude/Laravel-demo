<?php

namespace App\Task\Application\UseCase;
use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Application\Exceptions\TaskWithIdCantBeCreated;
use App\Task\Domain\Entity\Task;
use App\Task\Domain\Repository\TaskRepositoryInterface;


final readonly class CreateTaskUseCase
{

    public function __construct(
        private TaskRepositoryInterface $taskRepository
    )
    {

    }

    public function execute(TaskRequest $taskRequest):TaskResponse
    {
        if ($taskRequest->id) {
            throw new TaskWithIdCantBeCreated();
        }
        $newTask = Task::createNew(
            name: $taskRequest->title,
            description: $taskRequest->description,
        );

        $task = $this->taskRepository->create($newTask);

        return new TaskResponse(
            id: $task->getId(),
            title: $task->getName(),
            description: $task->getDescription(),
            completed: $task->isCompleted(),
            createdAt: $task->getCreatedAt(),
            updatedAt: $task->getUpdatedAt()
        );
    }
}
