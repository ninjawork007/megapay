<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
	protected $table = 'qr_codes';

    protected $fillable = ['user_id', 'type', 'qr_code', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
