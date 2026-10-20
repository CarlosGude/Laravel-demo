<?php

namespace App\Task\Application\Dto\Response;

final readonly class TaskResponse
{
    /**
     * @param array<int, string> $categories
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public bool $completed,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
        public array $categories = [],
    ) {

    }
}
