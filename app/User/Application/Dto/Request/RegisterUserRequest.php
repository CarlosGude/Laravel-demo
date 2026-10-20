<?php

namespace App\User\Application\Dto\Request;

final readonly class RegisterUserRequest
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }
}
