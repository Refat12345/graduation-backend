<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['noteContent', 'category', 'type', 'date', 
    'sessionID', 'senderID', 'receiverID', 'centerID'];

  
    public function dialysisSession()
    {
        return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'senderID', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverID', 'id');
    }

    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }


}
