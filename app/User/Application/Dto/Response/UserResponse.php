<?php

namespace App\User\Application\Dto\Response;

final readonly class UserResponse
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public string $role,
    ) {
    }
}
