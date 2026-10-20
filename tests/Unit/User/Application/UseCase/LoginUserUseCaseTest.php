<?php

namespace Tests\Unit\User\Application\UseCase;

use App\User\Application\Dto\Request\LoginUserRequest;
use App\User\Application\UseCase\LoginUserUseCase;
use App\User\Domain\Entity\User;
use App\User\Domain\Exceptions\InvalidCredentialsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Security\PasswordHasherInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class LoginUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_execute_returns_a_token_when_the_credentials_are_valid(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password')->createWithId(1);

        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $userRepository->expects('findByEmail')->with('jane@example.com')->andReturn($user);
        $userRepository->expects('issueApiToken')->with($user)->andReturn('plain-text-token');

        $passwordHasher = \Mockery::mock(PasswordHasherInterface::class);
        $passwordHasher->expects('verify')->with('secret1234', 'hashed-password')->andReturn(true);

        $useCase = new LoginUserUseCase($userRepository, $passwordHasher);

        $response = $useCase->execute(new LoginUserRequest(email: 'jane@example.com', password: 'secret1234'));

        $this->assertSame($user->getUuid(), $response->user->uuid);
        $this->assertSame('plain-text-token', $response->token);
    }

    public function test_execute_throws_when_the_user_does_not_exist(): void
    {
        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $userRepository->expects('findByEmail')->with('missing@example.com')->andReturn(null);
        $userRepository->expects('issueApiToken')->never();

        $passwordHasher = \Mockery::mock(PasswordHasherInterface::class);
        $passwordHasher->expects('verify')->never();

        $useCase = new LoginUserUseCase($userRepository, $passwordHasher);

        $this->expectException(InvalidCredentialsException::class);

        $useCase->execute(new LoginUserRequest(email: 'missing@example.com', password: 'secret1234'));
    }

    public function test_execute_throws_when_the_password_does_not_match(): void
    {
        $user = User::createNew('Jane Doe', 'jane@example.com', 'hashed-password')->createWithId(1);

        $userRepository = \Mockery::mock(UserRepositoryInterface::class);
        $userRepository->expects('findByEmail')->with('jane@example.com')->andReturn($user);
        $userRepository->expects('issueApiToken')->never();

        $passwordHasher = \Mockery::mock(PasswordHasherInterface::class);
        $passwordHasher->expects('verify')->with('wrong-password', 'hashed-password')->andReturn(false);

        $useCase = new LoginUserUseCase($userRepository, $passwordHasher);

        $this->expectException(InvalidCredentialsException::class);

        $useCase->execute(new LoginUserRequest(email: 'jane@example.com', password: 'wrong-password'));
    }
}
