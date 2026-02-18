<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\ReferencePointController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MissionAssignmentController;
use App\Http\Controllers\Admin\TechnicienController as AdminTechnicienController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('reference.search');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/references/search', [ReferencePointController::class, 'index'])->name('reference.search');
    Route::get('/api/references/{reference}', [ReferencePointController::class, 'showByReference'])->name('api.reference.show');

    Route::get('/reference-points/{referencePoint}/edit', [ReferencePointController::class, 'edit'])
        ->name('reference-points.edit');
    Route::put('/reference-points/{referencePoint}', [ReferencePointController::class, 'update'])
        ->name('reference-points.update');
    Route::delete('/reference-points/{referencePoint}', [ReferencePointController::class, 'destroy'])
        ->name('reference-points.destroy');

    Route::patch('/missions/{mission}/work', [MissionController::class, 'updateWork'])->name('missions.work.update');
    Route::resource('missions', MissionController::class);

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('techniciens', AdminTechnicienController::class)->except(['show']);
        Route::post('/missions/{mission}/assign', [MissionAssignmentController::class, 'store'])->name('missions.assign');
    });
});

require __DIR__.'/auth.php';
