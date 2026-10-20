<?php

namespace App\User\Infrastructure\Ports\Models;

use App\User\Application\Dto\Request\RegisterUserRequest;
use Illuminate\Http\Request;

final readonly class RegisterUserPortModel
{
    private function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }

    public static function fromHttpRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        return new self(
            name: $validated['name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }

    public function toApplicationRequest(): RegisterUserRequest
    {
        return new RegisterUserRequest(
            name: $this->name,
            email: $this->email,
            password: $this->password,
        );
    }
}
