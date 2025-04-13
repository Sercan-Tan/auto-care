<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\DatabaseBackupController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => false, // Kayıt olma özelliği kapatıldı
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');    
    Route::get('/costs', [CostController::class, 'index'])->name('costs.index');
    Route::resource('categories', CategoryController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('services', ServiceController::class)->except(['create', 'store']);    
    Route::get('vehicles/{vehicle}/services/create', [ServiceController::class, 'create'])->name('services.create');
    Route::post('vehicles/{vehicle}/services', [ServiceController::class, 'store'])->name('services.store');
    Route::resource('users', UserController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Yedekleme Rotaları
    Route::controller(DatabaseBackupController::class)->prefix('backups')->name('backups.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/create', 'create')->name('create');
        Route::post('/restore', 'restore')->name('restore');
        Route::post('/upload', 'upload')->name('upload');
        Route::get('/download/{filename}', 'download')->name('download');
        Route::delete('/delete', 'delete')->name('delete');
    });
    //Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
