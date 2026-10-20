<?php

namespace App\Task\Infrastructure\Repository;

use App\Task\Domain\Entity\Task;
use App\Task\Domain\Entity\TaskCollection;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Domain\Repository\TaskFilter;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Models\CategoryColumn;
use App\Task\Infrastructure\Models\TaskColumn;
use App\Task\Infrastructure\Models\TaskModel;
use Illuminate\Database\Eloquent\Builder;

final class MySqlTaskRepository implements TaskRepositoryInterface
{
    public function findById(int $id): ?Task
    {
        return TaskModel::query()->with('categories')->find($id)?->toEntity();
    }

    public function findAll(?TaskFilter $filter = null): TaskCollection
    {
        $filter ??= new TaskFilter();

        $query = TaskModel::query()->with('categories');

        if ($filter->ownerId !== null) {
            $query->where(TaskColumn::UserId->value, $filter->ownerId);
        }

        if ($filter->completed !== null) {
            $query->where(TaskColumn::Completed->value, $filter->completed);
        }

        if ($filter->createdFrom !== null) {
            $query->where(TaskColumn::CreatedAt->value, '>=', $filter->createdFrom);
        }

        if ($filter->createdTo !== null) {
            $query->where(TaskColumn::CreatedAt->value, '<=', $filter->createdTo);
        }

        if ($filter->categoryName !== null) {
            $query->whereHas(
                'categories',
                fn (Builder $query): Builder => $query->where(CategoryColumn::Name->value, $filter->categoryName)
            );
        }

        $query->orderBy($filter->sortBy->value, $filter->sortDirection->value);

        return TaskCollection::fromArray(
            $query->get()->map(fn (TaskModel $model): Task => $model->toEntity())->all()
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
