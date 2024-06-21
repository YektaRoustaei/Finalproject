<?php
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\ProviderAuthController;
use App\Http\Controllers\SeekerAuthController;

Route::prefix('provider')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register']);
    Route::post('login', [ProviderAuthController::class, 'login']);
    Route::post('logout', [ProviderAuthController::class, 'logout'])
        ->middleware('auth:sanctum');

});

Route::prefix('seeker')->group(function () {
    Route::post('register', [SeekerAuthController::class, 'register']);
    Route::post('login', [SeekerAuthController::class, 'login']);
    Route::post('logout', [SeekerAuthController::class, 'logout']);
});
