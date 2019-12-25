<?php

namespace App\Models;

use App\Models\Currency;
use DB;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table    = 'wallets';
    protected $fillable = ['user_id', 'currency_id', 'balance', 'is_default'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function active_currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id')->where('status', 'Active');
    }

    public function currency_exchanges()
    {
        return $this->hasMany(CurrencyExchange::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function walletBalance()
    {
        $data = $this->leftJoin('currencies', 'currencies.id', '=', 'wallets.currency_id')
            ->select(DB::raw('SUM(wallets.balance) as amount,wallets.currency_id,currencies.code, currencies.symbol'))
            ->groupBy('wallets.currency_id')
            ->get();

        $array_data = [];
        foreach ($data as $row)
        {
            // $array_data[$row->code] = decimalFormat($row->amount);
            $array_data[$row->code] = moneyFormat($row->symbol, formatNumber($row->amount));
        }
        return $array_data;

    }

    //Query for Mobile Application - starts
    public function getAvailableBalance($user_id)
    {
        $conditions = ['wallets.user_id' => $user_id];
        $wallets = Wallet::join('currencies', function ($join)
        {
            $join->on('currencies.id', '=', 'wallets.currency_id');
        })
        ->where($conditions)
        ->orderBy('wallets.balance', 'ASC')
        ->select([
            'wallets.balance as balance',
            'wallets.is_default as is_default',
            'currencies.name as curr_name',
            'currencies.code as curr_code',
        ])
        ->get();
        return $wallets;
    }
    //Query for Mobile Application - ends
}
