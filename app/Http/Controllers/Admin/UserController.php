<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\AdminsDataTable;
use App\DataTables\Admin\EachUserTransactionsDataTable;
use App\DataTables\Admin\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Dispute;
use App\Models\EmailTemplate;
use App\Models\FeesLimit;
use App\Models\PaymentMethod;
use App\Models\RequestPayment;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\VerifyUser;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $helper;
    protected $transaction;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function index(UsersDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_list';
        return $dataTable->render('admin.users.index', $data);
    }

    public function create()
    {
        // dd(session()->all());

        $data['menu']     = 'users';
        $data['sub_menu'] = 'users_create';

        $data['roles'] = $roles = Role::select('id', 'display_name')->where('user_type', "User")->get();
        // dd($roles);

        return view('admin.users.create', $data);
    }

    public function store(Request $request)
    {
        // dd($request->all());

        if ($_POST)
        {
            $rules = array(
                'first_name'            => 'required',
                'last_name'             => 'required',
                'email'                 => 'required|unique:users,email',
                'password'              => 'required|confirmed',
                'password_confirmation' => 'required',
            );

            $fieldNames = array(
                'first_name'            => 'First Name',
                'last_name'             => 'Last Name',
                'email'                 => 'Email',
                'password'              => 'Password',
                'password_confirmation' => 'Confirm Password',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                try
                {
                    \DB::beginTransaction();
                    $user             = new User();
                    $user->first_name = $request->first_name;
                    $user->last_name  = $request->last_name;

                    $formattedPhone = str_replace('+' . $request->carrierCode, "", $request->formattedPhone);
                    if (!empty($request->phone))
                    {
                        /*phone*/
                        $user->phone = preg_replace("/[\s-]+/", "", $formattedPhone);
                        // dd($user->phone);
                        $user->defaultCountry = $request->defaultCountry;
                        $user->carrierCode    = $request->carrierCode;
                        $user->formattedPhone = $request->formattedPhone;
                        /**/
                    }
                    else
                    {
                        $user->phone          = null;
                        $user->defaultCountry = null;
                        $user->carrierCode    = null;
                        $user->formattedPhone = null;
                    }

                    $user->email    = $request->email;
                    $user->password = \Hash::make($request->password);
                    $user->role_id  = $request->role;
                    // dd($user);
                    $user->save();

                    $UserDetail          = new UserDetail();
                    $UserDetail->user_id = $user->id;
                    $randomCountry       = Country::first(['id']);
                    if (!empty($randomCountry))
                    {
                        $UserDetail->country_id = $randomCountry->id;
                    }
                    $timezone             = Setting::where('name', 'default_timezone')->first(['value']);
                    $UserDetail->timezone = $timezone->value;
                    $UserDetail->save();

                    // Assigning user_type and role id to new user
                    RoleUser::insert(['user_id' => $user->id, 'role_id' => $user->role_id, 'user_type' => 'User']);

                    // Wallet creation
                    $default_currency = Currency::where(['default' => 1, 'status' => 'Active'])->select('id')->first();
                    Wallet::firstOrCreate([
                        'user_id'     => $user->id,
                        'currency_id' => $default_currency->id,
                        'balance'     => 0.00,
                        'is_default'  => 'Yes',
                    ]);

                    /**
                     * Entry for unknown transfer /request payments - starts
                     */
                    $userEmail          = $user->email;
                    $userFormattedPhone = $user->formattedPhone;

                    /**
                     * Entry for unknown transfer - starts
                     */
                    if (!empty($user->email) || !empty($user->formattedPhone))
                    {
                        $unknownTransferTransaction = Transaction::where(function ($q) use ($userEmail)
                        {
                            $q->where(['user_type' => 'unregistered']);
                            $q->where(['email' => $userEmail]);
                            $q->whereIn('transaction_type_id', [Transferred]);
                        })
                            ->orWhere(function ($q) use ($userFormattedPhone)
                        {
                                $q->where(['user_type' => 'unregistered']);
                                $q->where(['phone' => $userFormattedPhone]);
                                $q->whereIn('transaction_type_id', [Transferred]);
                            })
                            ->get(['transaction_reference_id', 'uuid']);
                        // dd($unknownTransferTransaction);
                    }

                    if (isset($unknownTransferTransaction))
                    {
                        // dd($unknownTransferTransaction);
                        foreach ($unknownTransferTransaction as $key => $value)
                        {
                            $transfer = Transfer::where(['uuid' => $value->uuid])->first(['id', 'uuid', 'receiver_id', 'status', 'currency_id', 'amount']);
                            // dd($transfer);
                            if ($transfer->uuid == $value->uuid)
                            {
                                $transfer->receiver_id = $user->id;
                                $transfer->status      = 'Success';
                                $transfer->save();

                                Transaction::where([
                                    'transaction_reference_id' => $value->transaction_reference_id,
                                    'transaction_type_id'      => Transferred,
                                ])->update([
                                    'end_user_id' => $user->id,
                                    'user_type'   => 'registered',
                                    'status'      => 'Success',
                                ]);

                                Transaction::where([
                                    'transaction_reference_id' => $value->transaction_reference_id,
                                    'transaction_type_id'      => Received,
                                ])->update([
                                    'user_id'   => $user->id,
                                    'user_type' => 'registered',
                                    'status'    => 'Success',
                                ]);

                                // dd($transfer->amount);
                                $unknownTransferWallet = Wallet::where(['user_id' => $user->id, 'currency_id' => $transfer->currency_id])->first(['id', 'balance']);
                                if (empty($unknownTransferWallet))
                                {
                                    $wallet              = new Wallet();
                                    $wallet->user_id     = $user->id;
                                    $wallet->currency_id = $transfer->currency_id;
                                    if ($wallet->currency_id == $default_currency->value)
                                    {
                                        $wallet->is_default = 'Yes';
                                    }
                                    else
                                    {
                                        $wallet->is_default = 'No';
                                    }
                                    $wallet->balance = $transfer->amount;
                                    $wallet->save();
                                }
                                else
                                {
                                    $unknownTransferWallet->balance = ($unknownTransferWallet->balance + $transfer->amount);
                                    $unknownTransferWallet->save();
                                }
                            }
                        }
                    }
                    /**
                     * Entry for unknown transfer - ends
                     */

                    /**
                     * Entry for unknown request payment - starts
                     */
                    if (!empty($user->email) || !empty($user->formattedPhone))
                    {
                        $unknownRequestTransaction = Transaction::where(function ($q) use ($userEmail)
                        {
                            $q->where(['user_type' => 'unregistered']);
                            $q->where(['email' => $userEmail]);
                            $q->whereIn('transaction_type_id', [Request_From]);
                        })
                            ->orWhere(function ($q) use ($userFormattedPhone)
                        {
                                $q->where(['user_type' => 'unregistered']);
                                $q->where(['phone' => $userFormattedPhone]);
                                $q->whereIn('transaction_type_id', [Request_From]);
                            })
                            ->get(['transaction_reference_id', 'uuid']);
                        // dd($unknownRequestTransaction);
                    }
                    if (isset($unknownRequestTransaction))
                    {
                        foreach ($unknownRequestTransaction as $key => $value)
                        {
                            $request_payment = RequestPayment::where(['uuid' => $value->uuid])->first(['id', 'uuid', 'currency_id', 'receiver_id']);
                            if ($request_payment->uuid == $value->uuid)
                            {
                                $request_payment->receiver_id = $user->id;
                                $request_payment->save();

                                Transaction::where([
                                    'transaction_reference_id' => $value->transaction_reference_id,
                                    'transaction_type_id'      => Request_From,
                                ])->update([
                                    'end_user_id' => $user->id,
                                    'user_type'   => 'registered',
                                ]);

                                Transaction::where([
                                    'transaction_reference_id' => $value->transaction_reference_id,
                                    'transaction_type_id'      => Request_To,
                                ])->update([
                                    'user_id'   => $user->id,
                                    'user_type' => 'registered',
                                ]);

                                $unknownRequestWallet = Wallet::where(['user_id' => $user->id, 'currency_id' => $request_payment->currency_id])->first(['id']);
                                if (empty($unknownRequestWallet))
                                {
                                    $wallet              = new Wallet();
                                    $wallet->user_id     = $user->id;
                                    $wallet->currency_id = $request_payment->currency_id;
                                    if ($wallet->currency_id == $default_currency->value)
                                    {
                                        $wallet->is_default = 'Yes';
                                    }
                                    else
                                    {
                                        $wallet->is_default = 'No';
                                    }
                                    $wallet->balance = 0.00;
                                    $wallet->save();
                                }
                            }
                        }
                    }
                    /**
                     * Entry for unknown request payment - ends
                     */

                    /**
                     * Entry for unknown transfer /request payments - ends
                     */

                    //email_verification - starts
                    if (!$user->user_detail->email_verification)
                    {
                        if (checkVerificationMailStatus() == "Enabled")
                        {
                            $verifyUser          = new VerifyUser();
                            $verifyUser->user_id = $user->id;
                            $verifyUser->token   = str_random(40);
                            $verifyUser->save();

                            //mail - temp -17
                            $englishUserVerificationEmailTempInfo = EmailTemplate::where(['temp_id' => 17, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                            $userVerificationEmailTempInfo        = EmailTemplate::where([
                                'temp_id'     => 17,
                                'language_id' => getDefaultLanguage(),
                                'type'        => 'email',
                            ])->select('subject', 'body')->first();

                            if (!empty($userVerificationEmailTempInfo->subject) && !empty($userVerificationEmailTempInfo->body))
                            {
                                // subject
                                $userVerificationEmailTempInfo_sub = $userVerificationEmailTempInfo->subject;
                                $userVerificationEmailTempInfo_msg = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $userVerificationEmailTempInfo->body); //p-1 - $user->first_name . ' ' . $user->last_name
                            }
                            else
                            {
                                $userVerificationEmailTempInfo_sub = $englishUserVerificationEmailTempInfo->subject;
                                $userVerificationEmailTempInfo_msg = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $englishUserVerificationEmailTempInfo->body);
                            }
                            $userVerificationEmailTempInfo_msg = str_replace('{email}', $user->email, $userVerificationEmailTempInfo_msg);                                            //p-2 - $user->email
                            $userVerificationEmailTempInfo_msg = str_replace('{verification_url}', url('user/verify', $user->verifyUser->token), $userVerificationEmailTempInfo_msg); //p-3 - $user->verifyUser->token
                            $userVerificationEmailTempInfo_msg = str_replace('{soft_name}', getCompanyName(), $userVerificationEmailTempInfo_msg);

                            if (checkAppMailEnvironment())
                            {
                                try
                                {
                                    \DB::commit();
                                    $this->email->sendEmail($user->email, $userVerificationEmailTempInfo_sub, $userVerificationEmailTempInfo_msg);
                                    $this->helper->one_time_message('success', 'An email has been sent to ' . $user->email . ' with verification code!');
                                    return redirect('admin/users');
                                }
                                catch (\Exception $e)
                                {
                                    \DB::rollBack();
                                    $this->helper->one_time_message('error', 'Unable to create user!');
                                    return redirect('admin/users');
                                }
                            }
                        }
                    }
                    \DB::commit();
                    $this->helper->one_time_message('success', 'User Created Successfully');
                    return redirect('admin/users');
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();
                    $this->helper->one_time_message('error', 'Unable to create user!');
                    return redirect('admin/users');
                }
                //email_verification - ends
            }
        }
    }

    public function edit($id)
    {
        $data['menu']  = 'users';
        $data['users'] = $users = User::find($id);
        // dd($users);

        $data['roles'] = $roles = Role::select('id', 'display_name')->where('user_type', "User")->get();
        return view('admin.users.edit', $data);
    }

    public function update(Request $request)
    {
        if ($_POST)
        {
            // dd($request->all());

            $rules = array(
                'first_name' => 'required',
                'last_name'  => 'required',
                'email'      => 'required|email|unique:users,email,' . $request->id,
            );

            $fieldNames = array(
                'first_name' => 'First Name',
                'last_name'  => 'Last Name',
                'email'      => 'Email',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {

                try
                {
                    \DB::beginTransaction();
                    $user             = User::find($request->id);
                    $user->first_name = $request->first_name;
                    $user->last_name  = $request->last_name;
                    $user->email      = $request->email;
                    $user->role_id    = $request->role;

                    $formattedPhone = ltrim($request->phone, '0');
                    if (!empty($request->phone))
                    {
                        /*phone*/
                        $user->phone          = preg_replace("/[\s-]+/", "", $formattedPhone);
                        $user->defaultCountry = $request->user_defaultCountry;
                        $user->carrierCode    = $request->user_carrierCode;
                        $user->formattedPhone = $request->formattedPhone;
                        /**/
                    }
                    else
                    {
                        $user->phone          = null;
                        $user->defaultCountry = null;
                        $user->carrierCode    = null;
                        $user->formattedPhone = null;
                    }

                    if (!is_null($request->password) && !is_null($request->password_confirmation))
                    {
                        $user->password = \Hash::make($request->password);
                    }
                    $user->save();

                    RoleUser::where(['user_id' => $request->id, 'user_type' => 'User'])->update(['role_id' => $request->role]); //by tuhin
                    \DB::commit();
                    $this->helper->one_time_message('success', 'User Updated Successfully');
                    return redirect('admin/users');
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('admin/users');
                }
            }
        }
    }

    /* Start of Admin Depsosit */
    public function eachUserDeposit($id, Request $request)
    {
        setActionSession();

        $data['menu']               = 'users';
        $data['users']              = $users              = User::find($id);
        $data['payment_met']        = $payment_met        = PaymentMethod::where(['name' => 'Mts', 'status' => 'Active'])->first(['id', 'name']);
        $data['active_currency']    = $activeCurrency    = Currency::where(['status' => 'Active'])->get(['id', 'status', 'code']);
        $feesLimitCurrency          = FeesLimit::where(['transaction_type_id' => Deposit, 'payment_method_id' => $payment_met->id, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
        // dd($feesLimitCurrency);
        $data['activeCurrencyList'] = $this->currencyList($activeCurrency, $feesLimitCurrency);

        if (!empty($request->all()))
        {
            $currency              = Currency::where(['id' => $request->currency_id, 'status' => 'Active'])->first(['symbol']);
            $request['currSymbol'] = $currency->symbol;
            $amount                 = $request->amount;
            $request['totalAmount'] = $amount + $request->fee;
            session(['transInfo' => $request->all()]);
            $data['transInfo'] = $transInfo = $request->all();

            //check amount and limit
            $feesDetails = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $request->currency_id, 'payment_method_id' => $transInfo['payment_method'], 'has_transaction' => 'Yes'])
                ->first(['min_limit', 'max_limit']);
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $data['error'] = 'Minimum amount ' . $feesDetails->min_limit;
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.deposit.create', $data);
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $data['error'] = 'Minimum amount ' . $feesDetails->min_limit . ' and Maximum amount ' . $feesDetails->max_limit;
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.deposit.create', $data);
                }
            }
            return view('admin.users.deposit.confirmation', $data);
        }
        return view('admin.users.deposit.create', $data);
    }

    //Extended function below - deposit
    public function currencyList($activeCurrency, $feesLimitCurrency)
    {
        $selectedCurrency = [];
        foreach ($activeCurrency as $aCurrency)
        {
            foreach ($feesLimitCurrency as $flCurrency)
            {
                if ($aCurrency->id == $flCurrency->currency_id && $aCurrency->status == 'Active' && $flCurrency->has_transaction == 'Yes')
                {
                    $selectedCurrency[$aCurrency->id]['id']   = $aCurrency->id;
                    $selectedCurrency[$aCurrency->id]['code'] = $aCurrency->code;
                }
            }
        }
        return $selectedCurrency;
    }
    /* End of Admin Depsosit */

    public function eachUserDepositSuccess(Request $request)
    {
        actionSessionCheck();

        $data['menu'] = 'users';
        $sessionValue = session('transInfo');
        // dd($sessionValue);

        $amount  = $sessionValue['amount'];
        $user_id = $sessionValue['user_id'];
        $uuid    = unique_code();
        $feeInfo = FeesLimit::where(['transaction_type_id' => Deposit, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
            ->first(['charge_percentage', 'charge_fixed']);
        //charge percentage calculation
        $p_calc = (($amount) * (@$feeInfo->charge_percentage) / 100);

        try
        {
            \DB::beginTransaction();
            //Deposit
            $deposit                    = new Deposit();
            $deposit->user_id           = $user_id;
            $deposit->currency_id       = $sessionValue['currency_id'];
            $deposit->payment_method_id = $sessionValue['payment_method'];
            $deposit->uuid              = $uuid;
            $deposit->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $deposit->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $deposit->amount            = $amount;
            $deposit->status            = 'Success';
            $deposit->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->payment_method_id        = $sessionValue['payment_method'];
            $transaction->transaction_reference_id = $deposit->id;
            $transaction->transaction_type_id      = Deposit;
            $transaction->uuid                     = $uuid;
            $transaction->subtotal                 = $amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $deposit->charge_percentage;
            $transaction->charge_fixed             = $deposit->charge_fixed;
            $transaction->total                    = $amount + $deposit->charge_percentage + $deposit->charge_fixed;
            $transaction->status                   = 'Success';
            $transaction->save();

            //Wallet
            $wallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $sessionValue['currency_id']])->first(['id', 'balance']);
            if (empty($wallet))
            {
                $createWallet              = new Wallet();
                $createWallet->user_id     = $user_id;
                $createWallet->currency_id = $sessionValue['currency_id'];
                $createWallet->balance     = $amount;
                $createWallet->is_default  = 'No';
                $createWallet->save();
            }
            else
            {
                $wallet->balance = ($wallet->balance + $amount);
                $wallet->save();
            }
            \DB::commit();

            $data['transaction'] = $transaction;
            $data['user_id']     = $user_id;
            $data['name']        = $sessionValue['fullname'];
            clearActionSession();
            return view('admin.users.deposit.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect("users/deposit/create/$user_id");
        }
    }

    public function eachUserdepositPrintPdf($transaction_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);

        $data['transactionDetails'] = $transactionDetails = Transaction::with(['payment_method:id,name', 'currency:id,symbol'])
            ->where(['id' => $transaction_id])
            ->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A3',
            'orientation' => 'P',
        ]);
        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;
        $mpdf->SetJS('this.print();');
        $mpdf->WriteHTML(view('admin.users.deposit.depositPrintPdf', $data));
        $mpdf->Output('deposit_' . time() . '.pdf', 'I'); //
    }

    /* Start of Admin Withdraw */
    public function eachUserWithdraw($id, Request $request)
    {
        setActionSession();
        $data['menu']        = 'users';
        $data['users']       = $users       = User::find($id);
        $data['payment_met'] = $payment_met = PaymentMethod::where(['name' => 'Mts'])->first(['id', 'name']);
        $payment_met_id      = $payment_met->id;
        $data['wallets']     = $wallets     = $users->wallets()->whereHas('active_currency', function ($q) use ($payment_met_id)
        {
            $q->whereHas('fees_limit', function ($query) use ($payment_met_id)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Withdrawal)->where('payment_method_id', $payment_met_id);
            });
        })
        ->with(['active_currency:id,code', 'active_currency.fees_limit:id,currency_id']) //Optimized
        ->get(['id', 'currency_id']);

        if (!empty($request->all()))
        {
            $amount                 = $request->amount;
            $currency               = Currency::where(['id' => $request->currency_id])->first(['symbol']);
            $request['currSymbol']  = $currency->symbol;
            $request['totalAmount'] = $request->amount + $request->fee;
            session(['transInfo' => $request->all()]);
            $data['transInfo'] = $transInfo = $request->all();

            //backend validation starts
            $request['transaction_type_id'] = Withdrawal;
            $request['currency_id']         = $request->currency_id;
            $request['payment_method_id']   = $request->payment_method;
            $amountFeesLimitCheck = $this->amountFeesLimitCheck($request);
            if ($amountFeesLimitCheck)
            {
                if ($amountFeesLimitCheck->getData()->success->status == 200)
                {
                    if ($amountFeesLimitCheck->getData()->success->totalAmount > $amountFeesLimitCheck->getData()->success->balance)
                    {
                        $data['error'] = "Insufficient Balance!";
                        $this->helper->one_time_message('error', $data['error']);
                        return view('admin.users.withdraw.create', $data);
                    }
                }
                elseif ($amountFeesLimitCheck->getData()->success->status == 401)
                {
                    $data['error'] = $amountFeesLimitCheck->getData()->success->message;
                    $this->helper->one_time_message('error', $data['error']);
                    return view('admin.users.withdraw.create', $data);
                }
            }
            //backend valdation ends
            return view('admin.users.withdraw.confirmation', $data);
        }
        return view('admin.users.withdraw.create', $data);
    }

    public function amountFeesLimitCheck(Request $request)
    {
        $amount      = $request->amount;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $request->currency_id, 'payment_method_id' => $request->payment_method_id])
            ->first(['min_limit', 'max_limit', 'charge_percentage', 'charge_fixed']);
        $wallet = Wallet::where(['currency_id' => $request->currency_id, 'user_id' => $request->user_id])->first(['balance']);


        if ($request->transaction_type_id == Withdrawal)
        {
            //Wallet Balance Limit Check Starts here
            $checkAmount = $amount + $feesDetails->charge_fixed + $feesDetails->charge_percentage;
            if (@$wallet)
            {
                if ((@$checkAmount) > (@$wallet->balance) || (@$wallet->balance < 0))
                {
                    $success['message'] = "Insufficient Balance!";
                    $success['status']  = '401';
                    return response()->json(['success' => $success]);
                }
            }
            //Wallet Balance Limit Check Ends here
        }



        //Amount Limit Check Starts here
        if (empty($feesDetails))
        {
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['pFeesHtml']      = formatNumber($feesPercentage);
            $success['fFees']          = $feesFixed;
            $success['fFeesHtml']      = formatNumber($feesFixed);
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = 0;
        }
        else
        {
            if (@$feesDetails->max_limit == null)
            {
                if ((@$amount < @$feesDetails->min_limit))
                {
                    $success['message'] = 'Minimum amount ' . $feesDetails->min_limit;
                    $success['status']  = '401';
                }
                else
                {
                    $success['status'] = 200;
                }
            }
            else
            {
                if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                {
                    $success['message'] = 'Minimum amount ' . $feesDetails->min_limit . ' and Maximum amount ' . $feesDetails->max_limit;
                    $success['status']  = '401';
                }
                else
                {
                    $success['status'] = 200;
                }
            }
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = $totalFess;
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesDetails->charge_percentage;
            $success['pFeesHtml']      = formatNumber($feesDetails->charge_percentage);
            $success['fFees']          = $feesDetails->charge_fixed;
            $success['fFeesHtml']      = formatNumber($feesDetails->charge_fixed);
            $success['min']            = $feesDetails->min_limit;
            $success['max']            = $feesDetails->max_limit;
            $success['balance']        = @$wallet->balance ? @$wallet->balance : 0;
        }
        //Amount Limit Check Ends here
        return response()->json(['success' => $success]);
    }

    public function eachUserWithdrawSuccess(Request $request)
    {
        actionSessionCheck();

        // dd($request->all());

        $data['menu'] = 'users';
        $sessionValue = session('transInfo');
        // dd($sessionValue);

        $feeInfo = FeesLimit::where(['transaction_type_id' => Withdrawal, 'currency_id' => $sessionValue['currency_id'], 'payment_method_id' => $sessionValue['payment_method']])
            ->first(['charge_percentage', 'charge_fixed']);
        $uuid    = unique_code();
        $user_id = $sessionValue['user_id'];
        $p_calc  = (($sessionValue['amount']) * (@$feeInfo->charge_percentage) / 100); //charge percentage calculation

        try
        {
            \DB::beginTransaction();
            //Withdrawal
            $withdrawal                    = new Withdrawal();
            $withdrawal->user_id           = $user_id;
            $withdrawal->currency_id       = $sessionValue['currency_id'];
            $withdrawal->payment_method_id = $sessionValue['payment_method'];
            $withdrawal->uuid              = $uuid;
            $withdrawal->charge_percentage = @$feeInfo->charge_percentage ? $p_calc : 0;
            $withdrawal->charge_fixed      = @$feeInfo->charge_fixed ? @$feeInfo->charge_fixed : 0;
            $withdrawal->subtotal          = ($sessionValue['amount'] - ($p_calc + (@$feeInfo->charge_fixed)));
            $withdrawal->amount            = $sessionValue['amount'];
            $withdrawal->status            = 'Success';
            $withdrawal->save();

            //Transaction
            $transaction                           = new Transaction();
            $transaction->user_id                  = $user_id;
            $transaction->currency_id              = $sessionValue['currency_id'];
            $transaction->payment_method_id        = $sessionValue['payment_method'];
            $transaction->transaction_reference_id = $withdrawal->id;
            $transaction->transaction_type_id      = Withdrawal;
            $transaction->uuid                     = $uuid;
            $transaction->subtotal                 = $withdrawal->amount;
            $transaction->percentage               = @$feeInfo->charge_percentage ? @$feeInfo->charge_percentage : 0;
            $transaction->charge_percentage        = $withdrawal->charge_percentage;
            $transaction->charge_fixed             = $withdrawal->charge_fixed;
            $transaction->total                    = '-' . ($withdrawal->amount + $withdrawal->charge_percentage + $withdrawal->charge_fixed);
            $transaction->status                   = 'Success';
            $transaction->save();

            //Wallet
            $wallet = Wallet::where(['user_id' => $user_id, 'currency_id' => $sessionValue['currency_id']])->first();
            if (!empty($wallet))
            {
                $wallet->balance = ($wallet->balance - ($withdrawal->amount + $withdrawal->charge_percentage + $withdrawal->charge_fixed));
                $wallet->save();
            }
            \DB::commit();

            $data['transaction'] = $transaction;
            $data['user_id']     = $user_id;
            $data['name']        = $sessionValue['fullname'];
            clearActionSession();
            return view('admin.users.withdraw.success', $data);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect("users/withdraw/create/$user_id");
        }
    }

    public function eachUserWithdrawPrintPdf($trans_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first(['value']);

        $data['transactionDetails'] = $transactionDetails = Transaction::with(['payment_method:id,name', 'currency:id,symbol'])
            ->where(['id' => $trans_id])->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A3',
            'orientation' => 'P',
        ]);
        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;
        $mpdf->SetJS('this.print();');
        $mpdf->WriteHTML(view('admin.users.withdraw.withdrawalPrintPdf', $data));
        $mpdf->Output('payout_' . time() . '.pdf', 'I');
    }
    /* End of Admin Withdraw */

    public function eachUserTransaction($id, EachUserTransactionsDataTable $dataTable)
    {
        $data['menu']  = 'users';
        $data['users'] = $users = User::find($id);

        $data['t_status']   = $t_status   = Transaction::where(['user_id' => $id])->select('status')->groupBy('status')->get();
        $data['t_currency'] = $t_currency = Transaction::where(['user_id' => $id])->select('currency_id')->groupBy('currency_id')->get();
        $data['t_type']     = $t_type     = Transaction::where(['user_id' => $id])->select('transaction_type_id')->groupBy('transaction_type_id')->get();

        if (isset($_GET['btn']))
        {
            // dd($_GET);
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['type']     = $_GET['type'];

            if (empty($_GET['from']))
            {
                // dd('empty');
                $data['from'] = null;
                $data['to']   = null;
                // dd($transactions);
            }
            else
            {
                // dd('not empty');
                $data['from'] = $_GET['from'];
                $data['to']   = $_GET['to'];
            }
        }
        else
        {
            // dd('init');
            $data['from'] = null;
            $data['to']   = null;

            $data['status']   = 'all';
            $data['currency'] = 'all';
            $data['type']     = 'all';
        }
        return $dataTable->with('user_id', $id)->render('admin.users.eachusertransaction', $data); //passing $id to dataTable ass user_id
    }

    public function eachUserWallet($id)
    {
        $data['menu']    = 'users';
        $data['wallets'] = $wallets = Wallet::where(['user_id' => $id])->orderBy('id', 'desc')->get();
        $data['users']   = User::find($id);
        return view('admin.users.eachuserwallet', $data);
    }

    public function eachUserTicket($id)
    {
        $data['menu']    = 'users';
        $data['tickets'] = $tickets = Ticket::where(['user_id' => $id])->orderBy('id', 'desc')->get();
        $data['users']   = User::find($id);
        return view('admin.users.eachuserticket', $data);
    }

    public function eachUserDispute($id)
    {
        $data['menu'] = 'users';

        $data['disputes'] = $disputes = Dispute::where(['claimant_id' => $id])->orWhere(['defendant_id' => $id])->orderBy('id', 'desc')->get();

        $data['users'] = User::find($id);

        return view('admin.users.eachuserdispute', $data);
    }

    public function destroy($id)
    {
        // dd($id);
        $user = User::find($id);
        if ($user)
        {
            $user->delete();

            // Deleting Non-Relational Table Entries
            ActivityLog::where(['user_id' => $id])->delete();
            RoleUser::where(['user_id' => $id, 'user_type' => 'User'])->delete();

            $this->helper->one_time_message('success', 'User Deleted Successfully');
            return redirect('admin/users');
        }
    }

    public function postEmailCheck(Request $request)
    {

        if (isset($request->admin_id) || isset($request->user_id))
        {
            if (isset($request->type) && $request->type == "admin-email")
            {
                $req_id = $request->admin_id;
                $email  = Admin::where(['email' => $request->email])->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })->exists();
            }
            else
            {
                $req_id = $request->user_id;
                $email  = User::where(['email' => $request->email])->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })->exists();
            }
        }
        else
        {
            if (isset($request->type) && $request->type == "admin-email")
            {
                $email = Admin::where(['email' => $request->email])->exists();
            }
            else
            {
                $email = User::where(['email' => $request->email])->exists();
            }
        }

        if ($email)
        {
            $data['status'] = true;
            $data['fail']   = "The email has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "Email Available!";
        }
        return json_encode($data);
    }

    public function duplicatePhoneNumberCheck(Request $request)
    {
        // dd($request->all());
        $req_id = $request->id;

        if (isset($req_id))
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->where(function ($query) use ($req_id)
            {
                $query->where('id', '!=', $req_id);
            })->first(['phone', 'carrierCode']);
        }
        else
        {
            // dd('no id');
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->first(['phone', 'carrierCode']);
        }

        if (!empty($user->phone) && !empty($user->carrierCode))
        {
            $data['status'] = true;
            $data['fail']   = "The phone number has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "The phone number is Available!";
        }
        return json_encode($data);
    }

    public function adminList(AdminsDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'admin_users_list';

        return $dataTable->render('admin.users.adminList', $data);
    }

    public function adminCreate()
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'admin_users_create';

        $data['roles'] = $roles = Role::select('id', 'display_name')->where('user_type', 'Admin')->get();
        // dd($roles);

        return view('admin.users.adminCreate', $data);
    }

    public function adminStore(Request $request)
    {

        $rules = array(
            'first_name'            => 'required',
            'last_name'             => 'required',
            'email'                 => 'required|unique:admins,email',
            'password'              => 'required|confirmed',
            'password_confirmation' => 'required',
        );

        $fieldNames = array(
            'first_name'            => 'First Name',
            'last_name'             => 'Last Name',
            'email'                 => 'Email',
            'password'              => 'Password',
            'password_confirmation' => 'Confirm Password',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $admin = new Admin();

            // $email_explode     = explode("@", $request->email);
            // $admin->username   = $email_explode[0];

            $admin->first_name = $request->first_name;
            $admin->last_name  = $request->last_name;
            $admin->email      = $request->email;
            $admin->password   = Hash::make($request->password);
            $admin->role_id    = $request->role;
            $admin->save();
            RoleUser::insert(['user_id' => $admin->id, 'role_id' => $request->role, 'user_type' => 'Admin']);
        }

        //condition because same function used in installer for create admin
        if (!isset($request->from_installer))
        {
            $this->helper->one_time_message('success', 'Admin Created Successfully!');
            return redirect()->intended("admin/admin_users");
        }
    }

    public function adminEdit($id)
    {
        $data['menu']  = 'users';
        $data['admin'] = $users = Admin::find($id);
        $data['roles'] = $roles = Role::select('id', 'display_name')->where('user_type', "Admin")->get();
        return view('admin.users.adminEdit', $data);
    }

    public function adminUpdate(Request $request)
    {

        $rules = array(
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:admins,email,' . $request->admin_id,
        );

        $fieldNames = array(
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email',
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $admin             = Admin::find($request->admin_id);
            $admin->first_name = $request->first_name;
            $admin->last_name  = $request->last_name;
            $admin->email      = $request->email;
            $admin->role_id    = $request->role;
            $admin->save();
            RoleUser::where(['user_id' => $admin->id, 'user_type' => 'Admin'])->update(['role_id' => $request->role]);
            $this->helper->one_time_message('success', 'Admin Updated Successfully!');
            return redirect()->intended("admin/admin_users");
        }
    }

    public function adminDestroy($id)
    {
        $admin = Admin::find($id);
        if ($admin)
        {
            $admin->delete();

            // Deleting Non-Relational Table Entries
            ActivityLog::where(['user_id' => $id])->delete();
            RoleUser::where(['user_id' => $id, 'user_type' => 'Admin'])->delete();

            $this->helper->one_time_message('success', 'Admin Deleted Successfully');
            return redirect()->intended("admin/admin_users");
        }
    }

}
