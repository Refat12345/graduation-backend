<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCenter extends Model
{
    use HasFactory;
    protected $fillable = ['userID', 'centerID','valid'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
    
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }



}
