<?php

return [
    'register_routes' => env('EXPENSES_REGISTER_ROUTES', true),
    'route_prefix' => env('EXPENSES_ROUTE_PREFIX', 'api'),
    'route_middleware' => ['api', 'auth:sanctum'],
    'table' => env('EXPENSES_TABLE', 'expenses'),
];
