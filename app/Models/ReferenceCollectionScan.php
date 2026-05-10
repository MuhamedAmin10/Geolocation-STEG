<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceCollectionScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_point_id',
        'technicien_id',
        'reference_code',
        'meter_type',
        'latitude',
        'longitude',
        'accuracy_m',
        'notes',
        'was_created',
        'scanned_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy_m' => 'float',
        'was_created' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function referencePoint()
    {
        return $this->belongsTo(ReferencePoint::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Technicien::class);
    }
}