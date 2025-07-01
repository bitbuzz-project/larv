<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Students\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Students\ProfileController;
use App\Http\Controllers\Students\SituationPedagogiqueController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\StudentModuleController; // Add this line
use App\Http\Controllers\Admin\NoteController;
use App\Http\Controllers\Students\StudentsNoteController;
use App\Http\Controllers\Admin\ReportController; // ADD THIS LINE


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

// Vérification que les routes d'import de notes sont bien définies
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Route principale pour afficher le formulaire d'import
    Route::get('/notes-import', [NoteController::class, 'showImport'])->name('notes.import');

    // Route pour l'import ODS (POST)
    Route::post('/notes-import', [NoteController::class, 'import'])->name('notes.import');

    // Route pour l'import CSV (POST)
    Route::post('/notes-import-csv', [NoteController::class, 'importCsv'])->name('notes.import.csv');

    // Route pour afficher les résultats
    Route::get('/notes-import-results', [NoteController::class, 'importResults'])->name('notes.import.results');

    // Route de test pour diagnostiquer les problèmes
    Route::post('/notes-import-test', function(Request $request) {
        return response()->json([
            'success' => true,
            'message' => 'Route de test fonctionnelle',
            'request_data' => [
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'csrf_token' => $request->header('X-CSRF-TOKEN') ? 'présent' : 'manquant',
                'files' => $request->hasFile('file') ? 'fichier présent' : 'aucun fichier',
                'import_type' => $request->input('import_type'),
                'annee_scolaire' => $request->input('annee_scolaire')
            ]
        ]);
    })->name('notes.import.test');
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
    // Notes Management (add this in the admin routes section)
    Route::get('/notes-import', [NoteController::class, 'showImport'])->name('notes.import');
    Route::post('/notes-import', [NoteController::class, 'import'])->name('notes.import');
    Route::get('/notes-import-results', [NoteController::class, 'importResults'])->name('notes.import.results');
    Route::post('/notes-import-csv', [NoteController::class, 'importCsv'])->name('notes.import.csv');

    // Module Management
    Route::resource('modules', ModuleController::class);

    // Module Import/Export routes
    Route::get('/modules-import', [ModuleController::class, 'showImport'])->name('modules.import');
    Route::post('/modules-import', [ModuleController::class, 'import'])->name('modules.import');
    Route::get('/modules-import-results', [ModuleController::class, 'importResults'])->name('modules.import.results');
    // Student Module Import/Export routes (NEW)
    Route::get('/student-modules-import', [StudentModuleController::class, 'showImport'])->name('student-modules.import'); // Add this line
    Route::post('/student-modules-import', [StudentModuleController::class, 'import'])->name('student-modules.import'); // Add this line
    Route::get('/student-modules-import-results', [StudentModuleController::class, 'importResults'])->name('student-modules.import.results'); // Add this line
    Route::get('/admin/student-modules/process-chunk/{importId}', [App\Http\Controllers\Admin\StudentModuleController::class, 'processChunk'])
        ->name('admin.student-modules.process-chunk');
     // Reports and Analytics Routes (ADD THESE LINES)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/detail/{area}', [ReportController::class, 'detailAnalytics'])->name('reports.detail');


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

    Route::get('/notes', [\App\Http\Controllers\Students\StudentsNoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/{noteId}/{table?}', [\App\Http\Controllers\Students\StudentsNoteController::class, 'show'])->name('notes.show');
    // NEW: Student Modules Routes
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Students\ModulesController::class, 'index'])->name('index');
        Route::get('/current-session', [\App\Http\Controllers\Students\ModulesController::class, 'currentSession'])->name('current-session');
        Route::get('/show/{module}', [\App\Http\Controllers\Students\ModulesController::class, 'show'])->name('show');
        Route::get('/export-pdf', [\App\Http\Controllers\Students\ModulesController::class, 'exportPdf'])->name('export-pdf');
    });

});


// Fallback route for testing
Route::fallback(function () {
    return redirect()->route('login');
});
