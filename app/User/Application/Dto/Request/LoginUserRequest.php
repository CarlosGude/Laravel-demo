<?php

namespace App\User\Application\Dto\Request;

final readonly class LoginUserRequest
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
