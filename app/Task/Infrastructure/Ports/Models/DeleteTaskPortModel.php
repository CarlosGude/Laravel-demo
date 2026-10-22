<?php

namespace App\Task\Infrastructure\Ports\Models;

final readonly class DeleteTaskPortModel
{
    private function __construct(
        public string $id,
    ) {
    }

    public static function fromHttpRequest(string $id): self
    {
        return new self(id: $id);
    }
}
