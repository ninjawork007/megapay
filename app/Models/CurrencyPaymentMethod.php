<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyPaymentMethod extends Model
{
	protected $table = 'currency_payment_methods';

    protected $fillable = [
        'currency_id',
        'method_id',
        'activated_for',
        'method_data',
        'processing_time',
    ];

    // protected $casts = [
    //     'method_data' => 'array'
    // ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }
}
