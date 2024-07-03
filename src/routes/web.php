<?php

use Illuminate\Support\Facades\Route;
use Xgenious\Installer\Http\Controllers\InstallerController;

Route::group(['prefix' => 'install', 'middleware' => ['web']], function () {
    Route::get('/', [InstallerController::class, 'index'])->name('installer.index');
    Route::post('/verify-purchase', [InstallerController::class, 'verifyPurchase'])->name('installer.verify-purchase');
    Route::post('/check-database', [InstallerController::class, 'checkDatabase'])->name('installer.check-database');
    Route::post('/install', [InstallerController::class, 'install'])->name('installer.install');
});