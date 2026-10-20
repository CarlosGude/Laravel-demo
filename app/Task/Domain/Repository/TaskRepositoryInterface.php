<?php

namespace App\Task\Domain\Repository;

use App\Task\Domain\Entity\Task;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    /**
     * @return Collection<int, Task>
     */
    public function findAll(): Collection;

    public function findById(int $id): ?Task;

    public function create(Task $task): Task;

    public function update(Task $task): Task;
}
