<?php

namespace App\Task\Application\Dto\Response;

final readonly class TaskResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public bool $completed,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $updatedAt,
    ) {

    }
}
