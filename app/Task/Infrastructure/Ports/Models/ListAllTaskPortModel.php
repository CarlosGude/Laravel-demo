<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Response\TaskResponse;

final readonly class ListAllTaskPortModel
{
    /**
     * @param TaskResponse[] $tasks
     */
    public static function toHttpResponse(array $tasks): array
    {
        return array_map(
            static fn (TaskResponse $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->completed,
                'created_at' => $task->createdAt?->format(DATE_ATOM),
                'updated_at' => $task->updatedAt?->format(DATE_ATOM),
            ],
            $tasks
        );
    }
}
