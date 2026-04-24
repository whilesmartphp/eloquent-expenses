<?php

namespace Whilesmart\Expenses;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ExpensesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/expenses.php', 'expenses');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/expenses.php' => config_path('expenses.php'),
        ], 'expenses-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'expenses-migrations');

        if (config('expenses.register_routes', true)) {
            Route::middleware(config('expenses.route_middleware', ['api', 'auth:sanctum']))
                ->prefix(config('expenses.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/api.php');
        }
    }
}
