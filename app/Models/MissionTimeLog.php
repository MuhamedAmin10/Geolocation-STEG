<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionTimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'technician_id',
        'action',
        'logged_at',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technicien::class, 'technician_id');
    }
}
