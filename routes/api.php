<?php

use App\Http\Controllers\ApplyJobController;
use App\Http\Controllers\Auth\ProviderAuthController;
use App\Http\Controllers\Auth\SeekerAuthController;
use App\Http\Controllers\CreateJobController;
use App\Http\Controllers\SaveJobController;
use App\Http\Controllers\UnSaveJobController;
use App\Http\Controllers\JobList;
use App\Http\Controllers\CategoryListController;
use App\Http\Controllers\ProviderInfo;
use App\Http\Controllers\SeekerInfo;

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
use App\Http\Middleware\Seeker\Job\PrepareRequestForSaveJob;
use App\Http\Middleware\Seeker\Job\EnsureSeekerJobNotSavedBefore;
use App\Http\Middleware\Seeker\Job\EnsureSeekerJobSavedBefore;
use Illuminate\Support\Facades\Route;

Route::prefix('provider')->group(function () {
    Route::post('register', [ProviderAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringProvider::class]);
    Route::post('login', [ProviderAuthController::class, 'login'])->middleware([PrepareRequestForLoginProvider::class, CheckCredential::class]);
    Route::post('logout', [ProviderAuthController::class, 'logout'])->middleware([EnsureUserIsProvider::class]);
    Route::get('get-info', ProviderInfo::class)->middleware([EnsureUserIsProvider::class]);
    Route::middleware([EnsureUserIsProvider::class])->prefix('jobs')->group(function () {
        Route::post('create', [CreateJobController::class, 'store'])->middleware([PrepareCreatingJobProcess::class]);
    });
});

Route::prefix('seeker')->group(function () {
    Route::post('register', [SeekerAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringSeeker::class]);
    Route::post('login', [SeekerAuthController::class, 'login'])->middleware([PrepareRequestForLoginSeeker::class, SeekerLoginCheckCredential::class]);
    Route::post('logout', [SeekerAuthController::class, 'logout'])->middleware([EnsureUserIsSeeker::class]);
    Route::get('get-info', SeekerInfo::class)->middleware([EnsureUserIsSeeker::class]);

    Route::middleware([EnsureUserIsSeeker::class])->prefix('jobs')->group(function () {
        Route::post('apply', [ApplyJobController::class, 'apply'])->middleware([PrepareRequestForApplyJob::class, EnsureSeekerApplyIsNotDuplicated::class]);
        Route::post('save', [SaveJobController::class, 'save'])->middleware([PrepareRequestForSaveJob::class,EnsureSeekerJobNotSavedBefore::class]);
        Route::post('unsave', [UnSaveJobController::class, 'unsave'])->middleware([PrepareRequestForSaveJob::class,EnsureSeekerJobSavedBefore::class]);

    });
});

Route::get('joblist',[JobList::class,'jobList']);
Route::get('categories',[CategoryListController::class,'categoryList']);

Route::middleware('auth:sanctum')->get('/provider', [ProviderInfo::class, 'getProvider']);
