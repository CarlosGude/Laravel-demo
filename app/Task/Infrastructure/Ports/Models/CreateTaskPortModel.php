<?php

namespace App\Task\Infrastructure\Ports\Models;

use Illuminate\Http\Request;
use App\Task\Application\Dto\Response\TaskResponse;
use App\Task\Application\Dto\Request\TaskRequest;

final readonly class CreateTaskPortModel
{
    private function __construct(
        public string $title,
        public string $description,
    )
    {
    }

    public static function fromHttpRequest(Request $request): self
    {
        return new self(
            title: (string) $request->input('title'),
            description: (string) $request->input('description'),
        );
    }

    public function toApplicationRequest(): TaskRequest
    {
        return new TaskRequest(
            id: null,
            title: $this->title,
            description: $this->description,
            completed: false,
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
