<?php

namespace App\Models;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $table    = 'fees';
    protected $fillable = ['transaction_type', 'charge_percentage', 'charge_fixed', 'payment_method_id'];
    public $timestamps = false;

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
