<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MerchantPayment extends Model
{
    protected $table   = 'merchant_payments';

    protected $fillable = [
        'merchant_id',
        'currency_id',
        'payment_method_id',
        'user_id',
        'gateway_reference',
        'order_no',
        'item_name',
        'uuid',
        'charge_percentage',
        'charge_fixed',
        'amount',
        'status',
    ];

    public $timestamps = false;

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

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
    * [Deposits Filtering Results]
    * @param  [null/date] $from     [start date]
    * @param  [null/date] $to       [end date]
    * @param  [string]    $status   [Status]
    * @param  [string]    $pm       [Payment Methods]
    * @param  [string]    $currency [Currency]
    * @param  [null/id]   $user     [User ID]
    * @return [query]     [All Query Results]
    */
    public function getMerchantPaymentsList($from, $to, $status, $currency, $pm)
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
            $conditions['merchant_payments.status'] = $status;
        }

        if (!empty($pm) && $pm != 'all')
        {
            $conditions['merchant_payments.payment_method_id'] = $pm;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['merchant_payments.currency_id'] = $currency;
        }

        if (!empty($date_range))
        {
            $merchant_payments = $this->with([
                'merchant.user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
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
                $query->whereDate('merchant_payments.created_at', '>=', $from)->whereDate('merchant_payments.created_at', '<=', $to);
            })
            ->select('merchant_payments.*');
        }
        else
        {
            $merchant_payments = $this->with([
                'merchant.user' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
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
            ->select('merchant_payments.*');
        }
        return $merchant_payments;
    }

    public function getMerchantPaymentsListForCsvPdf($from, $to, $status, $currency, $pm)
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
            $conditions['merchant_payments.status'] = $status;
        }

        if (!empty($pm) && $pm != 'all')
        {
            $conditions['merchant_payments.payment_method_id'] = $pm;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['merchant_payments.currency_id'] = $currency;
        }

        if (!empty($date_range))
        {
            $merchant_payments = $this->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('merchant_payments.created_at', '>=', $from)->whereDate('merchant_payments.created_at', '<=', $to);
            })
            ->orderBy('merchant_payments.id', 'desc')->get();
        }
        else
        {
            $merchant_payments = $this->where($conditions)->orderBy('merchant_payments.id', 'desc')->get();
        }
        return $merchant_payments;
    }
}
