<?php

namespace App\Task\Domain\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;

interface TaskRepositoryInterface
{
    public function findAll(): TaskCollection;

    public function findById(int $id): ?Task;

    public function create(Task $task): Task;

    public function update(Task $task): Task;
}
