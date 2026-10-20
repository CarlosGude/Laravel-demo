<?php

namespace App\Task\Infrastructure\Providers;

use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Repository\MySqlTaskRepository;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, MySqlTaskRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
