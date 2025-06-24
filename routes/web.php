<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanvaDesignController;
use App\Models\CanvaDesign;
use Illuminate\Support\Facades\Storage;

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
    // 1️⃣ Find the design row (404 if not found)
    $design = CanvaDesign::where('download_link', $link)->firstOrFail();
    // return $design;
    // 2️⃣ Decide which path to test
    $filePath = $design->file_path                     // Prefer column value
        ?: "canva_designs/{$design->name}.pdf"; // fallback

    // 3️⃣ Does the file actually exist in storage/app/public/... ?
    if (Storage::disk('public')->missing($filePath)) {
        abort(404, 'File not found.');
    }

    // 4️⃣ Stream the file as a download
    return Storage::disk('public')->download(
        $filePath,
        basename($filePath),          // download filename
        ['Content-Type' => 'application/pdf']
    );
})->name('canva.download');

// Route::get('/canva/download/{download_link}', [CanvaDesignController::class, 'download'])->name('canva.download');

Route::get('/canva/{id}/edit', [CanvaDesignController::class, 'edit'])->name('canva.edit');
Route::put('/canva/{id}', [CanvaDesignController::class, 'update'])->name('canva.update');
Route::delete('/canva/{id}', [CanvaDesignController::class, 'destroy'])->name('canva.destroy');