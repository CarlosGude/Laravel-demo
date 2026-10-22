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
        private RequestingUserProviderInterface $requestingUserProvider,
    ) {
    }

    #[OA\Get(
        path: '/api/tasks',
        summary: 'List tasks, optionally filtered and sorted',
        description: 'Returns the authenticated user\'s tasks (or every task, for admins). Supports filtering by '
            .'completion status, a single date or a date range, and a category, plus sorting by creation or '
            .'modification date in ascending or descending order. With no `page`, returns a bare array of every '
            .'matching task. With `page`, returns `{data, meta}` instead, where `data` holds that page\'s tasks '
            .'and `meta` holds `page`/`per_page`/`total`/`last_page`.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
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
            new OA\Parameter(
                name: 'page',
                description: 'Page number. When present, the response is wrapped as {data, meta} instead of a '
                    .'bare array',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Tasks per page (defaults to 15, max 100). Ignored unless page is present',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Invalid filter or sort parameters'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = ListAllTaskPortModel::fromHttpRequest($request);
        $requestingUser = $this->requestingUserProvider->fromRequest($request);

        $tasks = $this->useCase->execute($requestingUser, $portModel->toApplicationRequest());

        return response()->json(ListAllTaskPortModel::toHttpResponse($tasks));
    }
}
