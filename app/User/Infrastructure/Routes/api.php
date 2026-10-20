<?php

use App\User\Infrastructure\Ports\Http\LoginUserController;
use App\User\Infrastructure\Ports\Http\LogoutUserController;
use App\User\Infrastructure\Ports\Http\RegisterUserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', RegisterUserController::class);
Route::post('/auth/login', LoginUserController::class);
Route::middleware('auth:sanctum')->post('/auth/logout', LogoutUserController::class);
