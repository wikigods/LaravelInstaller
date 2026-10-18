<?php

use Illuminate\Support\Facades\Route;
use RachidLaasri\LaravelInstaller\Controllers\DatabaseController;
use RachidLaasri\LaravelInstaller\Controllers\EnvironmentController;
use RachidLaasri\LaravelInstaller\Controllers\FinalController;
use RachidLaasri\LaravelInstaller\Controllers\PermissionsController;
use RachidLaasri\LaravelInstaller\Controllers\RequirementsController;
use RachidLaasri\LaravelInstaller\Controllers\UpdateController;
use RachidLaasri\LaravelInstaller\Controllers\WelcomeController;

Route::group([
    'prefix' => 'install',
    'as' => 'LaravelInstaller::',
    'middleware' => ['web', 'install'],
], function () {
    Route::get('/', [WelcomeController::class, 'welcome'])->name('welcome');
    Route::get('environment', [EnvironmentController::class, 'environmentMenu'])->name('environment');
    Route::get('environment/wizard', [EnvironmentController::class, 'environmentWizard'])->name('environmentWizard');
    Route::post('environment/saveWizard', [EnvironmentController::class, 'saveWizard'])->name('environmentSaveWizard');
    Route::get('environment/classic', [EnvironmentController::class, 'environmentClassic'])->name('environmentClassic');
    Route::post('environment/saveClassic', [EnvironmentController::class, 'saveClassic'])->name('environmentSaveClassic');
    Route::get('requirements', [RequirementsController::class, 'requirements'])->name('requirements');
    Route::get('permissions', [PermissionsController::class, 'permissions'])->name('permissions');
    Route::get('database', [DatabaseController::class, 'database'])->name('database');
    Route::get('final', [FinalController::class, 'finish'])->name('final');
});

Route::group([
    'prefix' => 'update',
    'as' => 'LaravelUpdater::',
    'middleware' => 'web',
], function () {
    Route::group(['middleware' => 'update'], function () {
        Route::get('/', [UpdateController::class, 'welcome'])->name('welcome');
        Route::get('overview', [UpdateController::class, 'overview'])->name('overview');
        Route::get('database', [UpdateController::class, 'database'])->name('database');
    });

    // This route must remain outside the update middleware after migrations finish.
    Route::get('final', [UpdateController::class, 'finish'])->name('final');
});
