<?php

namespace App\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Models\TaskModel;

final class MySqlTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        return TaskModel::query()->with('categories')->find($id)?->toEntity();
    }

    public function findAll(): TaskCollection
    {
        return TaskCollection::fromArray(
            TaskModel::query()->with('categories')->get()->map(fn (TaskModel $model): Task => $model->toEntity())->all()
        );
    }

    public function create(Task $task): Task
    {
        $model = new TaskModel();
        $model->fillFromEntity($task)->save();
        $model->categories()->sync($task->getCategories()->ids());

        return $model->toEntity();
    }

    public function update(Task $task): Task
    {
        $model = TaskModel::query()->find($task->getId());

        if (!$model) {
            throw TaskNotFoundException::withId($task->getPersistedId());
        }

        $model->fillFromEntity($task)->save();
        $model->categories()->sync($task->getCategories()->ids());

        return $model->toEntity();
    }
}
