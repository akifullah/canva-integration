<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanvaDesignController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/canva', [CanvaDesignController::class, 'index'])->name('canva.index');
Route::get('/canva/create', [CanvaDesignController::class, 'create'])->name('canva.create');
Route::post('/canva', [CanvaDesignController::class, 'store'])->name('canva.store');
Route::get('/canva/download/{download_link}', [CanvaDesignController::class, 'download'])->name('canva.download');
Route::post('/canva/webhook', [CanvaDesignController::class, 'webhook'])->name('canva.webhook');
Route::get('/canva/auth', [CanvaDesignController::class, 'redirectToCanva'])->name('canva.auth');
Route::get('/canva/callback', [CanvaDesignController::class, 'handleCanvaCallback'])->name('canva.callback');
 