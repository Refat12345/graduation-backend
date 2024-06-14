<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShift extends Model
{
    use HasFactory;

    protected $fillable = ['shiftID', 'userID','valid'];
    
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shiftID', 'id');
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
}
