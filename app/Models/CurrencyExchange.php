<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\Wallet;
use DB;
use Illuminate\Database\Eloquent\Model;

class CurrencyExchange extends Model
{
    protected $table    = 'currency_exchanges';
    public $timestamps  = true;
    protected $fillable = [
        'user_id',
        'from_wallet',
        'to_wallet',
        'currency_id',
        'uuid',
        'exchange_rate',
        'amount',
        'type',
        'status',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    /**
     * [all exchanges data]
     * @return [void] [query]
     */
    public function getAllExchanges()
    {
        return $this->leftJoin('currencies', 'currencies.id', '=', 'currency_exchanges.currency_id')
            ->select('currency_exchanges.*', 'currencies.code')
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user [id]
     * @return [string]       [firstname and lastname]
     */
    public function getExchangesUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'currency_exchanges.user_id')
            ->where(['currency_exchanges.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search [query string]
     * @return [string]       [distinct firstname and lastname]
     */
    public function getExchangesUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'currency_exchanges.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'currency_exchanges.user_id')
            ->get();
    }

    public function getExchangesList($from, $to, $status, $currency, $user)
    {
        // dd($from);
        if (empty($from) || empty($to)){
            $date_range = null;
        }
        else if (empty($from)){
            $date_range = null;
        }
        else if (empty($to)){
            $date_range = null;
        }
        else{
            $date_range = 'Available';
        }

        /*$condtionsWithoutDate start*/
            $condtionsWithoutDate = " detail.id != 0";
            if ($status != 'all')
            {
                $condtionsWithoutDate .= " AND detail.status = '$status'";
            }
            if ($currency != 'all')
            {
                if ($user)
                {
                    $condtionsWithoutDate .= " AND detail.from_currecny_id = '$currency' AND detail.user_id = '$user' OR detail.to_currecny_id = '$currency' AND detail.user_id = '$user' ";
                }
                else
                {
                    $condtionsWithoutDate .= " AND detail.from_currecny_id = '$currency' OR detail.to_currecny_id = '$currency'";
                }
            }
            else
            {
                if ($user)
                {
                    $condtionsWithoutDate .= " AND detail.user_id = '$user' ";
                }
            }
        /*$condtionsWithoutDate end*/

        /*$condtionWithDate start*/
            $condtionWithDate = " detail.id != 0";
            if (!empty($from) || !empty($to))
            {
                $condtionWithDate .= " AND DATE(detail.created_at) BETWEEN '$from' AND '$to'";
            }
            if ($status != 'all')
            {
                $condtionWithDate .= " AND detail.status = '$status'";
            }
            if ($currency != 'all')
            {
                if ($user)
                {
                    $condtionWithDate .= " AND detail.from_currecny_id = '$currency' AND detail.user_id = '$user' OR detail.to_currecny_id = '$currency' AND detail.user_id = '$user' ";
                }
                else
                {
                    $condtionWithDate .= " AND detail.from_currecny_id = '$currency' OR detail.to_currecny_id = '$currency'";
                }
            }
            else
            {
                if ($user)
                {
                    $condtionWithDate .= " AND detail.user_id = '$user' ";
                }
            }
        /*$condtionWithDate end*/

        if (!empty($date_range))
        {
            $data = DB::select("
                SELECT
                detail.*,
                fc.code as fc_code,
                tc.code as tc_code,
                fc.symbol as fc_symbol,
                tc.symbol as tc_symbol,
                users.first_name, users.last_name

                FROM(SELECT
                ce.*,
                from_wallet.currency_id as from_currecny_id,
                to_wallet.currency_id as to_currecny_id
                FROM currency_exchanges as ce
                LEFT JOIN wallets as from_wallet ON from_wallet.id = from_wallet
                LEFT JOIN wallets as to_wallet ON to_wallet.id = to_wallet)

                as detail

                LEFT JOIN currencies as fc ON fc.id = from_currecny_id
                LEFT JOIN currencies as tc ON tc.id = to_currecny_id
                LEFT JOIN users ON users.id = detail.user_id
                WHERE $condtionWithDate
            ");
        }
        else
        {
            $data = DB::select("SELECT
                detail.*,
                fc.code as fc_code,
                tc.code as tc_code,
                fc.symbol as fc_symbol,
                tc.symbol as tc_symbol,
                users.first_name, users.last_name

                FROM(SELECT
                ce.*,
                from_wallet.currency_id as from_currecny_id,
                to_wallet.currency_id as to_currecny_id
                FROM currency_exchanges as ce
                LEFT JOIN wallets as from_wallet ON from_wallet.id = from_wallet
                LEFT JOIN wallets as to_wallet ON to_wallet.id = to_wallet)

                as detail

                LEFT JOIN currencies as fc ON fc.id = from_currecny_id
                LEFT JOIN currencies as tc ON tc.id = to_currecny_id
                LEFT JOIN users ON users.id = detail.user_id
                WHERE $condtionsWithoutDate
            ");
        }
        return $data;
    }

    public function getExchangesListForCsvPdf($from, $to, $status, $currency, $user)
    {
        // dd($from);
        if (empty($from) || empty($to)){
            $date_range = null;
        }
        else if (empty($from)){
            $date_range = null;
        }
        else if (empty($to)){
            $date_range = null;
        }
        else{
            $date_range = 'Available';
        }

        /*$condtionsWithoutDate start*/
        $condtionsWithoutDate = " detail.id != 0";
        if ($status != 'all')
        {
            $condtionsWithoutDate .= " AND detail.status = '$status'";
        }
        if ($currency != 'all')
        {
            if ($user)
            {
                $condtionsWithoutDate .= " AND detail.from_currecny_id = '$currency' AND detail.user_id = '$user' OR detail.to_currecny_id = '$currency' AND detail.user_id = '$user' ";
            }
            else
            {
                $condtionsWithoutDate .= " AND detail.from_currecny_id = '$currency' OR detail.to_currecny_id = '$currency'";
            }
        }
        else
        {
            if ($user)
            {
                $condtionsWithoutDate .= " AND detail.user_id = '$user' ";
            }
        }
        /*$condtionsWithoutDate end*/

        /*$condtionWithDate start*/
        $condtionWithDate = " detail.id != 0";
        if (!empty($from) || !empty($to))
        {
            $condtionWithDate .= " AND DATE(detail.created_at) BETWEEN '$from' AND '$to'";
        }
        if ($status != 'all')
        {
            $condtionWithDate .= " AND detail.status = '$status'";
        }
        if ($currency != 'all')
        {
            if ($user)
            {
                $condtionWithDate .= " AND detail.from_currecny_id = '$currency' AND detail.user_id = '$user' OR detail.to_currecny_id = '$currency' AND detail.user_id = '$user' ";
            }
            else
            {
                $condtionWithDate .= " AND detail.from_currecny_id = '$currency' OR detail.to_currecny_id = '$currency'";
            }
        }
        else
        {
            if ($user)
            {
                $condtionWithDate .= " AND detail.user_id = '$user' ";
            }
        }
        /*$condtionWithDate end*/

        if (!empty($date_range))
        {
            $data = DB::select("
                SELECT detail.*,
                fc.code as fc_code,
                tc.code as tc_code,
                fc.symbol as fc_symbol,
                tc.symbol as tc_symbol,
                users.first_name, users.last_name

                FROM(SELECT ce.*,
                from_wallet.currency_id as from_currecny_id,
                to_wallet.currency_id as to_currecny_id
                FROM currency_exchanges as ce
                LEFT JOIN wallets as from_wallet ON from_wallet.id = from_wallet
                LEFT JOIN wallets as to_wallet ON to_wallet.id = to_wallet)

                as detail

                LEFT JOIN currencies as fc ON fc.id = from_currecny_id
                LEFT JOIN currencies as tc ON tc.id = to_currecny_id
                LEFT JOIN users ON users.id = detail.user_id

                WHERE $condtionWithDate

                ORDER BY detail.created_at DESC
            ");
        }
        else
        {
            $data = DB::select("
                SELECT detail.*,
                fc.code as fc_code,
                tc.code as tc_code,
                fc.symbol as fc_symbol,
                tc.symbol as tc_symbol,
                users.first_name, users.last_name

                FROM(SELECT ce.*,
                from_wallet.currency_id as from_currecny_id,
                to_wallet.currency_id as to_currecny_id
                FROM currency_exchanges as ce
                LEFT JOIN wallets as from_wallet ON from_wallet.id = from_wallet
                LEFT JOIN wallets as to_wallet ON to_wallet.id = to_wallet)

                as detail

                LEFT JOIN currencies as fc ON fc.id = from_currecny_id
                LEFT JOIN currencies as tc ON tc.id = to_currecny_id
                LEFT JOIN users ON users.id = detail.user_id

                WHERE $condtionsWithoutDate
                ORDER BY detail.created_at DESC
            ");
        }
        return $data;
    }
}


