<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ArenaController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LeagueStageController;

use App\Http\Controllers\ArenaDashboardController;
use App\Http\Controllers\ArenaPlayerController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Player Global Profile
    Route::get('/player/profile', [PlayerProfileController::class, 'show']);
    Route::put('/player/profile', [PlayerProfileController::class, 'update']);
    Route::post('/player/profile/image', [PlayerProfileController::class, 'updateImage']);

    // Discovery (authenticated users)
    Route::get('arenas/discover', [ArenaController::class, 'publicIndex']);
    Route::post('arenas/{arena}/join', [ArenaPlayerController::class, 'join']);
    Route::post('arenas/{arena}/register', [ArenaPlayerController::class, 'register']);
    Route::get('arenas/{arena}/my-player', [ArenaPlayerController::class, 'myPlayer']);
    Route::put('arenas/{arena}/my-player', [ArenaPlayerController::class, 'updateMyPlayer']);
    Route::get('my-registrations', [ArenaPlayerController::class, 'myRegistrations']);

    // Admin Player Management
    Route::apiResource('arenas.players', ArenaPlayerController::class)->only(['index', 'store', 'update', 'destroy']);

    // Plural API Resource for multiple arenas
    Route::apiResource('arenas', ArenaController::class);
    Route::get('arenas/{arena}/dashboard', [ArenaDashboardController::class, 'index']);

    // Leagues (nested under arenas)
    Route::apiResource('arenas.leagues', LeagueController::class);
    Route::apiResource('arenas.leagues.stages', LeagueStageController::class)->shallow();
});

