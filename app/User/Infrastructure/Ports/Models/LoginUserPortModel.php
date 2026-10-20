<?php

namespace App\User\Infrastructure\Ports\Models;

use App\User\Application\Dto\Request\LoginUserRequest;
use Illuminate\Http\Request;

final readonly class LoginUserPortModel
{
    private function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromHttpRequest(Request $request): self
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return new self(
            email: $validated['email'],
            password: $validated['password'],
        );
    }

    public function toApplicationRequest(): LoginUserRequest
    {
        return new LoginUserRequest(
            email: $this->email,
            password: $this->password,
        );
    }
}
