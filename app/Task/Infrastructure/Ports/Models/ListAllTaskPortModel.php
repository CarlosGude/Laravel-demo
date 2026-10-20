<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Response\TaskResponse;

final readonly class ListAllTaskPortModel
{
    /**
     * @param TaskResponse[] $tasks
     * @return array<int, array{id: int, title: string, description: string, completed: bool, created_at: ?string, updated_at: ?string, categories: array<int, string>}>
     */
    public static function toHttpResponse(array $tasks): array
    {
        return array_map(
            static fn (TaskResponse $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->completed,
                'created_at' => $task->createdAt?->format(DATE_ATOM),
                'updated_at' => $task->updatedAt?->format(DATE_ATOM),
                'categories' => $task->categories,
            ],
            $tasks
        );
    }
}
