<?php

namespace App\User\Application\Dto\Response;

final readonly class AuthResponse
{
    public function __construct(
        public UserResponse $user,
        public string $token,
    ) {
    }
}
