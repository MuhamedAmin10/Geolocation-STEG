<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientReclamation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'reference_id',
        'compteur_reference',
        'subject',
        'description',
        'status',
        'mission_id',
        'handled_by',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function referencePoint()
    {
        return $this->belongsTo(ReferencePoint::class, 'reference_id');
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}