<?php

namespace App\User\Infrastructure\Ports\Http;

use App\User\Application\UseCase\RegisterUserUseCase;
use App\User\Domain\Exceptions\EmailAlreadyRegisteredException;
use App\User\Infrastructure\Ports\Models\AuthResponsePortModel;
use App\User\Infrastructure\Ports\Models\RegisterUserPortModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final readonly class RegisterUserController
{
    public function __construct(
        private RegisterUserUseCase $useCase,
    ) {
    }

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register a new user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterUserRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error or email already registered'),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $portModel = RegisterUserPortModel::fromHttpRequest($request);

        try {
            $authResponse = $this->useCase->execute($portModel->toApplicationRequest());
        } catch (EmailAlreadyRegisteredException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(AuthResponsePortModel::toHttpResponse($authResponse), 201);
    }
}
