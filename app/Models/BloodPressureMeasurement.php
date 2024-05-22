<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodPressureMeasurement extends Model
{
    use HasFactory;

    protected $fillable = ['pressureValue', 'pulseValue', 'time', 'sessionID'];

    public function dialysisSession(): BelongsTo
    {
        return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
    }
}
