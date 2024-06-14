<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionMedicine extends Model
{
    use HasFactory;

    protected $fillable = ['dateOfEnd', 'dateOfStart','amount', 'details', 'status', 'prescriptionID', 'medicineID','valid'];

  
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescriptionID', 'id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicineID', 'id');
    }
}
