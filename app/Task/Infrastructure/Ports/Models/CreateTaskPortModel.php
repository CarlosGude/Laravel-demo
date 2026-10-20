<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Request\TaskRequest;
use App\Task\Application\Dto\Response\TaskResponse;
use Illuminate\Http\Request;

final readonly class CreateTaskPortModel
{
    /**
     * @param array<int, string> $categoryNames
     */
    private function __construct(
        public string $title,
        public string $description,
        public array $categoryNames,
    ) {
    }

    public static function fromHttpRequest(Request $request): self
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'category_names' => ['sometimes', 'array'],
            'category_names.*' => ['string', 'max:255'],
        ]);

        return new self(
            title: $validated['title'],
            description: $validated['description'],
            categoryNames: $validated['category_names'] ?? [],
        );
    }

    public function toApplicationRequest(): TaskRequest
    {
        return new TaskRequest(
            uuid: null,
            title: $this->title,
            description: $this->description,
            completed: false,
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
