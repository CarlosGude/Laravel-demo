<?php

namespace App\Task\Infrastructure\Models;

use App\Task\Domain\Entity\Task;
use Database\Factories\TaskFactory;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TaskModel extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        TaskColumn::Name->value,
        TaskColumn::Description->value,
        TaskColumn::Completed->value,
        TaskColumn::CreatedAt->value,
        TaskColumn::UpdatedAt->value,
    ];

    protected function casts(): array
    {
        return [TaskColumn::Completed->value => 'boolean'];
    }

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function fillFromEntity(Task $task): static
    {
        return $this->fill([
            TaskColumn::Name->value => $task->getName(),
            TaskColumn::Description->value => $task->getDescription(),
            TaskColumn::Completed->value => $task->isCompleted(),
            TaskColumn::CreatedAt->value => $task->getCreatedAt(),
            TaskColumn::UpdatedAt->value => $task->getUpdatedAt(),
        ]);
    }

    public function toEntity(): Task
    {
        return Task::create(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            completed: $this->completed,
            createdAt: DateTimeImmutable::createFromInterface($this->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($this->updated_at),
        );
    }
}
