<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ArenaController;
use App\Http\Controllers\PlayerProfileController;
use App\Http\Controllers\LeagueController;
use App\Http\Controllers\LeagueStageController;
use App\Http\Controllers\LeagueStagePlayerController;
use App\Http\Controllers\LeagueStageRegistrationController;

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

    // Leagues — rotas públicas (antes do apiResource para não conflitar)
    Route::get('leagues/open', [LeagueController::class, 'open']);
    Route::get('leagues/{league}', [LeagueController::class, 'publicShow']);
    Route::post('leagues/{league}/stages/{stage}/register', [LeagueStageRegistrationController::class, 'selfRegister']);

    // Leagues — admin (nested under arenas)
    Route::apiResource('arenas.leagues', LeagueController::class);
    Route::apiResource('arenas.leagues.stages', LeagueStageController::class)->shallow();

    // Admin: CRUD de inscrições da etapa
    Route::prefix('arenas/{arena}/leagues/{league}/stages/{stage}/registrations')
        ->controller(LeagueStageRegistrationController::class)
        ->group(function () {
            Route::get('/',                        'index');
            Route::post('/',                       'store');
            Route::put('/{registration}',          'update');
            Route::patch('/{registration}/status', 'updateStatus');
            Route::delete('/{registration}',       'destroy');
        });

    // League Stage Players (inscrições)
    Route::get('stages/{stage}/players', [LeagueStagePlayerController::class, 'index']);
    Route::post('stages/{stage}/players', [LeagueStagePlayerController::class, 'store']);
    Route::patch('stages/{stage}/players/{player}/confirm', [LeagueStagePlayerController::class, 'confirm']);
    Route::delete('stages/{stage}/players/{player}', [LeagueStagePlayerController::class, 'destroy']);
});

