<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['cityName', 'countryID','valid'];
   
    public function country()
    {
        return $this->belongsTo(Country::class, 'countryID', 'id');
    }
}
