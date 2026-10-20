<?php

namespace App\Task\Application\Dto\Request;

final readonly class RequestingUser
{
    public function __construct(
        public int $id,
        public bool $isAdmin,
    ) {
    }
}
