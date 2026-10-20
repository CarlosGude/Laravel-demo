<?php

namespace App\Task\Infrastructure\Ports\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Task',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '9d1e3a2c-1b3f-4f4a-8a9c-6f2d7c9e4b2a'),
        new OA\Property(property: 'title', type: 'string', example: 'Buy groceries'),
        new OA\Property(property: 'description', type: 'string', example: 'Milk, eggs, bread'),
        new OA\Property(property: 'completed', type: 'boolean', example: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'categories',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Work', 'Urgent']
        ),
    ]
)]
final class TaskSchema
{
}
