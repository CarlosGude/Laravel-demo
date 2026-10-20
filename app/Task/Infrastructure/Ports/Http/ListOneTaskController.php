<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\ListOneTaskUseCase;
use App\Task\Infrastructure\Ports\Models\ListOneTaskPortModel;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final readonly class ListOneTaskController
{
    public function __construct(
        private ListOneTaskUseCase $useCase,
    ) {
    }

    #[OA\Get(
        path: '/api/tasks/{id}',
        summary: 'Get a single task by id',
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Task id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task found',
                content: new OA\JsonContent(ref: '#/components/schemas/Task')
            ),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    public function __invoke(int $id): JsonResponse
    {
        $portModel = ListOneTaskPortModel::fromHttpRequest($id);

        $task = $this->useCase->execute($portModel->id);

        $response = ListOneTaskPortModel::toHttpResponse($task);

        if ($response === null) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($response);
    }
}
