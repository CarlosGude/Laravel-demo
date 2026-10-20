<?php

namespace App\Task\Infrastructure\Models;

use App\Task\Domain\Entity\Task;
use Database\Factories\TaskFactory;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskModel extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = ['name', 'description', 'completed', 'created_at', 'updated_at'];

    protected function casts(): array
    {
        return ['completed' => 'boolean'];
    }

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function fillFromEntity(Task $task): static
    {
        return $this->fill([
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'completed' => $task->isCompleted(),
            'created_at' => $task->getCreatedAt(),
            'updated_at' => $task->getUpdatedAt(),
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
