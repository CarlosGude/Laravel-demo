<?php

namespace App\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Support\Collection;

final class MySqlTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        return TaskModel::query()->find($id)?->toEntity();
    }

    /**
     * @return Collection<int, Task>
     */
    public function findAll(): Collection
    {
        return TaskModel::query()->get()->map(fn (TaskModel $model): Task => $model->toEntity());
    }

    public function create(Task $task): Task
    {
        $model = new TaskModel();
        $model->fillFromEntity($task)->save();

        return $model->toEntity();
    }

    public function update(Task $task): Task
    {
        $model = TaskModel::query()->find($task->getId());

        if (!$model) {
            throw TaskNotFoundException::withId($task->getPersistedId());
        }

        $model->fillFromEntity($task)->save();

        return $model->toEntity();
    }
}
