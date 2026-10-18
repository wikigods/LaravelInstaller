<?php

namespace Wikigods\LaravelInstaller\Helpers;

use Illuminate\Support\Facades\DB;

trait MigrationsHelper
{
    /**
     * Get the migrations available to Laravel's migrator.
     *
     * @return array Array of migrations name, empty if no migrations are existing
     */
    public function getMigrations()
    {
        $paths = [database_path('migrations')];

        // Packages such as nwidart/laravel-modules register their enabled
        // modules' migration directories with Laravel's migrator.
        if (app()->bound('migrator')) {
            $migrator = app('migrator');

            if (method_exists($migrator, 'paths')) {
                $paths = array_merge($paths, $migrator->paths());
            }
        }

        $migrations = [];

        foreach (array_unique($paths) as $path) {
            $files = glob(rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*_*.php');

            foreach ($files ?: [] as $file) {
                $migrations[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        $migrations = array_values(array_unique($migrations));
        sort($migrations);

        return $migrations;
    }

    /**
     * Get the migrations that have already been ran.
     *
     * @return \Illuminate\Support\Collection List of migrations
     */
    public function getExecutedMigrations()
    {
        // migrations table should exist, if not, user will receive an error.
        return DB::table('migrations')->get()->pluck('migration');
    }

    /**
     * Get available migrations that have not been executed yet.
     *
     * @return array
     */
    public function getPendingMigrations()
    {
        $executed = $this->getExecutedMigrations()->all();

        return array_values(array_diff($this->getMigrations(), $executed));
    }
}
