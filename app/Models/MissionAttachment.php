<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id',
        'affectation_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
