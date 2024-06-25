<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursedMaterialsUser extends Model
{
    use HasFactory;
    protected $fillable = ['quantity', 'status', 'userID', 'centerID', 'disbursedMaterialID','valid'];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
    
    public function medicalCenter()
    {
        return $this->belongsTo(MedicalCenter::class, 'centerID', 'id');
    }
    
    public function disbursedMaterial()
    {
        return $this->belongsTo(DisbursedMaterial::class, 'disbursedMaterialID', 'id');
    }


    public function globalRequests()
{
    return $this->morphMany(GlobalRequest::class, 'requestable');
}

}
