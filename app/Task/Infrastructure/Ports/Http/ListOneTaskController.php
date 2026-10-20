<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\ListOneTaskUseCase;
use App\Task\Infrastructure\Ports\Models\ListOneTaskPortModel;
use Illuminate\Http\JsonResponse;

final readonly class ListOneTaskController
{
    public function __construct(
        private ListOneTaskUseCase $useCase,
    )
    {
    }

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
