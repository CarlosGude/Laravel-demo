<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Response\TaskResponse;

final readonly class ListOneTaskPortModel
{
    private function __construct(
        public int $id,
    )
    {
    }

    public static function fromHttpRequest(int $id): self
    {
        return new self(id: $id);
    }

    public static function toHttpResponse(?TaskResponse $task): ?array
    {
        if ($task === null) {
            return null;
        }

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'completed' => $task->completed,
            'created_at' => $task->createdAt?->format(DATE_ATOM),
            'updated_at' => $task->updatedAt?->format(DATE_ATOM),
        ];
    }
}
