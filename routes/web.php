<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionReferenceScanController;
use App\Http\Controllers\MissionMapController;
use App\Http\Controllers\MissionTimeTrackingController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\ReferenceCollectionController;
use App\Http\Controllers\ReferencePointController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReclamationController as AdminReclamationController;
use App\Http\Controllers\Admin\MissionAssignmentController;
use App\Http\Controllers\Admin\TechnicienController as AdminTechnicienController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    if (strcasecmp(trim((string) $user?->role), 'Client') === 0) {
        return redirect()->route('client.portal');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/references/search', function () {
    return view('references.search');
})->middleware(['auth'])->name('reference.search');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/client/portal', [ClientPortalController::class, 'index'])
        ->name('client.portal');
    Route::post('/client/feedback', [ClientPortalController::class, 'storeFeedback'])
        ->name('client.feedback.store');
    Route::post('/client/reclamations', [ClientPortalController::class, 'storeReclamation'])
        ->name('client.reclamations.store');

    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'edit'])
        ->name('notification-preferences.edit');
    Route::put('/notification-preferences', [NotificationPreferenceController::class, 'update'])
        ->name('notification-preferences.update');

    Route::get('/api/references/{reference}', [ReferencePointController::class, 'showByReference'])
        ->middleware('throttle:30,1')
        ->name('api.reference.show');

    Route::get('/references/collect', [ReferenceCollectionController::class, 'index'])
        ->name('references.collect');
    Route::post('/references/collect', [ReferenceCollectionController::class, 'store'])
        ->name('references.collect.store');

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
    Route::post('/missions/{mission}/time-log', [MissionTimeTrackingController::class, 'log'])->name('missions.time-log');
    Route::post('/missions/{mission}/verify-qr', [MissionController::class, 'verifyQr'])->name('missions.verify-qr');
    Route::post('/missions/{mission}/reference-scans', [MissionReferenceScanController::class, 'store'])->name('missions.reference-scans');
    Route::get('/technician/schedule', [MissionController::class, 'technicianSchedule'])->name('technician.schedule');
    Route::get('/technician/time-tracker', [MissionController::class, 'technicianTimeTracker'])->name('technician.tracker');
    Route::get('/missions-analysis', [MissionController::class, 'analysis'])->name('missions.analysis');
    Route::get('/missions-analysis/export', [MissionController::class, 'analysisExportPdf'])->name('missions.analysis.export');
    Route::get('/missions/export-technician-csv', [MissionController::class, 'exportTechnicianCsv'])
        ->name('missions.export-technician-csv');
    Route::get('/missions-map', [MissionMapController::class, 'index'])
        ->middleware('can:manage-missions')
        ->name('missions.map');
    Route::resource('missions', MissionController::class);

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/analysis', [AdminDashboardController::class, 'analysis'])->name('analysis');
        Route::resource('techniciens', AdminTechnicienController::class)->except(['show']);
        Route::post('/missions/{mission}/assign', [MissionAssignmentController::class, 'store'])->name('missions.assign');
        Route::post('/reclamations/{reclamation}/assign', [AdminReclamationController::class, 'assign'])->name('reclamations.assign');
    });
});

require __DIR__.'/auth.php';
