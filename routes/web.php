<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProjectPublicController;

// -----------------------------
// Admin Routes
// -----------------------------
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest admin routes
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    // Authenticated admin routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::resource('projects', ProjectController::class);
        Route::resource('users', UserController::class);
    });
});

// -----------------------------
// APK Download Routes (Place BEFORE project routes)
// -----------------------------
Route::prefix('apks')->group(function() {
    Route::post('/download/{apk}', [ProjectPublicController::class,'apiDownload'])->name('apks.download');
    Route::get('/history/{apk}', [ProjectPublicController::class,'apiDownloadHistory'])->name('apks.history');
    Route::get('/stats', [ProjectPublicController::class,'apiStats'])->name('apks.stats');
});

// -----------------------------
// Public Project Routes (Unified Login for Users/Admin)
// -----------------------------
Route::prefix('{project_slug}')->group(function () {
    Route::get('list', [ProjectPublicController::class, 'list'])->name('project.list');
    Route::post('list', [ProjectPublicController::class, 'loginAndList'])->name('project.loginAndList');

    Route::get('upload', [ProjectPublicController::class, 'uploadForm'])->name('project.uploadForm');
    Route::post('upload-login', [ProjectPublicController::class, 'loginAndUpload'])->name('project.loginAndUpload');
    Route::post('upload', [ProjectPublicController::class, 'uploadStore'])->name('project.uploadStore');
});