<?php

use App\Http\Controllers\ApplyJobController;
use App\Http\Controllers\Auth\ProviderAuthController;
use App\Http\Controllers\Auth\SeekerAuthController;
use App\Http\Controllers\CityListController;
use App\Http\Controllers\CreateJobController;
use App\Http\Controllers\JobSkillsController;
use App\Http\Controllers\ReccomendationController;
use App\Http\Controllers\SaveJobController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\UnSaveJobController;
use App\Http\Controllers\JobList;
use App\Http\Controllers\CategoryListController;
use App\Http\Controllers\ProviderInfo;
use App\Http\Controllers\CompanyListController;
use App\Http\Controllers\SeekerInfo;
use App\Http\Controllers\CreateCVController;


use App\Http\Middleware\EnsureUserIsProvider;
use App\Http\Middleware\EnsureUserIsSeeker;
use App\Http\Middleware\Provider\Authentication\Login\CheckCredential;
use App\Http\Middleware\Provider\Authentication\Login\PrepareRequestForLoginProvider;
use App\Http\Middleware\Provider\Authentication\Register\PrepareRequestForRegisteringProvider;

use App\Http\Middleware\Admin\PrepareRequestForLoginAdmin;
use App\Http\Middleware\Admin\CheckCredentialAdmin;
use App\Http\Middleware\Admin\EnsureUserIsAdmin;
use App\Http\Controllers\Auth\AdminAuthController;

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

Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login'])->middleware([PrepareRequestForLoginAdmin::class,CheckCredentialAdmin::class]);
    Route::post('logout', [AdminAuthController::class, 'logout'])->middleware([EnsureUserIsAdmin::class]);

});

Route::prefix('seeker')->group(function () {
    Route::post('register', [SeekerAuthController::class, 'register'])->middleware([PrepareRequestForRegisteringSeeker::class]);
    Route::post('login', [SeekerAuthController::class, 'login'])->middleware([PrepareRequestForLoginSeeker::class, SeekerLoginCheckCredential::class]);
    Route::post('logout', [SeekerAuthController::class, 'logout'])->middleware([EnsureUserIsSeeker::class]);
    Route::get('get-info', SeekerInfo::class)->middleware([EnsureUserIsSeeker::class]);

    Route::middleware([EnsureUserIsSeeker::class])->prefix('jobs')->group(function () {
        Route::post('apply', [ApplyJobController::class, 'apply'])->middleware([PrepareRequestForApplyJob::class, EnsureSeekerApplyIsNotDuplicated::class]);
        Route::post('save', [SaveJobController::class, 'save'])->middleware([PrepareRequestForSaveJob::class,EnsureSeekerJobNotSavedBefore::class]);
        Route::post('unsave', [SaveJobController::class, 'unsave'])->middleware([EnsureSeekerJobSavedBefore::class]);

    });
    Route::middleware([EnsureUserIsSeeker::class])->prefix('cv')->group(function () {
        Route::post('create', [CreateCVController::class, 'store']);

    });
});

Route::get('joblist',[JobList::class,'jobList']);
Route::get('companyList',[CompanyListController::class,'companyList']);
Route::get('categories',[CategoryListController::class,'categoryList']);
Route::get('skills', [SkillsController::class, 'allSkills']);
Route::post('skills', [SkillsController::class, 'store']);
Route::get('jobskills', [JobSkillsController::class, 'jobskills']);
Route::get('cities', [CityListController::class, 'cities']);




Route::get('recommend',[ReccomendationController::class,'jobRecommend']);





Route::middleware('auth:sanctum')->get('/provider', [ProviderInfo::class, 'getProvider']);
