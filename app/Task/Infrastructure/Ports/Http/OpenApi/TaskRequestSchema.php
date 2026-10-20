<?php

namespace App\Task\Infrastructure\Ports\Http\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskRequest',
    type: 'object',
    required: ['title', 'description'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Buy groceries'),
        new OA\Property(property: 'description', type: 'string', example: 'Milk, eggs, bread'),
        new OA\Property(property: 'completed', type: 'boolean', example: false),
        new OA\Property(
            property: 'category_names',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Work', 'Urgent']
        ),
    ]
)]
final class TaskRequestSchema
{
}
