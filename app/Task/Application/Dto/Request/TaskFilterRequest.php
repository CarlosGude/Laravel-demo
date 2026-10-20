<?php

namespace App\Task\Application\Dto\Request;

final readonly class TaskFilterRequest
{
    public function __construct(
        public ?bool $completed = null,
        public ?\DateTimeImmutable $createdFrom = null,
        public ?\DateTimeImmutable $createdTo = null,
        public ?string $categoryName = null,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
    ) {
    }
}
