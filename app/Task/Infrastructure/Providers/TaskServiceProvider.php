<?php

namespace App\Task\Infrastructure\Providers;

use App\Task\Domain\Repository\CategoryRepositoryInterface;
use App\Task\Domain\Repository\TaskRepositoryInterface;
use App\Task\Infrastructure\Ports\Http\RequestingUserProviderInterface;
use App\Task\Infrastructure\Ports\Http\SanctumRequestingUserProvider;
use App\Task\Infrastructure\Repository\MySqlCategoryRepository;
use App\Task\Infrastructure\Repository\MySqlTaskRepository;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, MySqlTaskRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, MySqlCategoryRepository::class);
        $this->app->bind(RequestingUserProviderInterface::class, SanctumRequestingUserProvider::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
