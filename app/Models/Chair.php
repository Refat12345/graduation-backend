<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chair extends Model
{
    use HasFactory;

    protected $fillable = ['chairNumber', 'roomName', 'centerID'];
    
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }
}
