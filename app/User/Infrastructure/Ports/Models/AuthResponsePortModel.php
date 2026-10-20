<?php

namespace App\User\Infrastructure\Ports\Models;

use App\User\Application\Dto\Response\AuthResponse;

final readonly class AuthResponsePortModel
{
    /**
     * @return array{user: array{id: string, name: string, email: string, role: string}, token: string}
     */
    public static function toHttpResponse(AuthResponse $authResponse): array
    {
        return [
            'user' => [
                'id' => $authResponse->user->uuid,
                'name' => $authResponse->user->name,
                'email' => $authResponse->user->email,
                'role' => $authResponse->user->role,
            ],
            'token' => $authResponse->token,
        ];
    }
}
