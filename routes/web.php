<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Student\DashboardController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/students-export', [AdminStudentController::class, 'export'])->name('students.export');
});

// Student Routes
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Fallback route for testing
Route::fallback(function () {
    return redirect()->route('login');
});
