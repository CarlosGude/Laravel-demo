<?php

namespace App\Task\Infrastructure\Ports\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Task',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
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
