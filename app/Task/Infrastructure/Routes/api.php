<?php

use App\Task\Infrastructure\Ports\Http\CreateTaskController;
use App\Task\Infrastructure\Ports\Http\DeleteTaskController;
use App\Task\Infrastructure\Ports\Http\ListAllTaskController;
use App\Task\Infrastructure\Ports\Http\ListOneTaskController;
use App\Task\Infrastructure\Ports\Http\UpdateTaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', ListAllTaskController::class);
    Route::get('/tasks/{id}', ListOneTaskController::class)->where('id', '[0-9a-fA-F-]{36}');
    Route::post('/tasks', CreateTaskController::class);
    Route::put('/tasks/{id}', UpdateTaskController::class)->where('id', '[0-9a-fA-F-]{36}');
    Route::delete('/tasks/{id}', DeleteTaskController::class)->where('id', '[0-9a-fA-F-]{36}');
});
