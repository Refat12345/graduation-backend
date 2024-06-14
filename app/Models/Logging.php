<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Logging extends Model
{
    protected $table = 'logging';
    use HasFactory;

    protected $fillable = [
        'operation',
        'destinationOfOperation',
        'oldData',
        'newData',
    
        'affectedUserID',
        'affectorUserID',
        'sessionID','valid'
    ];


public function affectedUser()
{
    return $this->belongsTo(User::class, 'affectedUserID', 'id');
}


public function affectorUser()
{
    return $this->belongsTo(User::class, 'affectorUserID', 'id');
}


public function session()
{
    return $this->belongsTo(DialysisSession::class, 'sessionID', 'id');
}

}
