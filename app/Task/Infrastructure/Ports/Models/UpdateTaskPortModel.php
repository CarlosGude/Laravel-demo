<?php

namespace App\Task\Infrastructure\Ports\Models;

use Illuminate\Http\Request;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Application\Dto\Request\TaskRequest;

final readonly class UpdateTaskPortModel
{
    private function __construct(
        public int $id,
        public string $title,
        public string $description,
        public bool $completed,
    )
    {
    }

    public static function fromHttpRequest(Request $request, int $id): self
    {
        return new self(
            id: $id,
            title: (string) $request->input('title'),
            description: (string) $request->input('description'),
            completed: (bool) $request->input('completed', false),
        );
    }

    public function toApplicationRequest(): TaskRequest
    {
        return new TaskRequest(
            id: $this->id,
            title: $this->title,
            description: $this->description,
            completed: $this->completed,
        );
    }

    public static function toHttpResponse(TaskResponse $task): array
    {
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
