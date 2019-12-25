<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutSetting extends Model
{
    protected $table = 'payout_settings';

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'id', 'type');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
