<?php

namespace App\Models;

use App\Models\DocumentVerification;
use App\Models\Role;
use App\Models\Transaction;
use Hexters\CoinPayment\Entities\CoinPaymentuserRelation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, CoinPaymentuserRelation; //HasApiTokens

    protected $fillable = [
        'role_id',
        'type',
        'first_name',
        'last_name',
        'phone',
        'google2fa_secret',
        'defaultCountry',
        'carrierCode',
        'email',
        'password',
        'phrase',
        'status',
        'picture',
        'address_verified',
        'identity_verified',
    ];

    protected $table = 'users';

    protected $hidden = [
        'password', 'remember_token', 'phrase', 'google2fa_secret',
    ];

    /**
     * Ecrypt the user's google_2fa secret.
     */
    // public function setGoogle2faSecretAttribute($value)
    // {
    //     $this->attributes['google2fa_secret'] = encrypt($value);
    // }

    /**
     * Decrypt the user's google_2fa secret.
     */
    // public function getGoogle2faSecretAttribute($value)
    // {
    //     return decrypt($value);
    // }

    //User - hasOne - deposit
    public function deposit()
    {
        return $this->hasOne(Deposit::class);
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    //newly created by parvez, march - 11, 2018
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    // fixed by parvez
    public function voucher()
    {
        return $this->hasMany(Voucher::class);
    }

    // fixed by parvez
    public function request_payment()
    {
        return $this->hasOne(RequestPayment::class);
    }

    public function merchant()
    {
        return $this->hasMany(Merchant::class);
    }

    public function merchant_payment()
    {
        return $this->hasMany(MerchantPayment::class);
    }

    //User - hasOne - log
    public function activity_log()
    {
        return $this->hasOne(ActivityLog::class);
    }

    public function dispute()
    {
        return $this->hasMany(Dispute::class);
    }

    public function disputeDiscussion()
    {
        return $this->hasMany(DisputeDiscussion::class, 'user_id');
    }

    /**
     * [Role]
     * @return [one to one relationship] [Role belongs to a User]
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'user_id');
    }

    public function file()
    {
        return $this->hasOne(Ticket::class, 'user_id');
    }

    public function ticket_reply()
    {
        return $this->hasOne(TicketReply::class, 'user_id');
    }

    public function payoutSettings()
    {
        return $this->hasMany(PayoutSetting::class, 'user_id');
    }

    // new
    public function verifyUser()
    {
        return $this->hasOne(VerifyUser::class, 'user_id');
    }

    public function device_log()
    {
        return $this->hasOne(DeviceLog::class, 'user_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'user_id');
    }

    public function user_detail()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    //pm - 1.7
    public function document_verification()
    {
        return $this->hasMany(DocumentVerification::class, 'user_id');
    }

    /**
     * [Each User Transaction Filtering Results]
     * @param  [null/date] $from     [start date]
     * @param  [null/date] $to       [end date]
     * @param  [string]    $status   [Status]
     * @param  [string]    $currency [Currency]
     * @param  [string]    $type     [Type]
     * @param  [null/id]   $user     [User ID]
     * @return [query]     [All Query Results]
     */
    public function getEachUserTransactionsList($from, $to, $status, $currency, $type, $user)
    {
        // dd($user);

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

        $conditions = [];
        if (!empty($status) && $status != 'all')
        {
            $conditions['status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['transaction_type_id'] = $type;
        }

        if (!empty($date_range))
        {
            $transactions = Transaction::with([
                'deposit.user'             => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transfer.sender'          => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency_exchange.user'   => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'request_payment.user'     => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'withdrawal.user'          => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'user'                     => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transaction_type'         => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'                 => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
                'end_user'                 => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transfer.receiver'        => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'request_payment.receiver' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },

            ])
            ->where(['user_id' => $user])
            ->where($conditions)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->select('transactions.*');
        }
        else
        {
            $transactions = Transaction::with([
                'deposit.user'             => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transfer.sender'          => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'currency_exchange.user'   => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'request_payment.user'     => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'withdrawal.user'          => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'user'                     => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transaction_type'         => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'                 => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
                'end_user'                 => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transfer.receiver'        => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'request_payment.receiver' => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
            ])->where(['user_id' => $user])
                ->where($conditions)
                ->select('transactions.*');
        }
        return $transactions;
    }
}
