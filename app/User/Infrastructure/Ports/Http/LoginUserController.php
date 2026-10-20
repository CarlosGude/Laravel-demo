<?php

namespace App\User\Infrastructure\Ports\Http;

use App\User\Application\UseCase\LoginUserUseCase;
use App\User\Domain\Exceptions\InvalidCredentialsException;
use App\User\Infrastructure\Ports\Models\AuthResponsePortModel;
use App\User\Infrastructure\Ports\Models\LoginUserPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class LoginUserController
{
    public function __construct(
        private LoginUserUseCase $useCase,
    ) {
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Log in and obtain an API token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginUserRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = LoginUserPortModel::fromHttpRequest($request);

        try {
            $authResponse = $this->useCase->execute($portModel->toApplicationRequest());
        } catch (InvalidCredentialsException $exception) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        return response()->json(AuthResponsePortModel::toHttpResponse($authResponse));
    }
}
