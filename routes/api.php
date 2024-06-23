<?php

use App\Http\Controllers\Auth\ProviderAuthController;
use App\Http\Controllers\Auth\SeekerAuthController;
use App\Http\Controllers\AddJobs;
use App\Http\Middleware\Provider\Authentication\Login\CheckCredential;
use App\Http\Middleware\Provider\Authentication\Login\PrepareRequestForLoginProvider;
use App\Http\Middleware\Provider\Authentication\Register\PrepareRequestForRegisteringProvider;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringProvider::class]);
    Route::post('login', [ProviderAuthController::class, 'login'])->middleware([PrepareRequestForLoginProvider::class, CheckCredential::class]);
    Route::post('logout', [ProviderAuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('addjobs', [AddJobs::class, 'store']);
    });
});

Route::prefix('seeker')->group(function () {
    Route::post('register', [SeekerAuthController::class, 'register']);
    Route::post('login', [SeekerAuthController::class, 'login']);
    Route::post('logout', [SeekerAuthController::class, 'logout'])->middleware('auth:sanctum');
});
