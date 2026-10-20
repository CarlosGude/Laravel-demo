<?php

namespace App\Task\Infrastructure\Ports\Http;

use App\Task\Application\UseCase\ListAllTaskUseCase;
use App\Task\Infrastructure\Ports\Models\ListAllTaskPortModel;
use Illuminate\Http\JsonResponse;

final readonly class ListAllTaskController
{
    public function __construct(
        private ListAllTaskUseCase $useCase,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $tasks = $this->useCase->execute();

        return response()->json(ListAllTaskPortModel::toHttpResponse($tasks));
    }
}
