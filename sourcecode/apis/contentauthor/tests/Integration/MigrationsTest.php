<?php

declare(strict_types=1);

namespace Tests\Integration;

use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Runs all migrations up and down on each supported database, using separate
 * databases so the database used by the other tests is left untouched.
 */
class MigrationsTest extends TestCase
{
    private const DATABASE = 'contentauthor_migrations_test';

    /**
     * @param 'mysql'|'pgsql' $driver
     */
    #[DataProvider('provider_drivers')]
    public function test_migrations_run_up(string $driver): void
    {
        $connection = $this->setUpConnection($driver);

        $this->artisan('migrate', ['--database' => $connection])
            ->assertSuccessful();

        $this->artisan('migrate:status', ['--database' => $connection, '--pending' => true])
            ->expectsOutputToContain('No pending migrations')
            ->assertSuccessful();

        DB::purge($connection);
    }

    /**
     * @param 'mysql'|'pgsql' $driver
     */
    #[DataProvider('provider_drivers')]
    public function test_migrations_roll_back(string $driver): void
    {
        $connection = $this->setUpConnection($driver);

        $this->artisan('migrate', ['--database' => $connection])
            ->assertSuccessful();

        $this->artisan('migrate:reset', ['--database' => $connection])
            ->assertSuccessful();

        $this->assertSame(
            ['migrations'],
            Schema::connection($connection)->getTableListing(),
            'Tables left after rolling back all migrations',
        );

        DB::purge($connection);
    }

    public static function provider_drivers(): Generator
    {
        yield 'mariadb' => ['mysql'];
        yield 'postgres' => ['pgsql'];
    }

    /**
     * @param 'mysql'|'pgsql' $driver
     */
    private function setUpConnection(string $driver): string
    {
        $config = match ($driver) {
            'mysql' => config('database.connections.mysql'),
            'pgsql' => [
                ...config('database.connections.pgsql'),
                'host' => env('DB_PGSQL_HOST'),
                'port' => env('DB_PGSQL_PORT'),
                'username' => env('DB_PGSQL_USERNAME'),
                'password' => env('DB_PGSQL_PASSWORD'),
            ],
        };

        // Connect to a database that always exists, and create the one to migrate
        $admin = "migrations_admin_$driver";
        config(["database.connections.$admin" => [
            ...$config,
            'database' => $driver === 'pgsql' ? 'postgres' : null,
        ]]);

        DB::connection($admin)->statement('DROP DATABASE IF EXISTS ' . self::DATABASE . ($driver === 'pgsql' ? ' WITH (FORCE)' : ''));
        DB::connection($admin)->statement('CREATE DATABASE ' . self::DATABASE);
        DB::purge($admin);

        $connection = "migrations_test_$driver";
        config(["database.connections.$connection" => [...$config, 'database' => self::DATABASE]]);

        return $connection;
    }
}
