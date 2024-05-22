<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'titer'];

    public function prescriptionMedicine()
    {
        return $this->hasOne(PrescriptionMedicine::class, 'medicineID', 'id');
    }

 
}



