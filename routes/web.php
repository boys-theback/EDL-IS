<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPathController;
use App\Http\Controllers\WhitelistController;
use App\Http\Controllers\WhitelistFileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [WhitelistController::class, 'index'])->name('dashboard');
    Route::post('/whitelist', [WhitelistController::class, 'store'])->name('whitelist.store');
    Route::get('/whitelist/{entry}/edit', [WhitelistController::class, 'edit'])->name('whitelist.edit');
    Route::put('/whitelist/{entry}', [WhitelistController::class, 'update'])->name('whitelist.update');
    Route::delete('/whitelist/{entry}', [WhitelistController::class, 'destroy'])->name('whitelist.destroy');
    Route::delete('/whitelist', [WhitelistController::class, 'bulkDestroy'])->name('whitelist.bulk-destroy');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::put('/settings/output-directory', [AdminPathController::class, 'update'])->name('settings.output-directory');
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    });

    Route::get('/{directory}', [WhitelistFileController::class, 'index'])->name('whitelist-files.index');
    Route::get('/{directory}/{filename}', [WhitelistFileController::class, 'show'])->name('whitelist-files.show');
});
