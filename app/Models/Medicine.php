<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'titer','valid'];

    public function prescriptionMedicine()
    {
        return $this->hasOne(PrescriptionMedicine::class, 'medicineID', 'id');
    }



    public function prescriptions()
    {
        return $this->belongsToMany(Prescription::class, 'prescription_medicines', 'medicineID', 'prescriptionID')
                    ->withPivot('dateOfStart', 'dateOfEnd', 'amount', 'details', 'status', 'medicineID');
    }
}

 




