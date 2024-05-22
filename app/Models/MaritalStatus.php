<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaritalStatus extends Model
{
    use HasFactory;

    protected $fillable = ['childrenNumber', 'healthStateChildren', 'generalPatientInformationID'];
    
    public function generalPatientInformation(): BelongsTo
    {
        return $this->belongsTo(GeneralPatientInformation::class, 'generalPatientInformationID', 'id');
    }
}
