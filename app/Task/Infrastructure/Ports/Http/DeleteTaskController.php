<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\DeleteTaskUseCase;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Infrastructure\Ports\Models\DeleteTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

final readonly class DeleteTaskController
{
    public function __construct(
        private DeleteTaskUseCase $useCase,
    ) {
    }

    #[OA\Delete(
        path: '/api/tasks/{id}',
        summary: 'Delete a task',
        description: 'Only the owner of the task, or an admin, can delete it. Any other user receives a 404, '
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
            new OA\Response(response: 204, description: 'Task deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Task not found, or not owned by the requester'),
        ]
    )]
    public function __invoke(Request $request, string $id): JsonResponse|Response
    {
        $portModel = DeleteTaskPortModel::fromHttpRequest($id);
        $requestingUser = RequestingUserFactory::fromRequest($request);

        try {
            $this->useCase->execute($portModel->id, $requestingUser);
        } catch (TaskNotFoundException) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->noContent();
    }
}
