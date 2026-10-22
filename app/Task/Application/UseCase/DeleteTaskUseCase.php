<?php

namespace App\Task\Application\UseCase;

use App\Task\Application\Dto\Request\RequestingUser;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\TaskRepositoryInterface;

final readonly class DeleteTaskUseCase
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function execute(string $uuid, RequestingUser $requestingUser): void
    {
        $existingTask = $this->taskRepository->findByUuid($uuid);

        if (!$existingTask) {
            throw TaskNotFoundException::withUuid($uuid);
        }

        if (!$requestingUser->isAdmin && !$existingTask->isOwnedBy($requestingUser->id)) {
            throw TaskNotFoundException::withUuid($uuid);
        }

        $this->taskRepository->delete($existingTask);
    }
}
