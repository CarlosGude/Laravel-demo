<?php

namespace App\User\Infrastructure\Providers;

use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\Security\PasswordHasherInterface;
use App\User\Infrastructure\Repository\MySqlUserRepository;
use App\User\Infrastructure\Security\LaravelPasswordHasher;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, MySqlUserRepository::class);
        $this->app->bind(PasswordHasherInterface::class, LaravelPasswordHasher::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
