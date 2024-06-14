<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineTaken extends Model
{
    use HasFactory;

    protected $fillable = ['value', 'sessionID', 'medicineID','valid'];


    public function dialysisSession()
    {
        return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicineID', 'id');
    }


  
}
