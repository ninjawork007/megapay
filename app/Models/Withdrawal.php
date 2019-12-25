<?php

namespace App\Models;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';

    public $timestamps = true;

    protected $fillable = ['user_id', 'currency_id', 'payment_method_id', 'uuid', 'charge_percentage', 'charge_fixed', 'subtotal', 'amount', 'payment_method_info', 'status'];

    //eagar loading with Scope query technique
    public function scopeWithAll($query)
    {
        $query->with('payment_method', 'currency', 'transaction', 'user');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function withdrawal_detail()
    {
        return $this->hasOne(WithdrawalDetail::class, 'withdrawal_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getWithdrawalsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'withdrawals.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getWithdrawalsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'withdrawals.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'withdrawals.user_id')
            ->get();
    }

    /**
     * [Withdrawals Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [string]    $pm     [Payment Methods]
     * @param  [null/id]   $user   [User ID]
     * @return [query]     [All Query Results]
     */
    public function getWithdrawalsList($from, $to, $status, $currency, $pm, $user)
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
            $conditions['withdrawals.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['withdrawals.currency_id'] = $currency;
        }

        if (!empty($pm) && $pm != 'all')
        {
            $conditions['withdrawals.payment_method_id'] = $pm;
        }

        if (!empty($user))
        {
            $conditions['withdrawals.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $withdrawals = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency' => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
                'payment_method' => function ($query)
                {
                    $query->select('id', 'name');
                },
            ])
            ->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('withdrawals.created_at', '>=', $from)->whereDate('withdrawals.created_at', '<=', $to);
            })
            ->select('withdrawals.*');
        }
        else
        {
            $withdrawals = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency' => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
                'payment_method' => function ($query)
                {
                    $query->select('id', 'name');
                },
            ])
            ->where($conditions)
            ->select('withdrawals.*');
        }
        return $withdrawals;
    }

    public function getWithdrawalsListForCsvPdf($from, $to, $status, $currency, $pm, $user)
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
            $conditions['withdrawals.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['withdrawals.currency_id'] = $currency;
        }

        if (!empty($pm) && $pm != 'all')
        {
            $conditions['withdrawals.payment_method_id'] = $pm;
        }

        if (!empty($user))
        {
            $conditions['withdrawals.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $withdrawals = $this->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('withdrawals.created_at', '>=', $from)->whereDate('withdrawals.created_at', '<=', $to);
            })
            ->orderBy('withdrawals.id', 'desc')->get();
        }
        else
        {
            $withdrawals = $this->where($conditions)->orderBy('withdrawals.id', 'desc')->get();
        }
        return $withdrawals;
    }
}
