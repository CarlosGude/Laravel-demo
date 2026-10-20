<?php

namespace App\User\Application\UseCase;

use App\User\Application\Dto\Request\RegisterUserRequest;
use App\User\Application\Dto\Response\AuthResponse;
use App\User\Application\Dto\Response\UserResponse;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\EmailAlreadyRegisteredException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Security\PasswordHasherInterface;

final readonly class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(RegisterUserRequest $request): AuthResponse
    {
        if ($this->userRepository->findByEmail($request->email) !== null) {
            throw EmailAlreadyRegisteredException::withEmail($request->email);
        }

        $newUser = User::createNew(
            name: $request->name,
            email: $request->email,
            passwordHash: $this->passwordHasher->hash($request->password),
        );

        $user = $this->userRepository->create($newUser);
        $token = $this->userRepository->issueApiToken($user);

        return new AuthResponse(
            user: new UserResponse(
                uuid: $user->getUuid(),
                name: $user->getName(),
                email: $user->getEmail(),
                role: $user->getRole()->value,
            ),
            token: $token,
        );
    }
}
