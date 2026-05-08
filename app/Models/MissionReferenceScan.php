<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionReferenceScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'reference_point_id',
        'technicien_id',
        'reference_code',
        'compteur_type',
        'latitude',
        'longitude',
        'accuracy_m',
        'distance_m',
        'is_match',
        'notes',
        'scanned_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_m' => 'float',
        'distance_m' => 'float',
        'is_match' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function referencePoint()
    {
        return $this->belongsTo(ReferencePoint::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Technicien::class);
    }
}