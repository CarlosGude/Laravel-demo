<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Dto\Response\TaskResponse;
use Illuminate\Http\Request;

final readonly class UpdateTaskPortModel
{
    /**
     * @param array<int, string> $categoryNames
     */
    private function __construct(
        public string $id,
        public string $title,
        public string $description,
        public bool $completed,
        public array $categoryNames,
    ) {
    }

    public static function fromHttpRequest(Request $request, string $id): self
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'completed' => ['required', 'boolean'],
            'category_names' => ['sometimes', 'array'],
            'category_names.*' => ['string', 'max:255'],
        ]);

        return new self(
            id: $id,
            title: $validated['title'],
            description: $validated['description'],
            completed: $validated['completed'],
            categoryNames: $validated['category_names'] ?? [],
        );
    }

    public function toApplicationRequest(): TaskRequest
    {
        return new TaskRequest(
            uuid: $this->id,
            title: $this->title,
            description: $this->description,
            completed: $this->completed,
            categoryNames: $this->categoryNames,
        );
    }

    /**
     * @return array{id: string, title: string, description: string, completed: bool, created_at: ?string, updated_at: ?string, categories: array<int, string>}
     */
    public static function toHttpResponse(TaskResponse $task): array
    {
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
