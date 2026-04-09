<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ReferencePointController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MissionAssignmentController;
use App\Http\Controllers\Admin\TechnicienController as AdminTechnicienController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Legacy redirect: old search URL now goes to dashboard
Route::get('/references/search', function () {
    return redirect()->route('dashboard');
})->middleware(['auth'])->name('reference.search');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/api/references/{reference}', [ReferencePointController::class, 'showByReference'])
        ->middleware('throttle:30,1')
        ->name('api.reference.show');

    Route::get('/reference-points/create', [ReferencePointController::class, 'create'])
        ->middleware('can:manage-references')
        ->name('reference-points.create');
    Route::post('/reference-points', [ReferencePointController::class, 'store'])
        ->middleware('can:manage-references')
        ->name('reference-points.store');
    Route::get('/reference-points/{referencePoint}/edit', [ReferencePointController::class, 'edit'])
        ->middleware('can:manage-references')
        ->name('reference-points.edit');
    Route::put('/reference-points/{referencePoint}', [ReferencePointController::class, 'update'])
        ->middleware('can:manage-references')
        ->name('reference-points.update');
    Route::delete('/reference-points/{referencePoint}', [ReferencePointController::class, 'destroy'])
        ->middleware('can:manage-references')
        ->name('reference-points.destroy');

    Route::patch('/missions/{mission}/work', [MissionController::class, 'updateWork'])->name('missions.work.update');
    Route::get('/missions-analysis', [MissionController::class, 'analysis'])->name('missions.analysis');
    Route::get('/missions-analysis/export', [MissionController::class, 'analysisExportPdf'])->name('missions.analysis.export');
    Route::resource('missions', MissionController::class);

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analysis', [AdminDashboardController::class, 'analysis'])->name('analysis');
        Route::resource('techniciens', AdminTechnicienController::class)->except(['show']);
        Route::post('/missions/{mission}/assign', [MissionAssignmentController::class, 'store'])->name('missions.assign');
    });
});

require __DIR__.'/auth.php';
