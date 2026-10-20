<?php

namespace App\User\Application\UseCase;

use App\User\Application\Dto\Request\LoginUserRequest;
use App\User\Application\Dto\Response\AuthResponse;
use App\User\Application\Dto\Response\UserResponse;
use App\User\Domain\Exceptions\InvalidCredentialsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Security\PasswordHasherInterface;

final readonly class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(LoginUserRequest $request): AuthResponse
    {
        $user = $this->userRepository->findByEmail($request->email);

        if ($user === null || !$this->passwordHasher->verify($request->password, $user->getPasswordHash())) {
            throw new InvalidCredentialsException();
        }

        $token = $this->userRepository->issueApiToken($user);

        return new AuthResponse(
            user: new UserResponse(
                id: $user->getPersistedId(),
                name: $user->getName(),
                email: $user->getEmail(),
                role: $user->getRole()->value,
            ),
            token: $token,
        );
    }
}
