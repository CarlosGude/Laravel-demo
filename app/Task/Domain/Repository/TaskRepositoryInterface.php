<?php

namespace App\Task\Domain\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;

interface TaskRepositoryInterface
{
    public function findAll(?TaskFilter $filter = null): TaskCollection;

    public function findByUuid(string $uuid): ?Task;

    public function create(Task $task): Task;

    public function update(Task $task): Task;
}
