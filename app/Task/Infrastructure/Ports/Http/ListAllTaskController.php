<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\ListAllTaskUseCase;
use App\Task\Infrastructure\Ports\Models\ListAllTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class ListAllTaskController
{
    public function __construct(
        private ListAllTaskUseCase $useCase,
    ) {
    }

    #[OA\Get(
        path: '/api/tasks',
        summary: 'List tasks, optionally filtered and sorted',
        description: 'Returns all tasks. Supports filtering by completion status, a single date or a date range, '
            .'and a category, plus sorting by creation or modification date in ascending or descending order.',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'completed',
                description: 'Filter by completion status: true for completed tasks, false for pending ones',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(
                name: 'date',
                description: 'Filter tasks created on this exact date (YYYY-MM-DD). Takes precedence over '
                    .'created_from/created_to when present',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'created_from',
                description: 'Filter tasks created on or after this date/time (inclusive)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'created_to',
                description: 'Filter tasks created on or before this date/time (inclusive)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'category',
                description: 'Filter tasks that have a category with this exact name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Work')
            ),
            new OA\Parameter(
                name: 'sort_by',
                description: 'Field to sort by',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['created_at', 'updated_at'], default: 'created_at')
            ),
            new OA\Parameter(
                name: 'sort_direction',
                description: 'Sort direction',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tasks',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Task')
                )
            ),
            new OA\Response(response: 422, description: 'Invalid filter or sort parameters'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = ListAllTaskPortModel::fromHttpRequest($request);

        $tasks = $this->useCase->execute($portModel->toApplicationRequest());

        return response()->json(ListAllTaskPortModel::toHttpResponse($tasks));
    }
}
