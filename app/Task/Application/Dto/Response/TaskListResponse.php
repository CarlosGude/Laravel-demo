<?php

namespace App\Task\Application\Dto\Response;

final readonly class TaskListResponse
{
    /**
     * @param array<int, TaskResponse> $tasks
     */
    public function __construct(
        public array $tasks,
        public ?int $page = null,
        public ?int $perPage = null,
        public ?int $total = null,
        public ?int $lastPage = null,
    ) {

    }
}
