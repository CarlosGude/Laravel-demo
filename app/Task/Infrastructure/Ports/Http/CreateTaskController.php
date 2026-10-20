<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\CreateTaskUseCase;
use App\Task\Infrastructure\Ports\Models\CreateTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class CreateTaskController
{
    public function __construct(
        private CreateTaskUseCase $useCase,
    ) {
    }

    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create a new task',
        tags: ['Tasks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/TaskRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Task created',
                content: new OA\JsonContent(ref: '#/components/schemas/Task')
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = CreateTaskPortModel::fromHttpRequest($request);

        $task = $this->useCase->execute($portModel->toApplicationRequest());

        return response()->json(CreateTaskPortModel::toHttpResponse($task), 201);
    }
}
