<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalRequest extends Model
{
    protected $fillable = ['content', 'direction', 'requestID', 'requesterID', 'reciverID'];
    
    public function request(): BelongsTo
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
    
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesterID', 'id');
    }
    
    public function reciver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reciverID', 'id');
    }

    use HasFactory;
}
