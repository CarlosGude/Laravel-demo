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
        private RequestingUserProviderInterface $requestingUserProvider,
    ) {
    }

    #[OA\Post(
        path: '/api/tasks',
        summary: 'Create a new task',
        tags: ['Tasks'],
        security: [['bearerAuth' => []]],
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = CreateTaskPortModel::fromHttpRequest($request);
        $requestingUser = $this->requestingUserProvider->fromRequest($request);

        $task = $this->useCase->execute($portModel->toApplicationRequest(), $requestingUser);

        return response()->json(CreateTaskPortModel::toHttpResponse($task), 201);
    }
}
