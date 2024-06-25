<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chair extends Model
{
    use HasFactory;

    protected $fillable = ['chairNumber', 'roomName', 'centerID','valid'];
    
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }

    public function requestable()
    {
        return $this->morphTo();
    }
}
