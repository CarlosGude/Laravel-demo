<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Response\TaskResponse;

final readonly class ListOneTaskPortModel
{
    private function __construct(
        public string $id,
    ) {
    }

    public static function fromHttpRequest(string $id): self
    {
        return new self(id: $id);
    }

    /**
     * @return ?array{id: string, title: string, description: string, completed: bool, created_at: ?string, updated_at: ?string, categories: array<int, string>}
     */
    public static function toHttpResponse(?TaskResponse $task): ?array
    {
        if ($task === null) {
            return null;
        }

        return [
            'id' => $task->uuid,
            'title' => $task->title,
            'description' => $task->description,
            'completed' => $task->completed,
            'created_at' => $task->createdAt?->format(DATE_ATOM),
            'updated_at' => $task->updatedAt?->format(DATE_ATOM),
            'categories' => $task->categories,
        ];
    }
}
