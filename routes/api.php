<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Expenses\Http\Controllers\ExpenseController;

Route::apiResource('expenses', ExpenseController::class);
