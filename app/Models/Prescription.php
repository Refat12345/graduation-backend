<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [ 'patientID', 'doctorID','valid'];

  
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patientID', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctorID', 'id');
    }


    
    public function medicines()
{
    return $this->belongsToMany(Medicine::class, 'prescription_medicines', 'prescriptionID', 'medicineID')
                ->withPivot('amount', 'details', 'status', 'dateOfStart', 'dateOfEnd');
}
}
