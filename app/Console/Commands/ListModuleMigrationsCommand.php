<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListModuleMigrationsCommand extends Command
{
    protected $signature = 'migrations:modules';

    protected $description = 'List migrations from every module (app/*/Infrastructure/Database/Migrations) plus database/migrations';

    public function handle(): int
    {
        $migrations = collect(glob(base_path('app/*/Infrastructure/Database/Migrations/*.php')))
            ->map(fn (string $path) => [
                'module' => basename(dirname($path, 4)),
                'migration' => basename($path, '.php'),
            ])
            ->merge(
                collect(glob(base_path('database/migrations/*.php')))
                    ->map(fn (string $path) => [
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
                $ran === null ? 'unknown' : ($ran->contains($m['migration']) ? 'ran' : 'pending'),
            ])
        );

        return self::SUCCESS;
    }

    private function ranMigrations(): ?\Illuminate\Support\Collection
    {
        try {
            return DB::table('migrations')->pluck('migration');
        } catch (\Throwable) {
            return null;
        }
    }
}
