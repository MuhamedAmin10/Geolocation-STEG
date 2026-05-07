<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'in_app',
        'email',
        'sms',
        'whatsapp',
        'mission_assigned',
        'status_changed',
        'sla_breached',
        'time_reminder',
    ];

    protected $casts = [
        'in_app' => 'boolean',
        'email' => 'boolean',
        'sms' => 'boolean',
        'whatsapp' => 'boolean',
        'mission_assigned' => 'boolean',
        'status_changed' => 'boolean',
        'sla_breached' => 'boolean',
        'time_reminder' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
