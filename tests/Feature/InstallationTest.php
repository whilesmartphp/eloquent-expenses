<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Expenses\ExpensesServiceProvider;

class InstallationTest extends TestCase
{
    #[Test]
    public function migration_creates_the_expenses_table(): void
    {
        $this->assertTrue(Schema::hasTable('expenses'));

        foreach (['owner_type', 'owner_id', 'vendor_type', 'vendor_id', 'account_type', 'account_id',
            'vendor_name', 'amount_cents', 'tax_cents', 'total_cents', 'currency', 'status',
            'deleted_at'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('expenses', $column),
                "expenses.{$column} missing -- consuming apps will break."
            );
        }
    }

    #[Test]
    public function config_defaults_are_loaded_from_the_package(): void
    {
        $this->assertSame('expenses', config('expenses.table'));
        $this->assertSame('api', config('expenses.route_prefix'));
        $this->assertIsArray(config('expenses.route_middleware'));
    }

    #[Test]
    public function api_resource_routes_are_registered_under_the_configured_prefix(): void
    {
        $registered = collect(Route::getRoutes())->map(fn ($r) => $r->uri())->all();

        $this->assertContains('api/expenses', $registered);
        $this->assertContains('api/expenses/{expense}', $registered);
    }

    #[Test]
    public function publishable_tags_are_registered(): void
    {
        $provider = app()->getProvider(ExpensesServiceProvider::class);
        $this->assertNotNull($provider, 'ExpensesServiceProvider must be registered.');

        $configTag = ServiceProvider::$publishGroups['expenses-config'] ?? null;
        $migrationsTag = ServiceProvider::$publishGroups['expenses-migrations'] ?? null;

        $this->assertNotNull($configTag, 'Missing expenses-config publish tag.');
        $this->assertNotNull($migrationsTag, 'Missing expenses-migrations publish tag.');
    }

    #[Test]
    public function route_middleware_can_be_overridden_via_config(): void
    {
        // TestCase sets it to ['api']; prove the service provider honours config.
        $routes = collect(Route::getRoutes())->filter(fn ($r) => str_starts_with($r->uri(), 'api/expenses'));
        $middlewareOnOneRoute = $routes->first()->gatherMiddleware();

        $this->assertContains('api', $middlewareOnOneRoute);
        $this->assertNotContains('auth:sanctum', $middlewareOnOneRoute);
    }
}
