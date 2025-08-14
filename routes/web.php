<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanvaDesignController;
use App\Models\CanvaDesign;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [CanvaDesignController::class, 'index'])->name('canva.index');
Route::get('/create', [CanvaDesignController::class, 'create'])->name('canva.create');
Route::post('/store', [CanvaDesignController::class, 'store'])->name('canva.store');
// Route::post('/canva/webhook', [CanvaDesignController::class, 'webhook'])->name('canva.webhook');
Route::get('/canva/auth', [CanvaDesignController::class, 'redirectToCanva'])->name('canva.auth');
Route::get('/canva/callback', [CanvaDesignController::class, 'handleCanvaCallback'])->name('canva.callback');

Route::get('/download/{link}', function (string $link) {
    $design = CanvaDesign::where('download_link', $link)->firstOrFail();
    $slug = \Illuminate\Support\Str::slug($design->name);
    $filePath = $design->file_path ?: "canva_designs/{$slug}.pdf";

    // Check if the file exists in storage/app/public/...
    if (!Storage::disk('public')->exists($filePath)) {
        abort(404, 'File not found.');
    }

    // Get the absolute path to the file
    $absolutePath = storage_path('app/public/' . $filePath);

    // Stream the file as a download
    return response()->download(
        $absolutePath,
        basename($filePath),
        ['Content-Type' => 'application/pdf']
    );
})->name('canva.download');

Route::get('/preview/{link}', function (string $link) {
    $design = CanvaDesign::where('download_link', $link)->firstOrFail();
    $slug = \Illuminate\Support\Str::slug($design->name);
    $filePath = $design->file_path ?: "canva_designs/{$slug}.pdf";

    // Check if the file exists in storage/app/public/...
    if (!Storage::disk('public')->exists($filePath)) {
        abort(404, 'File not found.');
    }

    // Get the absolute path to the file
    $absolutePath = storage_path('app/public/' . $filePath);

    // Stream the file inline for preview
    return response()->file(
        $absolutePath,
        ['Content-Type' => 'application/pdf']
    );
})->name('canva.preview');

// Route::get('/canva/download/{download_link}', [CanvaDesignController::class, 'download'])->name('canva.download');

Route::get('/canva/{id}/edit', [CanvaDesignController::class, 'edit'])->name('canva.edit');
Route::put('/canva/{id}', [CanvaDesignController::class, 'update'])->name('canva.update');
Route::delete('/canva/{id}', [CanvaDesignController::class, 'destroy'])->name('canva.destroy');

Route::get('/password', function () {
    return view('password');
})->name('password.form');


// Route::post('/canva/fetch', [CanvaDesignController::class, 'fetchAllLatest'])->name('canva.fetch');
Route::get('/canva/fetch/{id}', [CanvaDesignController::class, 'fetchSingleLatest'])->name('canva.fetchSingle');
Route::get('/canva/fetch', [CanvaDesignController::class, 'fetchAllLatest'])->name('canva.fetch');




Route::post('/password/submit', [CanvaDesignController::class, "auth"])->name('password.submit');
Route::post('/change-password', [CanvaDesignController::class, "changePassword"] )->name('updatePassword.submit');
Route::get('/logout', [CanvaDesignController::class, "logout"] )->name('logout');
