<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = ['noteContent', 'category', 'type', 'date', 
    'sessionID', 'senderID', 'receiverID', 'centerID'];

  
    public function dialysisSession(): BelongsTo
    {
        return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'senderID', 'id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiverID', 'id');
    }

    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }


}
