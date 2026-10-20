<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\UpdateTaskUseCase;
use App\Task\Domain\Exceptions\TaskNotFoundException;
use App\Task\Infrastructure\Ports\Models\UpdateTaskPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class UpdateTaskController
{
    public function __construct(
        private UpdateTaskUseCase $useCase,
    ) {
    }

    public function __invoke(Request $request, int $id): JsonResponse
    {
        $portModel = UpdateTaskPortModel::fromHttpRequest($request, $id);

        try {
            $task = $this->useCase->execute($portModel->toApplicationRequest());
        } catch (TaskNotFoundException) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(UpdateTaskPortModel::toHttpResponse($task));
    }
}
