<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferencePoint extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'latitude',
        'longitude',
        'adresse',
        'gouvernorat',
        'delegation',
        'precision_m',
        'statut',
        'updated_by',
    ];

    public function missions()
    {
        return $this->hasMany(Mission::class, 'reference_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}