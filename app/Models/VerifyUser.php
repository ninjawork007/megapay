<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerifyUser extends Model
{
    protected $table    = 'verify_users';
    protected $fillable = ['user_id', 'token'];

    // new
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
