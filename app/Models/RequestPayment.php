<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\RequestPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RequestPayment extends Model
{
    protected $table    = 'request_payments';
    protected $fillable = [
        'user_id',
        'receiver_id',
        'currency_id',
        'uuid',
        'amount',
        'accept_amount',
        'email',
        'phone',
        'invoice_no',
        'purpose',
        'note',
        'status',
    ];

    public $timestamps  = true;

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

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user [id]
     * @return [string]       [firstname and lastname]
     */
    public function getRequestPaymentsUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'request_payments.user_id')
            ->where(['request_payments.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search [query string]
     * @return [string]       [distinct firstname and lastname]
     */
    public function getRequestPaymentsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'request_payments.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'request_payments.user_id')
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
    public function getRequestPaymentsList($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        //start date conditions
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
        //end date conditions

        if (!empty($status) && $status != 'all')
        {
            $conditions['request_payments.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['request_payments.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['request_payments.type'] = $type;
        }

        if (!empty($user))
        {
            $conditions['request_payments.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $request_payments = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'receiver' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency' => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
            ])
            ->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('request_payments.created_at', '>=', $from)->whereDate('request_payments.created_at', '<=', $to);
            })
            ->select('request_payments.*');
        }
        else
        {
            $request_payments = $this->with([
                'user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'receiver' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency' => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
            ])
            ->where($conditions)
            ->select('request_payments.*');
        }
        return $request_payments;
    }

    public function getRequestPaymentsListForCsvPdf($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        //start date conditions
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
        //end date conditions

        if (!empty($status) && $status != 'all')
        {
            $conditions['request_payments.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['request_payments.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['request_payments.type'] = $type;
        }

        if (!empty($user))
        {
            $conditions['request_payments.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $request_payments = $this->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('request_payments.created_at', '>=', $from)->whereDate('request_payments.created_at', '<=', $to);
            })
            ->orderBy('request_payments.id', 'desc')->get();
        }
        else
        {
            $request_payments = $this->where($conditions)->orderBy('request_payments.id', 'desc')->get();
        }
        return $request_payments;
    }
}
