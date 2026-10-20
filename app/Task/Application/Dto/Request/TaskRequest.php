<?php

namespace App\Task\Application\Dto\Request;

final readonly class TaskRequest
{
    /**
     * @param array<int, string> $categoryNames
     */
    public function __construct(
        public ?string $uuid,
        public string $title,
        public string $description,
        public bool $completed,
        public array $categoryNames = [],
    ) {

    }
}
