<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table    = 'vouchers';
    public $timestamps  = true;
    protected $fillable = [
        'user_id',
        'activator_id',
        'currency_id',
        'uuid',
        'charge_percentage',
        'charge_fixed',
        'amount',
        'code',
        'redeemed',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activator()
    {
        return $this->belongsTo(User::class, 'activator_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id','id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user [id]
     * @return [string]       [firstname and lastname]
     */
    public function getVouchersUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'vouchers.user_id')
            ->where(['vouchers.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search [query string]
     * @return [string]       [distinct firstname and lastname]
     */
    public function getVouchersUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'vouchers.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'vouchers.user_id')
            ->get();
    }

    /**
     * [Exchanges Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string] $status [Status]
     * @param  [null/id] $user   [User ID]
     * @return [query]         [All Query Results]
     */
    public function getVouchersList($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to))
        {
            $date_range = null;
        }
        else if (empty($from))
        {
            $date_range = null;
        }
        else if (empty($to))
        {
            $date_range = null;
        }
        else
        {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['vouchers.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['vouchers.currency_id'] = $currency;
        }

        if (!empty($user))
        {
            $conditions['vouchers.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $vouchers = Voucher::where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('vouchers.created_at', '>=', $from)->whereDate('vouchers.created_at', '<=', $to);
            });
            // ->orderBy('vouchers.id', 'desc')->get();
        }
        else
        {
            // $vouchers = $this->where($conditions)->latest()->get();
            $vouchers = Voucher::where($conditions);
            // ->orderBy('vouchers.id', 'desc')->get();
        }
        return $vouchers;
    }

    public function getVouchersListForCsvPdf($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to))
        {
            $date_range = null;
        }
        else if (empty($from))
        {
            $date_range = null;
        }
        else if (empty($to))
        {
            $date_range = null;
        }
        else
        {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['vouchers.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['vouchers.currency_id'] = $currency;
        }

        if (!empty($user))
        {
            $conditions['vouchers.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $vouchers = Voucher::where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('vouchers.created_at', '>=', $from)->whereDate('vouchers.created_at', '<=', $to);
            })
            ->orderBy('vouchers.id', 'desc')->get();
        }
        else
        {
            // dd('ss');
            // $vouchers = $this->where($conditions)->orderBy('vouchers.id', 'desc')->get();
            // $vouchers = Voucher::where($conditions)->orderBy('vouchers.id', 'desc')->get();
            $vouchers = Voucher::where($conditions)->orderBy('vouchers.id', 'desc')->get();
            // $vouchers = $this->where($conditions)->latest()->get();
        }
        return $vouchers;
    }
}
