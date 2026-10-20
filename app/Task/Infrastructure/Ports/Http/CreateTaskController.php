<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\CreateTaskUseCase;
use App\Task\Infrastructure\Ports\Models\CreateTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class CreateTaskController
{
    public function __construct(
        private CreateTaskUseCase $useCase,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $portModel = CreateTaskPortModel::fromHttpRequest($request);

        $task = $this->useCase->execute($portModel->toApplicationRequest());

        return response()->json(CreateTaskPortModel::toHttpResponse($task), 201);
    }
}
