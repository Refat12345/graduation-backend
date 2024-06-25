<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalRequest extends Model
{
    
    protected $fillable = ['content', 'direction', 'requestID', 'requesterID', 'reciverID','valid'];
    
    public function request()
    {
        return $this->belongsTo(Requests::class, 'requestID', 'id');
    }
    
    public function requester()
    {
        return $this->belongsTo(User::class, 'requesterID', 'id');
    }
    
    public function reciver()
    {
        return $this->belongsTo(User::class, 'reciverID', 'id');
    }

    use HasFactory;


    public function globalRequests()
    {
        return $this->morphMany(GlobalRequest::class, 'requestable');
    }
    
}
