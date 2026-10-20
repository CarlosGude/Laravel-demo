<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Factory::guessFactoryNamesUsing(
            /**
             * @param class-string<Model> $modelName
             * @return class-string<Factory>
             */
            static function (string $modelName): string {
                $basename = Str::beforeLast(class_basename($modelName), 'Model');

                /** @var class-string<Factory<Model>> */
                return 'Database\\Factories\\'.$basename.'Factory';
            }
        );
    }
}
