<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListModuleMigrationsCommand extends Command
{
    protected $signature = 'migrations:modules';

    protected $description = 'List migrations from every module (app/*/Infrastructure/Database/Migrations) plus database/migrations';

    public function handle(): int
    {
        $migrations = collect(glob(base_path('app/*/Infrastructure/Database/Migrations/*.php')) ?: [])
            ->map(fn (string $path): array => [
                'module' => basename(dirname($path, 4)),
                'migration' => basename($path, '.php'),
            ])
            ->merge(
                collect(glob(base_path('database/migrations/*.php')) ?: [])
                    ->map(fn (string $path): array => [
                        'module' => 'root',
                        'migration' => basename($path, '.php'),
                    ])
            )
            ->sortBy('migration')
            ->values();

        if ($migrations->isEmpty()) {
            $this->info('No migrations found.');

            return self::SUCCESS;
        }

        $ran = $this->ranMigrations();

        $this->table(
            ['Module', 'Migration', 'Status'],
            $migrations->map(fn (array $m) => [
                $m['module'],
                $m['migration'],
                $this->statusFor($ran, $m['migration']),
            ])
        );

        return self::SUCCESS;
    }

    /**
     * @param ?Collection<int, string> $ran
     */
    private function statusFor(?Collection $ran, string $migration): string
    {
        if ($ran === null) {
            return 'unknown';
        }

        return $ran->contains($migration) ? 'ran' : 'pending';
    }

    /**
     * @return ?Collection<int, string>
     */
    private function ranMigrations(): ?Collection
    {
        try {
            return DB::table('migrations')
                ->pluck('migration')
                ->map(static function (mixed $migration): string {
                    if (!is_string($migration)) {
                        throw new \UnexpectedValueException('The migrations.migration column must be a string.');
                    }

                    return $migration;
                })
                ->values();
        } catch (\Throwable) {
            return null;
        }
    }
}
