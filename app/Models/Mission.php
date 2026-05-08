<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\MissionReferenceScan;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'client_id',
        'type_mission',
        'priorite',
        'description',
        'statut',
        'created_by',
        'due_at',
        'started_at',
        'completed_at',
        'total_working_time',
        'estimated_duration',
        'travel_time_minutes',
        'on_site_time_minutes',
        'efficiency_score',
        'sla_level',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'efficiency_score' => 'float',
    ];

    public function referencePoint()
    {
        return $this->belongsTo(ReferencePoint::class, 'reference_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function clientReclamation()
    {
        return $this->hasOne(ClientReclamation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function currentAffectation()
    {
        return $this->hasOne(Affectation::class)->latestOfMany('assigned_at');
    }

    public function timeLogs()
    {
        return $this->hasMany(MissionTimeLog::class);
    }

    public function attachments()
    {
        return $this->hasMany(MissionAttachment::class)->latest();
    }

    public function referenceScans()
    {
        return $this->hasMany(MissionReferenceScan::class)->latest('scanned_at');
    }

    public function latestReferenceScan()
    {
        return $this->hasOne(MissionReferenceScan::class)->latestOfMany('scanned_at');
    }

    public function clientFeedback()
    {
        return $this->hasOne(ClientFeedback::class);
    }

    public function expectedDurationMinutes(): int
    {
        if ($this->estimated_duration) {
            return (int) $this->estimated_duration;
        }

        return match ($this->sla_level) {
            'Platinum' => 120,
            'Gold' => 240,
            'Silver' => 360,
            default => 480,
        };
    }

    public function elapsedWorkingMinutes(): int
    {
        if ($this->total_working_time !== null) {
            return (int) $this->total_working_time;
        }

        if ($this->started_at === null) {
            return 0;
        }

        $end = $this->completed_at ?? Carbon::now();

        return Carbon::parse($this->started_at)->diffInMinutes($end);
    }

    public function slaState(): string
    {
        $expected = $this->expectedDurationMinutes();
        if ($expected <= 0) {
            return 'green';
        }

        $actual = $this->elapsedWorkingMinutes();
        if ($actual >= $expected) {
            return 'red';
        }

        if ($actual >= (int) floor($expected * 0.8)) {
            return 'yellow';
        }

        return 'green';
    }
}