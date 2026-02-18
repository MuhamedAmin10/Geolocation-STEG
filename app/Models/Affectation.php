<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'technicien_id',
        'assigned_by',
        'assigned_at',
        'rapport',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function technicien()
    {
        return $this->belongsTo(Technicien::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}