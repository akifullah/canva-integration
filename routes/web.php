<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanvaDesignController;
use App\Models\CanvaDesign;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/canva', [CanvaDesignController::class, 'index'])->name('canva.index');
Route::get('/canva/create', [CanvaDesignController::class, 'create'])->name('canva.create');
Route::post('/canva', [CanvaDesignController::class, 'store'])->name('canva.store');
// Route::post('/canva/webhook', [CanvaDesignController::class, 'webhook'])->name('canva.webhook');
Route::get('/canva/auth', [CanvaDesignController::class, 'redirectToCanva'])->name('canva.auth');
Route::get('/canva/callback', [CanvaDesignController::class, 'handleCanvaCallback'])->name('canva.callback');


Route::get('/download/{link}', function ($link) {
    $design = CanvaDesign::where('download_link', $link)->firstOrFail();

    // Use saved file_path if available, otherwise build from UUID
    $filePath = $design->file_path ?: "canva_designs/{$design->download_link}.pdf";

    if (! Storage::disk('public')->exists($filePath)) {
        abort(404, 'File not found.');
    }

    // Download the latest design file
    return Storage::disk('public')->download($filePath, basename($filePath), [
        'Content-Type' => 'application/pdf',
    ]);
})->name('canva.download'); 

// Route::get('/canva/download/{download_link}', [CanvaDesignController::class, 'download'])->name('canva.download');