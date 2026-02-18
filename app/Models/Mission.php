<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_id',
        'type_mission',
        'priorite',
        'description',
        'statut',
        'created_by',
        'due_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function referencePoint()
    {
        return $this->belongsTo(ReferencePoint::class, 'reference_id');
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
}