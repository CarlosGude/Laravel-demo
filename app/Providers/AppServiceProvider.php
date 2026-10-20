<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName): string {
            $basename = Str::beforeLast(class_basename($modelName), 'Model');

            return 'Database\\Factories\\'.$basename.'Factory';
        });
    }
}
