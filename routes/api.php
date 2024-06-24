<?php

use App\Http\Controllers\ApplyJobController;
use App\Http\Controllers\Auth\ProviderAuthController;
use App\Http\Controllers\Auth\SeekerAuthController;
use App\Http\Controllers\CreateJobController;
use App\Http\Middleware\EnsureUserIsProvider;
use App\Http\Middleware\EnsureUserIsSeeker;
use App\Http\Middleware\Provider\Authentication\Login\CheckCredential;
use App\Http\Middleware\Provider\Authentication\Login\PrepareRequestForLoginProvider;
use App\Http\Middleware\Provider\Authentication\Register\PrepareRequestForRegisteringProvider;
use App\Http\Middleware\Provider\Job\PrepareCreatingJobProcess;
use App\Http\Middleware\Seeker\Authentication\Login\CheckCredential as SeekerLoginCheckCredential;
use App\Http\Middleware\Seeker\Authentication\Login\PrepareRequestForLoginSeeker;
use App\Http\Middleware\Seeker\Authentication\Register\PrepareRequestForRegisteringSeeker;
use App\Http\Middleware\Seeker\Job\EnsureSeekerApplyIsNotDuplicated;
use App\Http\Middleware\Seeker\Job\PrepareRequestForApplyJob;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringProvider::class]);
    Route::post('login', [ProviderAuthController::class, 'login'])->middleware([PrepareRequestForLoginProvider::class, CheckCredential::class]);
    Route::post('logout', [ProviderAuthController::class, 'logout'])->middleware([EnsureUserIsProvider::class]);
    Route::middleware([EnsureUserIsProvider::class])->prefix('jobs')->group(function () {
        Route::post('create', [CreateJobController::class, 'store'])->middleware([PrepareCreatingJobProcess::class]);
    });
});

Route::prefix('seeker')->group(function () {
    Route::post('register', [SeekerAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringSeeker::class]);
    Route::post('login', [SeekerAuthController::class, 'login'])->middleware([PrepareRequestForLoginSeeker::class, SeekerLoginCheckCredential::class]);
    Route::post('logout', [SeekerAuthController::class, 'logout'])->middleware([EnsureUserIsSeeker::class]);
    Route::middleware([EnsureUserIsSeeker::class])->prefix('jobs')->group(function () {
        Route::post('apply', [ApplyJobController::class, 'apply'])->middleware([PrepareRequestForApplyJob::class, EnsureSeekerApplyIsNotDuplicated::class]);
    });
});
