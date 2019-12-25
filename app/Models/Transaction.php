<?php

namespace App\Models;

use App\Models\Currency;
use App\Models\Merchant;
use App\Models\MerchantPayment;
use App\Models\PaymentMethod;
use App\Models\Preference;
use App\Models\User;
use App\Traits\Excludable;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use Excludable;

    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'end_user_id',
        'currency_id',
        'payment_method_id',
        'merchant_id',
        'bank_id',
        'file_id',
        'uuid',
        'refund_reference',
        'transaction_reference_id',
        'transaction_type_id',
        'user_type',
        'email',
        'phone',
        'subtotal',
        'percentage',
        'charge_percentage',
        'charge_fixed',
        'total',
        'note',
        'status',
    ];

    //eagar loading with Scope query technique
    public function scopeWithAll($query)
    {
        $query->with('user', 'end_user',
            'currency', 'payment_method',
            'merchant', 'deposit',
            'transfer', 'currency_exchange',
            'voucher', 'request_payment',
            'merchant_payment', 'withdrawal',
            'dispute');
    }

    /*
    Start of relationships
     */

    /**
     * [user description]
     * @return [many to one relationship] [Many Transactions belongs to a User]
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function end_user()
    {
        // return $this->belongsTo(User::class, 'end_user_id', 'id');
        return $this->belongsTo(User::class, 'end_user_id');
    }

    /**
     * [currency description]
     * @return [one to one relationship] [Transaction belongs to a Currency]
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * [payment_method description]
     * @return [one to one relationship] [Transaction belongs to a PaymentMethod]
     */
    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'transaction_reference_id', 'id');
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class, 'transaction_reference_id', 'id');
    }

    public function transfer()
    {
        return $this->belongsTo(Transfer::class, 'transaction_reference_id', 'id');
    }

    public function currency_exchange()
    {
        return $this->belongsTo(CurrencyExchange::class, 'transaction_reference_id', 'id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'transaction_reference_id', 'id');
    }

    public function request_payment()
    {
        return $this->belongsTo(RequestPayment::class, 'transaction_reference_id', 'id');
    }

    /**
     * [merchant description]
     * @return [one to one relationship] [Transaction belongs to a merchant]
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function merchant_payment()
    {
        return $this->belongsTo(MerchantPayment::class, 'transaction_reference_id', 'id');
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
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

    /*
    end of relationships
     */

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getTransactionsUsersName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getTransactionsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'transactions.user_id')
            ->get();
    }

    public function getTransactions($from, $to, $type, $wallet, $status)
    {
        // dd($type);
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
        $conditions['transactions.user_id'] = Auth::user()->id;
        $whereInCondition = [Deposit, Withdrawal, Bank_Transfer, Transferred, Received, Exchange_From, Exchange_To, Voucher_Created, Voucher_Activated, Request_From, Request_To, Payment_Sent, Payment_Received];
        if (!empty($type) && $type != 'all')
        {
            //$conditions['transactions.transaction_type_id'] = $type;
            if ($type == Deposit || $type == Withdrawal)
            {
                $whereInCondition = [$type];
            }
            else
            {
                if ($type == 'sent')
                {
                    $whereInCondition = [Transferred, Payment_Sent, Bank_Transfer];
                }
                elseif ($type == 'request')
                {
                    $whereInCondition = [Request_From, Request_To];
                }
                elseif ($type == 'received')
                {
                    $whereInCondition = [Received, Payment_Received];
                }
                elseif ($type == 'exchange')
                {
                    $whereInCondition = [Exchange_From, Exchange_To];
                }
                elseif ($type == 'voucher')
                {
                    $whereInCondition = [Voucher_Created, Voucher_Activated];
                }
            }
        }
        // dd($whereInCondition);

        if (!empty($wallet) && $wallet != 'all')
        {
            $conditions['transactions.currency_id'] = $wallet;
        }

        if (!empty($status) && $status != 'all')
        {
            $conditions['transactions.status'] = $status;
        }

        if (empty($date_range))
        {
            // $transaction = Transaction::where($conditions)->whereIn('transactions.transaction_type_id', $whereInCondition)
            //     ->orderBy('transactions.id', 'desc')->paginate(15);
            $transaction = Transaction::with([
                'end_user:id,first_name,last_name,picture',
                'transaction_type:id,name',
                'payment_method:id,name',
                'bank:id,file_id,bank_name',
                'bank.file:id,filename',
                'merchant:id,business_name,logo',
                'currency:id,code',
                'dispute:id',
                'transfer:id,sender_id',
                'transfer.sender:id,first_name,last_name',
            ])
            ->where($conditions)->whereIn('transactions.transaction_type_id', $whereInCondition)
            ->orderBy('transactions.id', 'desc')
            ->paginate(15);
            // ->get();
            // dd($transaction);
        }
        else
        {
            $from = date('Y-m-d', strtotime($from));
            $to   = date('Y-m-d', strtotime($to));

            // $transaction = Transaction::where($conditions)
            //     ->whereIn('transactions.transaction_type_id', $whereInCondition)
            //     ->whereDate('transactions.created_at', '>=', $from)
            //     ->whereDate('transactions.created_at', '<=', $to)
            //     ->orderBy('transactions.id', 'desc')
            //     ->paginate(15);
            $transaction = Transaction::with([
                'end_user:id,first_name,last_name,picture',
                'transaction_type:id,name',
                'payment_method:id,name',
                'bank:id,file_id,bank_name',
                'bank.file:id,filename',
                'merchant:id,business_name,logo',
                'currency:id,code',
                'dispute:id',
                'transfer:id,sender_id',
                'transfer.sender:id,first_name,last_name',
            ])
            ->where($conditions)
            ->whereIn('transactions.transaction_type_id', $whereInCondition)
            ->whereDate('transactions.created_at', '>=', $from)
            ->whereDate('transactions.created_at', '<=', $to)
            ->orderBy('transactions.id', 'desc')
            ->paginate(15);
        }
        return $transaction;
    }

    /**
     * [Transactions Filtering Results]
     * @param  [null/date] $from     [start date]
     * @param  [null/date] $to       [end date]
     * @param  [string]    $status   [Status]
     * @param  [string]    $currency [currency]
     * @param  [string]    $type     [type]
     * @param  [null/id]   $user     [User ID]
     * @return [query]     [All Query Results]
     */
    public function getTransactionsList($from, $to, $status, $currency, $type, $user)
    {
        // echo $type;
        // exit();
        // dd($from);
        // dd($type);
        // dd($to);

        $conditions = [];

        //start date conditions
        // if (empty($from) || empty($to))
        // {
        //     $date_range = null;
        // }
        // else if (empty($from))
        // {
        //     $date_range = null;
        // }
        // else if (empty($to))
        // {
        //     $date_range = null;
        // }
        // else
        // {
        //     $date_range = 'Available';
        // }
        if (!empty($from) && !empty($to))
        {
            $date_range = 'Available';
        }
        else
        {
            $date_range = null;
        }
        //end date conditions

        if (!empty($status) && $status != 'all')
        {
            $conditions['transactions.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transactions.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['transaction_type_id'] = $type;
        }

        if (!empty($user))
        {
            $conditions['transactions.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $transactions = $this->with([
                'user'             => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'end_user'         => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transaction_type' => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'         => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
            ])
                ->where($conditions)
                ->whereDate('transactions.created_at', '>=', $from)
                ->whereDate('transactions.created_at', '<=', $to)
                ->select('transactions.*');
        }
        else
        {
            $transactions = $this->with([
                'user'             => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
                'transaction_type' => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'         => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
                'end_user'         => function ($query)
                {
                    $query->select('id', 'first_name', 'last_name');
                },
            ])
                ->where($conditions)
                ->select('transactions.*');
        }
        return $transactions;
    }

    public function getTransactionsListForCsvPDF($from, $to, $status, $currency, $type, $user)
    {
        // dd($from);
        // dd($to);

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
            $conditions['transactions.status'] = $status;
        }

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transactions.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['transactions.transaction_type_id'] = $type;
        }

        if (!empty($user))
        {
            $conditions['transactions.user_id'] = $user;
        }

        if (!empty($date_range))
        {
            $transactions = $this->where($conditions)
                ->whereDate('transactions.created_at', '>=', $from)
                ->whereDate('transactions.created_at', '<=', $to)
                ->orderBy('transactions.id', 'desc')->take(1100)->get(); //mdf problem, so, i have set take(1100)
                                                                     // dd($transactions);
        }
        else
        {
            $transactions = $this->where($conditions)->orderBy('transactions.id', 'desc')->take(1100)->get(); //mdf problem, so, i have set take(1100)
                                                                                                              // dd($transactions);
        }
        return $transactions;
    }

    /**
     * [Revenues]
     * @return [void] [Total Charge of Each Transaction With Separate Currency Data]
     */
    public function getTotalCharge()
    {
        return $this->select('currency_id')
            ->addSelect(\DB::raw('SUM(charge_percentage + charge_fixed) as total_charge'))
            ->groupBy('currency_id')
            ->get();
    }

    public function getRevenuesList($from, $to, $currency, $type)
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

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transactions.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['transactions.transaction_type_id'] = $type;
        }

        if (!empty($date_range))
        {
            $revenues = $this->with([
                'transaction_type' => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'         => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
            ])
                ->where(function ($query)
            {
                    $query->where('charge_percentage', '>', 0);
                    $query->orWhere('charge_fixed', '!=', 0);
                })
                ->where('status', 'Success')
                ->where($conditions)
                ->whereIn('transaction_type_id', [Deposit, Withdrawal, Transferred, Request_To, Payment_Received])
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
                ->select('transactions.id', 'transactions.created_at', 'transactions.transaction_type_id', 'transactions.charge_percentage', 'transactions.charge_fixed', 'transactions.currency_id', 'transactions.status');
        }
        else
        {
            $revenues = $this->with([
                'transaction_type' => function ($query)
                {
                    $query->select('id', 'name');
                },
                'currency'         => function ($query)
                {
                    $query->select('id', 'code', 'symbol');
                },
            ])
                ->where(function ($query)
            {
                    $query->where('charge_percentage', '>', 0);
                    $query->orWhere('charge_fixed', '!=', 0);
                })
                ->where('status', 'Success')
                ->whereIn('transaction_type_id', [Deposit, Withdrawal, Transferred, Request_To, Payment_Received])
                ->where($conditions)
                ->select('transactions.id', 'transactions.created_at', 'transactions.transaction_type_id', 'transactions.charge_percentage', 'transactions.charge_fixed', 'transactions.currency_id', 'transactions.status');
        }
        return $revenues;
    }

    public function getRevenuesListForCurrencyInfoAndCsvPdf($from, $to, $currency, $type)
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

        if (!empty($currency) && $currency != 'all')
        {
            $conditions['transactions.currency_id'] = $currency;
        }

        if (!empty($type) && $type != 'all')
        {
            $conditions['transactions.transaction_type_id'] = $type;
        }

        if (!empty($date_range))
        {
            $revenues = $this->where($conditions)
                ->where(function ($query)
            {
                    $query->where('charge_percentage', '>', 0);
                    $query->orWhere('charge_fixed', '!=', 0);
                })
                ->where('status', 'Success')
                ->whereIn('transaction_type_id', [Deposit, Withdrawal, Transferred, Request_To, Payment_Received])
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
                ->select('transactions.id', 'transactions.created_at', 'transactions.transaction_type_id', 'transactions.charge_percentage', 'transactions.charge_fixed', 'transactions.currency_id', 'transactions.status')
                ->orderBy('transactions.id', 'desc')
                ->get();
        }
        else
        {
            $revenues = $this->where($conditions)
                ->where(function ($query)
            {
                    $query->where('charge_percentage', '>', 0);
                    $query->orWhere('charge_fixed', '!=', 0);
                })
                ->where('status', 'Success')
                ->whereIn('transaction_type_id', [Deposit, Withdrawal, Transferred, Request_To, Payment_Received])
                ->select('transactions.id', 'transactions.created_at', 'transactions.transaction_type_id', 'transactions.charge_percentage', 'transactions.charge_fixed', 'transactions.currency_id', 'transactions.status')
                ->orderBy('transactions.id', 'desc')
                ->get();
        }
        return $revenues;
    }

    /**
     *  DASHBOARD FUNCTIONALITIES
     */
    public function dashboardTransactionList()
    {
        // $transaction = Transaction::where(['transactions.user_id' => Auth::user()->id])->orderBy('transactions.id', 'desc')->take(10)->get();
        $transaction = Transaction::with([
            'end_user:id,first_name,last_name,picture',
            'transaction_type:id,name',
            'payment_method:id,name',
            'bank:id,file_id,bank_name',
            'bank.file:id,filename',
            'merchant:id,business_name,logo',
            'currency:id,code',
            'dispute:id',
            'transfer:id,sender_id',
            'transfer.sender:id,first_name,last_name',
        ])->where(['transactions.user_id' => Auth::user()->id])->orderBy('transactions.id', 'desc')->take(10)->get();
        return $transaction;
    }

    public function lastThirtyDaysDeposit()
    {
        $getLastOneMonthDates = getLastOneMonthDates();
        $final                = [];
        $data_map             = array();
        $today                = date('Y-m-d');
        $previousDate         = date("Y-m-d", strtotime("-30 day", strtotime(date('d-m-Y'))));
        $data                 = $this->select(DB::raw('currency_id,SUM(total) as amount,created_at as trans_date,MONTH(created_at) as month,DAY(created_at) as day'))
            ->whereBetween('created_at', [$previousDate, $today])->where(['transaction_type_id' => Deposit, 'status' => 'Success'])
            ->groupBy('currency_id', 'day')->get();
        // $homeCurrency = Setting::where(['name' => 'default_currency', 'type' => 'general'])->select('value')->first();
        // $currencyRate = Currency::where(['id' => $homeCurrency->value])->select('rate')->first();

        $currencies       = getCurrencyIdOfTransaction($data);
        $currencyWithRate = Currency::whereIn('id', $currencies)->get();

        if (!empty($data))
        {
            $data_map = generateAmountBasedOnDfltCurrency($data, $currencyWithRate);
            //dd($data_map);

            $dataArray = [];
            $i         = 0;
            foreach ($getLastOneMonthDates as $key => $value)
            {
                $date                   = explode('-', $value);
                $td                     = (int) $date[0];
                $tm                     = (int) $date[1];
                $dataArray[$i]['day']   = $date[0];
                $dataArray[$i]['month'] = $date[1];
                if (isset($data_map[$td][$tm]))
                {
                    $dataArray[$i]['amount'] = abs($data_map[$td][$tm]);
                }
                else
                {
                    $dataArray[$i]['amount'] = 0;
                }
                $i++;
            }
            foreach ($dataArray as $key => $res)
            {
                $final[$key] = decimalFormat(abs($res['amount']));
                // $final[$key] = moneyFormat($currencyRate->symbol, formatNumber(abs($res['amount'])));
            }
        }
        //dd($final);
        return $final;
    }

    public function lastThirtyDaysWitdrawal()
    {

        $getLastOneMonthDates = getLastOneMonthDates();
        $final                = [];
        $data_map             = [];
        $today                = date('Y-m-d');
        $previousDate         = date("Y-m-d", strtotime("-30 day", strtotime(date('d-m-Y'))));
        $data                 = $this->select(DB::raw('currency_id,SUM(total) as amount,created_at as trans_date,MONTH(created_at) as month,DAY(created_at) as day'))->whereBetween('created_at', [$previousDate, $today])->where(['transaction_type_id' => Withdrawal, 'status' => 'Success'])->groupBy('currency_id', 'day')->get();
        $currencies           = getCurrencyIdOfTransaction($data);
        $currencyWithRate     = Currency::whereIn('id', $currencies)->get();
        if (!empty($data))
        {
            $data_map  = generateAmountBasedOnDfltCurrency($data, $currencyWithRate);
            $dataArray = [];
            $i         = 0;
            foreach ($getLastOneMonthDates as $key => $value)
            {
                $date                   = explode('-', $value);
                $td                     = (int) $date[0];
                $tm                     = (int) $date[1];
                $dataArray[$i]['day']   = $date[0];
                $dataArray[$i]['month'] = $date[1];
                if (isset($data_map[$td][$tm]))
                {
                    $dataArray[$i]['amount'] = abs($data_map[$td][$tm]);
                }
                else
                {
                    $dataArray[$i]['amount'] = 0;
                }
                $i++;
            }
            foreach ($dataArray as $key => $res)
            {
                $final[$key] = decimalFormat(abs($res['amount']));
            }
        }
        return $final;
    }

    public function lastThirtyDaysTransfer()
    {

        $getLastOneMonthDates = getLastOneMonthDates();
        $final                = [];
        $today                = date('Y-m-d');
        $previousDate         = date("Y-m-d", strtotime("-30 day", strtotime(date('d-m-Y'))));
        $data                 = $this->select(DB::raw('currency_id,SUM(subtotal) as amount,created_at as trans_date,MONTH(created_at) as month,DAY(created_at) as day'))->whereBetween('created_at', [$previousDate, $today])->where(['transaction_type_id' => Transferred, 'status' => 'Success'])->groupBy('currency_id', 'day')->get();
        $currencies           = getCurrencyIdOfTransaction($data);
        $currencyWithRate     = Currency::whereIn('id', $currencies)->get();

        if (!empty($data))
        {
            $data_map  = generateAmountBasedOnDfltCurrency($data, $currencyWithRate);
            $dataArray = [];
            $i         = 0;
            foreach ($getLastOneMonthDates as $key => $value)
            {
                $date                   = explode('-', $value);
                $td                     = (int) $date[0];
                $tm                     = (int) $date[1];
                $dataArray[$i]['day']   = $date[0];
                $dataArray[$i]['month'] = $date[1];
                if (isset($data_map[$td][$tm]))
                {
                    $dataArray[$i]['amount'] = abs($data_map[$td][$tm]);
                }
                else
                {
                    $dataArray[$i]['amount'] = 0;
                }
                $i++;
            }
            foreach ($dataArray as $key => $res)
            {
                $final[$key] = decimalFormat(abs($res['amount']));

            }
        }
        return $final;
    }

    public function totalRevenue($from, $to)
    {
        $data = $this->select(DB::raw('currency_id,SUM(charge_percentage + charge_fixed) as total_charge,MONTH(created_at) as month,DAY(created_at) as day'))
            ->whereBetween('created_at', [$from, $to])->whereIn('transaction_type_id', [Deposit, Withdrawal, Transferred])->groupBy('currency_id', 'day')->get();

        $currencies       = getCurrencyIdOfTransaction($data);
        $currencyWithRate = Currency::whereIn('id', $currencies)->get();
        $final            = 0;
        if (!empty($data))
        {
            $final = generateAmountForTotal($data, $currencyWithRate);
        }
        return $final;
    }

    public function totalDeposit($from, $to)
    {
        $data = $this->select(DB::raw('currency_id,SUM(charge_percentage + charge_fixed) as total_charge,
                                              MONTH(created_at) as month,
                                              DAY(created_at) as day'))->whereBetween('created_at', [$from, $to])->where('transaction_type_id', Deposit)->groupBy('currency_id', 'day')->get();

        $currencies       = getCurrencyIdOfTransaction($data);
        $currencyWithRate = Currency::whereIn('id', $currencies)->get();
        $final            = 0;
        if (!empty($data))
        {
            $final = generateAmountForTotal($data, $currencyWithRate);
        }
        return $final;
    }

    public function totalWithdrawal($from, $to)
    {
        $data = $this->select(DB::raw('currency_id,SUM(charge_percentage + charge_fixed) as total_charge,MONTH(created_at) as month,DAY(created_at) as day'))->whereBetween('created_at', [$from, $to])->where('transaction_type_id', Withdrawal)->groupBy('currency_id', 'day')->get();

        $currencies       = getCurrencyIdOfTransaction($data);
        $currencyWithRate = Currency::whereIn('id', $currencies)->get();
        $final            = 0;
        if (!empty($data))
        {
            $final = generateAmountForTotal($data, $currencyWithRate);
        }
        return $final;
    }

    public function totalTransfer($from, $to)
    {
        $data             = $this->select(DB::raw('currency_id,SUM(charge_percentage + charge_fixed) as total_charge,MONTH(created_at) as month,DAY(created_at) as day'))->whereBetween('created_at', [$from, $to])->where('transaction_type_id', Transferred)->groupBy('currency_id', 'day')->get();
        $currencies       = getCurrencyIdOfTransaction($data);
        $currencyWithRate = Currency::whereIn('id', $currencies)->get();
        $final            = 0;
        if (!empty($data))
        {
            $final = generateAmountForTotal($data, $currencyWithRate);
        }
        return $final;

    }

    //Query for Mobile Application - starts
    public function getTransactionLists($type, $user_id)
    {
        $conditions = ['transactions.user_id' => $user_id];
        if ($type == 'allTransactions')
        {
            $whereInCondition = [Deposit, Withdrawal, Transferred, Received, Exchange_From, Exchange_To, Request_From, Request_To, Payment_Sent, Payment_Received];
        }
        elseif ($type == 'allMoneyIn')
        {
            $whereInCondition = [Deposit, Transferred, Received, Exchange_To, Request_From, Request_To, Payment_Received];
        }
        elseif ($type == 'allMoneyOut')
        {
            $whereInCondition = [Withdrawal, Transferred, Received, Exchange_From, Request_From, Request_To, Payment_Sent];
        }

        $transaction = $this->with([
            'currency:id,code,symbol',
            'user:id,first_name,last_name,picture',
            'end_user:id,first_name,last_name,picture',
            'payment_method:id,name',
            'transaction_type:id,name',
            'merchant:id,business_name',
            'bank:id,bank_name,file_id',
            'bank.file:id,filename',
        ])
            ->where($conditions)
            ->whereIn('transactions.transaction_type_id', $whereInCondition)
            ->orderBy('transactions.id', 'desc')
            ->select([
                'transactions.id as id',
                'transactions.user_id',
                'transactions.end_user_id',
                'transactions.currency_id',
                'transactions.payment_method_id',
                'transactions.merchant_id',
                'transactions.bank_id',
                'transactions.transaction_type_id',
                'transactions.subtotal as subtotal',
                'transactions.charge_percentage as charge_percentage',
                'transactions.charge_fixed as charge_fixed',
                'transactions.total as total',
                'transactions.status as status',
                'transactions.email as email',
                'transactions.phone as phone',
                'transactions.created_at as t_created_at',
            ])
            ->get();

        $transactions = [];
        for ($i = 0; $i < count($transaction); $i++)
        {
            if ($transaction[$i]->user_id)
            {
                $transactions[$i]['user_id']     = $transaction[$i]->user_id;
                $transactions[$i]['user_f_name'] = $transaction[$i]->user->first_name;
                $transactions[$i]['user_l_name'] = $transaction[$i]->user->last_name;
                $transactions[$i]['user_photo']  = $transaction[$i]->user->picture;
            }

            if ($transaction[$i]->end_user_id)
            {
                $transactions[$i]['end_user_id']     = $transaction[$i]->end_user_id;
                $transactions[$i]['end_user_f_name'] = $transaction[$i]->end_user->first_name;
                $transactions[$i]['end_user_l_name'] = $transaction[$i]->end_user->last_name;
                $transactions[$i]['end_user_photo']  = $transaction[$i]->end_user->picture;
            }

            $transactions[$i]['id']                  = $transaction[$i]->id;
            $transactions[$i]['transaction_type_id'] = $transaction[$i]->transaction_type->id;
            $transactions[$i]['transaction_type']    = $transaction[$i]->transaction_type->name;
            $transactions[$i]['curr_code']           = $transaction[$i]->currency->code;
            $transactions[$i]['curr_symbol']         = $transaction[$i]->currency->symbol;
            $transactions[$i]['charge_percentage']   = $transaction[$i]->charge_percentage;
            $transactions[$i]['charge_fixed']        = $transaction[$i]->charge_fixed;
            $transactions[$i]['subtotal']            = $transaction[$i]->subtotal;
            $transactions[$i]['total']               = $transaction[$i]->total;
            $transactions[$i]['status']              = $transaction[$i]->status;
            $transactions[$i]['email']               = $transaction[$i]->email;
            $transactions[$i]['phone']               = $transaction[$i]->phone;
            // $transactions[$i]['t_created_at']        = $transaction[$i]->t_created_at;
            $transactions[$i]['t_created_at'] = $this->dateFormatForUser($transaction[$i]->t_created_at, $user_id);

            if ($transaction[$i]->payment_method_id)
            {
                $transactions[$i]['payment_method_name'] = $transaction[$i]->payment_method->name;
                $transactions[$i]['payment_method_id']   = $transaction[$i]->payment_method->id;
                $transactions[$i]['company_name']        = getCompanyName();
            }

            if ($transaction[$i]->merchant_id)
            {
                $transactions[$i]['merchant_id']   = $transaction[$i]->merchant_id;
                $transactions[$i]['merchant_name'] = $transaction[$i]->merchant->business_name;
                $transactions[$i]['logo']          = $transaction[$i]->merchant->logo;
            }

            if ($transaction[$i]->bank_id)
            {
                $transactions[$i]['bank_id']   = $transaction[$i]->bank_id;
                $transactions[$i]['bank_name'] = $transaction[$i]->bank->bank_name;
                if ($transaction[$i]->bank->file_id)
                {
                    $transactions[$i]['bank_logo'] = $transaction[$i]->bank->file->filename;
                }
            }
        }
        // d($transactions, 1);
        return $transactions;
    }

    public function getTransactionDetails($tr_id, $user_id)
    {
        $conditions       = ['transactions.id' => $tr_id, 'transactions.user_id' => $user_id];
        $whereInCondition = [Deposit, Withdrawal, Transferred, Received, Exchange_From, Exchange_To, Request_From, Request_To, Payment_Sent, Payment_Received];

        $transaction = $this->with([
            'currency:id,code,symbol',
            'user:id,first_name,last_name,picture',
            'end_user:id,first_name,last_name,picture',
            'payment_method:id,name',
            'transaction_type:id,name',
            'merchant:id,business_name',
        ])
            ->where($conditions)
            ->whereIn('transactions.transaction_type_id', $whereInCondition)
            ->orderBy('transactions.id', 'desc')
            ->select([
                'transactions.id as id', //
                'transactions.user_id',  //
                'transactions.end_user_id',
                'transactions.currency_id',       //
                'transactions.payment_method_id', //
                'transactions.merchant_id as merchant_id',
                'transactions.transaction_type_id', //
                'transactions.transaction_reference_id as transaction_reference_id',
                'transactions.charge_percentage as charge_percentage',
                'transactions.charge_fixed as charge_fixed',
                'transactions.subtotal as subtotal',
                'transactions.total as total',
                'transactions.uuid as transaction_id',
                'transactions.status as status',
                'transactions.note as description',
                'transactions.email as email',
                'transactions.phone as phone',
                'transactions.created_at as t_created_at',
            ])->first();

        if (@$transaction->user_id)
        {
            $transaction->user_id     = @$transaction->user_id;
            $transaction->user_f_name = @$transaction->user->first_name;
            $transaction->user_l_name = @$transaction->user->last_name;
            $transaction->user_photo  = @$transaction->user->picture;
        }

        if (@$transaction->end_user_id)
        {
            $transaction->end_user_id     = @$transaction->end_user_id;
            $transaction->end_user_f_name = @$transaction->end_user->first_name;
            $transaction->end_user_l_name = @$transaction->end_user->last_name;
            $transaction->end_user_photo  = @$transaction->end_user->picture;
        }

        $transaction->curr_code   = @$transaction->currency->code;
        $transaction->curr_symbol = @$transaction->currency->symbol;

        if (@$transaction->payment_method_id)
        {
            $transaction->payment_method_name = @$transaction->payment_method->name;
            $transaction->company_name        = getCompanyName();
        }

        if (@$transaction->merchant_id)
        {
            $transaction->merchant_name = @$transaction->merchant->business_name;
        }

        $transaction->type_id      = @$transaction->transaction_type->id;
        $transaction->type         = @$transaction->transaction_type->name;
        $transaction->t_created_at = $this->dateFormatForUser($transaction->t_created_at, $user_id);

        //d($transaction,1);
        return $transaction;
    }

    public function dateFormatForUser($value, $user_id)
    {
        $user     = User::with('user_detail:user_id,timezone')->where(['id' => $user_id])->first(['id']);
        $timezone = $user->user_detail->timezone;
        $today    = new \DateTime($value, new \DateTimeZone(config('app.timezone')));
        $today->setTimezone(new \DateTimeZone($timezone));
        $value = $today->format('Y-m-d h:m:s');

        $preference = Preference::where(['category' => 'preference', 'field' => 'date_format_type'])->first(['value'])->value;
        $separator  = Preference::where(['category' => 'preference', 'field' => 'date_sepa'])->first(['value'])->value;

        $data   = str_replace(['/', '.', ' ', '-'], $separator, $preference);
        $data   = explode($separator, $data);
        $first  = $data[0];
        $second = $data[1];
        $third  = $data[2];

        $dateInfo = str_replace(['/', '.', ' ', '-'], $separator, $value);
        $datas    = explode($separator, $dateInfo);
        $year     = $datas[0];
        $month    = $datas[1];
        $day      = $datas[2];

        $dateObj   = \DateTime::createFromFormat('!m', $month);
        $monthName = $dateObj->format('F');

        if ($first == 'yyyy' && $second == 'mm' && $third == 'dd')
        {
            $value = $year . $separator . $month . $separator . $day;
        }
        elseif ($first == 'dd' && $second == 'mm' && $third == 'yyyy')
        {

            $value = $day . $separator . $month . $separator . $year;
        }
        elseif ($first == 'mm' && $second == 'dd' && $third == 'yyyy')
        {

            $value = $month . $separator . $day . $separator . $year;
        }
        elseif ($first == 'dd' && $second == 'M' && $third == 'yyyy')
        {
            $value = $day . $separator . $monthName . $separator . $year;
        }
        elseif ($first == 'yyyy' && $second == 'M' && $third == 'dd')
        {
            $value = $year . $separator . $monthName . $separator . $day;
        }
        return $value;
    }
    //Query for Mobile Application - ends

}
