<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\ListOneTaskUseCase;
use App\Task\Infrastructure\Ports\Models\ListOneTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class ListOneTaskController
{
    public function __construct(
        private ListOneTaskUseCase $useCase,
        private RequestingUserProviderInterface $requestingUserProvider,
    ) {
    }

    #[OA\Get(
        path: '/api/tasks/{id}',
        summary: 'Get a single task by id',
        description: 'Only the owner of the task, or an admin, can see it. Any other user receives a 404, '
            .'the same as for a non-existent task, to avoid leaking its existence.',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Task UUID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task found',
                content: new OA\JsonContent(ref: '#/components/schemas/Task')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Task not found, or not owned by the requester'),
        ]
    )]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $portModel = ListOneTaskPortModel::fromHttpRequest($id);
        $requestingUser = $this->requestingUserProvider->fromRequest($request);

        $task = $this->useCase->execute($portModel->id, $requestingUser);

        $response = ListOneTaskPortModel::toHttpResponse($task);

        if ($response === null) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json($response);
    }
}
