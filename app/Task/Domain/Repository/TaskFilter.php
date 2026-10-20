<?php

namespace App\Task\Domain\Repository;

final readonly class TaskFilter
{
    public function __construct(
        public ?int $ownerId = null,
        public ?bool $completed = null,
        public ?\DateTimeImmutable $createdFrom = null,
        public ?\DateTimeImmutable $createdTo = null,
        public ?string $categoryName = null,
        public TaskSortField $sortBy = TaskSortField::CreatedAt,
        public SortDirection $sortDirection = SortDirection::Descending,
    ) {
    }
}
