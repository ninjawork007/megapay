<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table    = 'transfers';
    public $timestamps  = false;
    protected $fillable = ['sender_id', 'receiver_id', 'currency_id', 'bank_id', 'file_id', 'uuid', 'fee', 'amount', 'note', 'email', 'phone', 'status'];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id');
    }

    //new
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    //new
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getTransfersUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'transfers.sender_id')
            ->where(['transfers.sender_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getTransfersUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'transfers.sender_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'transfers.sender_id')
            ->get();
    }

    /**
     * [Transfers Filtering Results]
     * @param  [null/date] $from   [start date]
     * @param  [null/date] $to     [end date]
     * @param  [string]    $status [Status]
     * @param  [null/id]   $user   [User ID]
     * @return [void]      [All Query Results]
     */
    public function getTransfersList($from, $to, $status, $currency, $user)
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
            $conditions['transfers.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transfers.currency_id'] = $currency;
        }

        if (!empty($user))
        {
            $conditions['transfers.sender_id'] = $user;
        }

        if (!empty($date_range))
        {
            //not optimized
            /*$transfers = Transfer::with('sender','receiver','currency')->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('transfers.created_at', '>=', $from)->whereDate('transfers.created_at', '<=', $to);
            })->select('transfers.*');*/

            //optimized
            $transfers = Transfer::with([
                'sender' => function ($query) {
                    $query->select('id', 'first_name','last_name');
                },
                'receiver' => function ($query) {
                    $query->select('id', 'first_name','last_name');
                },
                'currency' => function ($query) {
                    $query->select('id', 'code','symbol');
                }
            ])->where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('transfers.created_at', '>=', $from)->whereDate('transfers.created_at', '<=', $to);
            })
            ->select('transfers.*');

            // ->orderBy('transfers.id', 'desc')->get();
        }
        else
        {
            //not optimized
            /*$transfers = Transfer::with('sender','receiver','currency')->where($conditions)->select('transfers.*');*/

            //optimized
            $transfers = Transfer::with([
                'sender' => function ($query) {
                    $query->select('id', 'first_name','last_name');
                },
                'receiver' => function ($query) {
                    $query->select('id', 'first_name','last_name');
                },
                'currency' => function ($query) {
                    $query->select('id', 'code','symbol');
                }
            ])->where($conditions)->select('transfers.*');
            // ->orderBy('transfers.id', 'desc')->get();
        }
        return $transfers;
    }

    public function getTransfersListForCsvPdf($from, $to, $status, $currency, $user)
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
            $conditions['transfers.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transfers.currency_id'] = $currency;
        }

        if (!empty($user))
        {
            $conditions['transfers.sender_id'] = $user;
        }

        if (!empty($date_range))
        {
            $transfers = Transfer::where($conditions)->where(function ($query) use ($from, $to)
            {
                $query->whereDate('transfers.created_at', '>=', $from)->whereDate('transfers.created_at', '<=', $to);
            })
            ->orderBy('transfers.id', 'desc')->get();
        }
        else
        {
            $transfers = Transfer::where($conditions)->orderBy('transfers.id', 'desc')->get();
        }
        return $transfers;
    }
}
