<?php

namespace Hexters\CoinPayment\Entities;

trait CoinPaymentuserRelation
{

    public function coinpayment_transactions()
    {
        return $this->hasMany(cointpayment_log_trx::class, 'user_id');
    }

    //custom model
    public function coinpayment_merchant_transactions()
    {
        return $this->hasMany(cointpayment_log_trx::class, 'merchant_id');
    }

}
