<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAnalysis extends Model
{
    use HasFactory;

    protected $fillable = ['averageMin', 'averageMax', 'value', 'analysisDate', 'notes', 'quarter', 'analysisTypeID', 'userID','valid'];
    


    
    public function analysisType()
    {
        return $this->belongsTo(AnalysisType::class, 'analysisTypeID', 'id');
    }


    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }
}
