<?php

namespace Tests\Unit\User\Application\UseCase;

use App\User\Application\Dto\Request\RegisterUserRequest;
use App\User\Application\UseCase\RegisterUserUseCase;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\EmailAlreadyRegisteredException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Security\PasswordHasherInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RegisterUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_creates_a_new_regular_user_and_returns_a_token(): void
    {
        $persistedUser = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password')->createWithId(1);

        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $userRepository->expects('findByEmail')->with('jane@example.com')->andReturn(null);
        $userRepository->expects('create')
            ->with(\Mockery::on(fn (User $user) => $user->getEmail() === 'jane@example.com'
                && $user->getPasswordHash() === 'hashed-password'
                && !$user->isAdmin()))
            ->andReturn($persistedUser);
        $userRepository->expects('issueApiToken')->with($persistedUser)->andReturn('plain-text-token');

        $passwordHasher = \Mockery::mock(PasswordHasherInterface::class);
        $passwordHasher->expects('hash')->with('secret1234')->andReturn('hashed-password');

        $useCase = new RegisterUserUseCase($userRepository, $passwordHasher);

        $response = $useCase->execute(new RegisterUserRequest(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'secret1234',
        ));

        $this->assertSame(1, $response->user->id);
        $this->assertSame('jane@example.com', $response->user->email);
        $this->assertSame('user', $response->user->role);
        $this->assertSame('plain-text-token', $response->token);
    }

    public function test_execute_throws_when_the_email_is_already_registered(): void
    {
        $existingUser = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password')->createWithId(1);

        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $userRepository->expects('findByEmail')->with('jane@example.com')->andReturn($existingUser);
        $userRepository->expects('create')->never();

        $passwordHasher = \Mockery::mock(PasswordHasherInterface::class);
        $passwordHasher->expects('hash')->never();

        $useCase = new RegisterUserUseCase($userRepository, $passwordHasher);

        $this->expectException(EmailAlreadyRegisteredException::class);

        $useCase->execute(new RegisterUserRequest(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'secret1234',
        ));
    }
}
