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
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('logout',  [AdminAuthController::class, 'logout'])->name('logout');

    // Authenticated admin routes
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
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
// Global Logout Route
// -----------------------------
Route::post('/logout', [ProjectPublicController::class, 'logout'])->name('logout');

// -----------------------------
// Public Project Routes (Unified Login for Users/Admin)
// -----------------------------
Route::prefix('projects/{project_slug}')->name('project.')->group(function () {
    // List APKs
    Route::get('/', [ProjectPublicController::class, 'list'])->name('list');
    Route::post('/login', [ProjectPublicController::class, 'loginAndList'])->name('loginAndList');

    // Upload APKs
    Route::get('/upload', [ProjectPublicController::class, 'uploadForm'])->name('uploadForm');
    Route::post('/upload-login', [ProjectPublicController::class, 'loginAndUpload'])->name('loginAndUpload');
    Route::post('/upload', [ProjectPublicController::class, 'uploadStore'])->name('uploadStore');

    // Project-specific logout
    Route::post('/logout', [ProjectPublicController::class, 'logout'])->name('logout');
});

// -----------------------------
// Fallback route for old URLs
// -----------------------------
Route::get('/{project_slug}/list', function($project_slug) {
    return redirect()->route('project.list', $project_slug);
});
Route::get('/{project_slug}/upload', function($project_slug) {
    return redirect()->route('project.uploadForm', $project_slug);
});