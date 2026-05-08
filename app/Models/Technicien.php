<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MissionReferenceScan;

class Technicien extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'prenom',
        'telephone',
        'zone_intervention',
        'competences',
        'disponible',
    ];

    protected $casts = [
        'disponible' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }

    public function referenceScans()
    {
        return $this->hasMany(MissionReferenceScan::class)->latest('scanned_at');
    }
}