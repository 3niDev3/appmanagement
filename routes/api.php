<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectPublicController;

Route::prefix('apks')->middleware('web')->group(function() {
    Route::post('/download/{apk}', [ProjectPublicController::class,'apiDownload'])->name('apks.download');
    Route::get('/history/{apk}', [ProjectPublicController::class,'apiDownloadHistory'])->name('apks.history');
    Route::get('/stats', [ProjectPublicController::class,'apiStats'])->name('apks.stats'); // optional
});
