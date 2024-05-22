<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCenter extends Model
{
    use HasFactory;
    protected $fillable = ['userID', 'centerID'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
    
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }



}
