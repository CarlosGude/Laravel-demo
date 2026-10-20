<?php

namespace App\Task\Application\Dto\Request;

final readonly class TaskRequest
{
    public function __construct(
        public ?int $id,
        public string $title,
        public string $description,
        public bool $completed
    ) {

    }
}
