<?php

namespace App\User\Infrastructure\Ports\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '9d1e3a2c-1b3f-4f4a-8a9c-6f2d7c9e4b2a'),
        new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: UserSchema::EXAMPLE_EMAIL),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
    ]
)]
#[OA\Schema(
    schema: 'AuthResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'token', type: 'string', example: '1|abcdef1234567890'),
    ]
)]
#[OA\Schema(
    schema: 'RegisterUserRequest',
    type: 'object',
    required: ['name', 'email', 'password'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: UserSchema::EXAMPLE_EMAIL),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret1234', minLength: 8),
    ]
)]
#[OA\Schema(
    schema: 'LoginUserRequest',
    type: 'object',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: UserSchema::EXAMPLE_EMAIL),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret1234'),
    ]
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum personal access token'
)]
final class UserSchema
{
    public const EXAMPLE_EMAIL = 'jane@example.com';
}
