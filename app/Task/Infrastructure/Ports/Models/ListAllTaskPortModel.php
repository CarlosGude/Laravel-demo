<?php

namespace App\Task\Infrastructure\Ports\Models;

use App\Task\Application\Dto\Request\TaskFilterRequest;
use App\Task\Application\Dto\Response\TaskListResponse;
use App\Task\Application\Dto\Response\TaskResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final readonly class ListAllTaskPortModel
{
    private function __construct(
        public ?bool $completed,
        public ?\DateTimeImmutable $createdFrom,
        public ?\DateTimeImmutable $createdTo,
        public ?string $categoryName,
        public string $sortBy,
        public string $sortDirection,
        public ?int $page,
        public ?int $perPage,
    ) {
    }

    public static function fromHttpRequest(Request $request): self
    {
        $validated = $request->validate([
            'completed' => ['sometimes', Rule::in(['true', 'false', '1', '0', true, false, 1, 0])],
            'date' => ['sometimes', 'date'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date'],
            'category' => ['sometimes', 'string'],
            'sort_by' => ['sometimes', Rule::in(['created_at', 'updated_at'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if (isset($validated['date'])) {
            $createdFrom = (new \DateTimeImmutable($validated['date']))->setTime(0, 0, 0);
            $createdTo = $createdFrom->setTime(23, 59, 59);
        } else {
            $createdFrom = isset($validated['created_from']) ? new \DateTimeImmutable($validated['created_from']) : null;
            $createdTo = isset($validated['created_to']) ? new \DateTimeImmutable($validated['created_to']) : null;
        }

        return new self(
            completed: array_key_exists('completed', $validated) ? $request->boolean('completed') : null,
            createdFrom: $createdFrom,
            createdTo: $createdTo,
            categoryName: $validated['category'] ?? null,
            sortBy: $validated['sort_by'] ?? 'created_at',
            sortDirection: $validated['sort_direction'] ?? 'desc',
            page: $validated['page'] ?? null,
            perPage: $validated['per_page'] ?? null,
        );
    }

    public function toApplicationRequest(): TaskFilterRequest
    {
        return new TaskFilterRequest(
            completed: $this->completed,
            createdFrom: $this->createdFrom,
            createdTo: $this->createdTo,
            categoryName: $this->categoryName,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            page: $this->page,
            perPage: $this->perPage,
        );
    }

    /**
     * @return array<int, array{id: string, title: string, description: string, completed: bool, created_at: ?string, updated_at: ?string, categories: array<int, string>}>|array{data: array<int, array{id: string, title: string, description: string, completed: bool, created_at: ?string, updated_at: ?string, categories: array<int, string>}>, meta: array{page: int, per_page: int, total: int, last_page: int}}
     */
    public static function toHttpResponse(TaskListResponse $response): array
    {
        $tasks = array_map(
            static fn (TaskResponse $task): array => [
                'id' => $task->uuid,
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->completed,
                'created_at' => $task->createdAt?->format(DATE_ATOM),
                'updated_at' => $task->updatedAt?->format(DATE_ATOM),
                'categories' => $task->categories,
            ],
            $response->tasks
        );

        if ($response->page === null) {
            return $tasks;
        }

        return [
            'data' => $tasks,
            'meta' => [
                'page' => $response->page,
                'per_page' => $response->perPage,
                'total' => $response->total,
                'last_page' => $response->lastPage,
            ],
        ];
    }
}
