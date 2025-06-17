<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Students\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\ProfileController;
use App\Http\Controllers\Students\SituationPedagogiqueController;
use App\Http\Controllers\Admin\ModuleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Default redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (using custom Student authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [StudentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'login']);
});

// Logout route (for authenticated users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Student Management
    Route::resource('students', AdminStudentController::class);

    // Student Import/Export routes
    Route::get('/students-import', [AdminStudentController::class, 'showImport'])->name('students.import');
    Route::post('/students-import', [AdminStudentController::class, 'import'])->name('students.import');
    Route::get('/students-import-results', [AdminStudentController::class, 'importResults'])->name('students.import.results');
    Route::get('/students-export', [AdminStudentController::class, 'export'])->name('students.export');


    // Module Management
    Route::resource('modules', ModuleController::class);

    // Module Import/Export routes
    Route::get('/modules-import', [ModuleController::class, 'showImport'])->name('modules.import');
    Route::post('/modules-import', [ModuleController::class, 'import'])->name('modules.import');
    Route::get('/modules-import-results', [ModuleController::class, 'importResults'])->name('modules.import.results');



});

// Student Routes
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('profile.change-password');
    Route::post('/profile/change-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
        // Situation Pédagogique Routes
    Route::get('/situation-pedagogique', [SituationPedagogiqueController::class, 'index'])->name('situation-pedagogique.index');
    Route::get('/situation-pedagogique/{year}', [SituationPedagogiqueController::class, 'showYear'])->name('situation-pedagogique.year');

});

// Fallback route for testing
Route::fallback(function () {
    return redirect()->route('login');
});
