<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Whilesmart\Expenses\ExpensesServiceProvider;
use Whilesmart\OwnerAccess\OwnerAccessServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Separate tables for test host models so polymorphic type columns
        // genuinely disambiguate different record sources.
        Schema::create('workspaces', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('suppliers', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            OwnerAccessServiceProvider::class,
            ExpensesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Routes stay on so API tests can hit them; drop auth middleware so
        // tests don't need a sanctum user. Consuming apps keep auth:sanctum.
        $app['config']->set('expenses.route_middleware', ['api']);
    }
}
