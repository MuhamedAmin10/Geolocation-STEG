<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\MissionTimeLog;
use App\Models\Technicien;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MissionTimeTrackingController extends Controller
{
    public function log(Request $request, Mission $mission): JsonResponse
    {
        Gate::authorize('work-mission', $mission);

        $data = $request->validate([
            'action' => ['required', 'in:start_work,pause,resume,complete,break_start,break_end'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $technicianId = Technicien::query()
            ->where('user_id', $request->user()->id)
            ->value('id');

        if (!$technicianId) {
            abort(403);
        }

        $timestamp = now();

        if (in_array($data['action'], ['start_work', 'resume', 'complete'], true)) {
            $distance = $this->distanceToMissionMeters($mission, $data['latitude'] ?? null, $data['longitude'] ?? null);

            if ($distance === null) {
                throw ValidationException::withMessages([
                    'geofence' => 'GPS position is required for this action.',
                ]);
            }

            if ($distance > 50) {
                return response()->json([
                    'message' => 'Out of range: you must be within 50m of the reference point.',
                    'distance_meters' => round($distance, 1),
                ], 422);
            }
        }

        DB::transaction(function () use ($mission, $technicianId, $data, $timestamp) {
            MissionTimeLog::query()->create([
                'mission_id' => $mission->id,
                'technician_id' => $technicianId,
                'action' => $data['action'],
                'logged_at' => $timestamp,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($data['action'] === 'start_work') {
                if ($mission->started_at === null) {
                    $mission->started_at = $timestamp;
                }
                $mission->statut = 'En cours';
            }

            if ($data['action'] === 'complete') {
                if ($mission->started_at === null) {
                    $mission->started_at = $timestamp;
                }

                $mission->completed_at = $timestamp;
                $mission->statut = 'Terminée';
            }

            $this->refreshComputedTimes($mission, $technicianId, $timestamp, $data['action'] === 'complete');
            $mission->save();
        });

        $mission->refresh();

        return response()->json([
            'message' => 'Temps mis a jour.',
            'mission' => [
                'id' => $mission->id,
                'statut' => $mission->statut,
                'started_at' => optional($mission->started_at)->toIso8601String(),
                'completed_at' => optional($mission->completed_at)->toIso8601String(),
                'total_working_time' => $mission->total_working_time,
                'on_site_time_minutes' => $mission->on_site_time_minutes,
                'travel_time_minutes' => $mission->travel_time_minutes,
                'efficiency_score' => $mission->efficiency_score,
            ],
        ]);
    }

    private function distanceToMissionMeters(Mission $mission, ?float $latitude, ?float $longitude): ?float
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $reference = $mission->referencePoint()->first(['latitude', 'longitude']);
        if (!$reference || $reference->latitude === null || $reference->longitude === null) {
            return null;
        }

        $earthRadius = 6371000;
        $latFrom = deg2rad((float) $reference->latitude);
        $lonFrom = deg2rad((float) $reference->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $earthRadius * $angle;
    }

    private function refreshComputedTimes(Mission $mission, int $technicianId, Carbon $at, bool $isComplete): void
    {
        $logs = MissionTimeLog::query()
            ->where('mission_id', $mission->id)
            ->where('technician_id', $technicianId)
            ->orderBy('logged_at')
            ->get(['action', 'logged_at']);

        $activeStart = null;
        $pauseStart = null;
        $breakStart = null;

        $onSiteMinutes = 0;
        $pausedMinutes = 0;
        $breakMinutes = 0;
        $firstStartAt = null;

        foreach ($logs as $log) {
            $loggedAt = Carbon::parse($log->logged_at);

            if ($log->action === 'start_work') {
                $firstStartAt ??= $loggedAt;
                $activeStart ??= $loggedAt;
                $pauseStart = null;
                continue;
            }

            if ($log->action === 'resume') {
                $activeStart ??= $loggedAt;

                if ($pauseStart) {
                    $pausedMinutes += $pauseStart->diffInMinutes($loggedAt);
                    $pauseStart = null;
                }

                continue;
            }

            if ($log->action === 'pause') {
                if ($activeStart) {
                    $onSiteMinutes += $activeStart->diffInMinutes($loggedAt);
                    $activeStart = null;
                }
                $pauseStart ??= $loggedAt;
                continue;
            }

            if ($log->action === 'break_start') {
                if ($activeStart) {
                    $onSiteMinutes += $activeStart->diffInMinutes($loggedAt);
                    $activeStart = null;
                }
                $breakStart ??= $loggedAt;
                continue;
            }

            if ($log->action === 'break_end') {
                if ($breakStart) {
                    $breakMinutes += $breakStart->diffInMinutes($loggedAt);
                    $breakStart = null;
                }
                $activeStart ??= $loggedAt;
                continue;
            }

            if ($log->action === 'complete') {
                if ($activeStart) {
                    $onSiteMinutes += $activeStart->diffInMinutes($loggedAt);
                    $activeStart = null;
                }

                if ($pauseStart) {
                    $pausedMinutes += $pauseStart->diffInMinutes($loggedAt);
                    $pauseStart = null;
                }

                if ($breakStart) {
                    $breakMinutes += $breakStart->diffInMinutes($loggedAt);
                    $breakStart = null;
                }
            }
        }

        if (!$isComplete && $activeStart) {
            $onSiteMinutes += $activeStart->diffInMinutes($at);
        }

        if (!$isComplete && $pauseStart) {
            $pausedMinutes += $pauseStart->diffInMinutes($at);
        }

        if (!$isComplete && $breakStart) {
            $breakMinutes += $breakStart->diffInMinutes($at);
        }

        $travelTime = 0;
        if ($mission->started_at && $firstStartAt) {
            $travelTime = max(0, Carbon::parse($mission->started_at)->diffInMinutes($firstStartAt));
        }

        $totalWorking = max(0, $onSiteMinutes + $travelTime);

        $mission->on_site_time_minutes = $onSiteMinutes;
        $mission->travel_time_minutes = $travelTime;
        $mission->total_working_time = $totalWorking;

        if ($mission->estimated_duration && $totalWorking > 0) {
            $mission->efficiency_score = round(($mission->estimated_duration / $totalWorking) * 100, 2);
        }

        // Keep pause/break durations as metadata in a single trail note for quick analytics fallback.
        $mission->setAttribute('updated_at', now());
    }
}
