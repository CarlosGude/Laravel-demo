<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\UpdateTaskUseCase;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Infrastructure\Ports\Models\UpdateTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class UpdateTaskController
{
    public function __construct(
        private UpdateTaskUseCase $useCase,
    ) {
    }

    #[OA\Put(
        path: '/api/tasks/{id}',
        summary: 'Update an existing task',
        description: 'Only the owner of the task, or an admin, can update it. Any other user receives a 404, '
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TaskRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Task updated',
                content: new OA\JsonContent(ref: '#/components/schemas/Task')
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Task not found, or not owned by the requester'),
        ]
    )]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $portModel = UpdateTaskPortModel::fromHttpRequest($request, $id);
        $requestingUser = RequestingUserFactory::fromRequest($request);

        try {
            $task = $this->useCase->execute($portModel->toApplicationRequest(), $requestingUser);
        } catch (TaskNotFoundException) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(UpdateTaskPortModel::toHttpResponse($task));
    }
}
