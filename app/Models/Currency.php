<?php

namespace App\Models;

use App\Models\Transaction;
use DB;
use Illuminate\Database\Eloquent\Model;
use Session;

class Currency extends Model
{
    protected $table    = 'currencies';

    protected $fillable = ['name', 'symbol', 'code', 'hundreds_name', 'rate', 'logo', 'status', 'default', 'exchange_from'];

    protected $appends  = ['org_symbol'];

    public $timestamps  = false;

    // mutators and accessors below
    public function getSessionCodeAttribute()
    {
        if (Session::get('currencies'))
        {
            return Session::get('currencies');
        }
        else
        {
            return DB::table('currencies')->where('default', 1)->first()->code;
        }
    }

    public static function code_to_symbol($code)
    {
        $symbol = DB::table('currencies')->where('code', $code)->first()->symbol;
        return $symbol;
    }

    // Appending Values To JSON
    public function getOrgSymbolAttribute()
    {
        $symbol = $this->attributes['symbol'];
        return $symbol;
    }

    /**
     * Relationships below
     */
    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'currency_id');
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'currency_id');
    }

    public function currency_exchange()
    {
        return $this->hasOne(CurrencyExchange::class, 'currency_id');
    }

    public function voucher()
    {
        return $this->hasOne(Voucher::class, 'currency_id');
    }

    public function payment_request()
    {
        return $this->hasOne(RequestPayment::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'currency_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'currency_id');
    }

    public function fees_limit()
    {
        return $this->hasMany(FeesLimit::class,'currency_id');
    }

    public function currency_payment_method()
    {
        return $this->hasOne(CurrencyPaymentMethod::class, 'currency_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'currency_id');
    }

    //pm_v2.3
    public function merchant()
    {
        return $this->hasOne(Merchant::class, 'currency_id');
    }
}
