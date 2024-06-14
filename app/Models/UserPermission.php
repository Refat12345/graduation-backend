<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;


    protected $fillable = ['userID', 'permissionID','valid'];

    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionID','id');
    }
}
