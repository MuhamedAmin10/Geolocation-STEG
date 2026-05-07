<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'mission_id',
        'submitted_by',
        'rating',
        'comment',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
