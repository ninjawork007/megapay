<?php

namespace App\Http\Controllers\Users;

use App;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use Validator;

class VoucherController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function index(Request $request)
    {
        $data['menu'] = 'voucher';

        if ($_POST)
        {
            $rules = array(
                'amount'      => 'required|check_wallet_balance',
                'currency_id' => 'required',
            );

            $fieldNames = array(
                'amount'      => 'Amount',
                'currency_id' => 'Currency',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $feesDetails = App\Models\FeesLimit::where(['transaction_type_id' => Voucher_Activated, 'currency_id' => $request->currency_id])->first();
                $currency    = Currency::find($request->currency_id);
                Session::put('voucher_amount', $request->amount);
                Session::put('voucher_currency', $currency->code);
                $data['amount']      = $request->amount;
                $data['currency']    = $currency->symbol;
                $data['fees']        = $fees        = (($feesDetails->charge_percentage * $request->amount) / 100) + $feesDetails->charge_fixed;
                $data['totalAmount'] = $request->amount + $fees;
                $data['currency_id'] = $request->currency_id;
                Session::put('voucher_fees', $fees);
                return view('user_dashboard.voucher.confirmation', $data);
            }
        }
        else
        {

            $data['list'] = $list= Voucher::where(['user_id' => Auth::user()->id])
                ->orderBy('id', 'desc')
                ->paginate(10);
            // dd($list);

            // $data['list'] = Voucher::where(['user_id' => Auth::user()->id])->orderBy('id', 'desc')->paginate(10);

            $data['wallets'] = auth()->user()->wallets()->whereHas('active_currency', function ($q)
            {
                $q->whereHas('fees_limit', function ($query)
                {
                    $query->where('has_transaction', 'Yes')->where('transaction_type_id', Voucher_Activated);
                });
            })->get();

            return view('user_dashboard.voucher.list', $data);
        }
    }

    public function add()
    {
        $data['menu']          = 'voucher';
        $data['content_title'] = 'Voucher';
        $data['icon']          = 'gift';

        $data['currencies'] = auth()->user()->wallets()->whereHas('active_currency', function ($q)
        {
            $q->whereHas('fees_limit', function ($query)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Voucher_Activated);
            });
        })->get();
        // dd($data['currencies']);
        return view('user_dashboard.voucher.create', $data);
    }

    public function store(Request $request)
    {
        $uid = Auth::user()->id;
        #region
        # for checking the requested wallet is the current wallet and active or not
        $wallets = auth()->user()->wallets()->whereHas('active_currency', function ($q)
        {
            $q->whereHas('fees_limit', function ($query)
            {
                $query->where('has_transaction', 'Yes')->where('transaction_type_id', Voucher_Activated);
            });
        })->get();

        $arr = [];
        foreach ($wallets as $wallet)
        {
            $arr[] = $wallet->currency_id;
        }
        if (!in_array($request->currency_id, $arr))
        {
            $this->helper->one_time_message('error', __('Currency not found!'));
            return back();
        }
        #endregion
        $rules = array(
            'amount' => 'required|check_wallet_balance',
        );

        $fieldNames = array(
            'amount' => 'Amount',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $uuid      = unique_code();
            $myWallet  = Wallet::where(['user_id' => $uid, 'currency_id' => $request->currency_id])->first();
            $feesLimit = App\Models\FeesLimit::where(['currency_id' => $request->currency_id, 'transaction_type_id' => Voucher_Activated])->first();
            // Deduct from base wallet

            $totalFees = ($feesLimit->charge_percentage * $request->amount) / 100 + $feesLimit->charge_fixed;

            $wallet          = Wallet::find($myWallet->id);
            $wallet->balance = ($wallet->balance - ($request->amount + $totalFees));
            $wallet->save();

            $voucher                    = new Voucher();
            $voucher->user_id           = $uid;
            $voucher->currency_id       = $request->currency_id;
            $voucher->charge_percentage = ($feesLimit->charge_percentage * $request->amount) / 100;
            $voucher->charge_fixed      = $feesLimit->charge_fixed;
            $voucher->amount            = $request->amount;
            $voucher->code              = strtoupper(str_random(30));
            $voucher->status            = 'Success';
            $voucher->uuid              = $uuid;
            $voucher->save();

            $transaction                           = new Transaction();
            $transaction->user_id                  = $uid;
            $transaction->currency_id              = $request->currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $voucher->id;
            $transaction->transaction_type_id      = Voucher_Created;
            $transaction->subtotal                 = $request->amount;

            $transaction->percentage = $feesLimit->charge_percentage; //added by parvez

                                                                                                        // $transaction->charge_percentage = $feesLimit->charge_percentage;
            $transaction->charge_percentage = ($feesLimit->charge_percentage * $request->amount) / 100; //added by parvez

            $transaction->charge_fixed = $feesLimit->charge_fixed;

            $transaction->total = '-' . ($request->amount + $totalFees);

            $transaction->status = $voucher->status;
            $transaction->save();

            //$this->helper->one_time_message('success', 'Voucher Created Successfully!');
            $data['totalAmount'] = $request->amount + $totalFees;
            $data['voucher_id']  = $voucher->id;


            $data['voucher_fee'] = $transaction->charge_percentage + $transaction->charge_fixed;

            $data['currency_code'] = Currency::find($request->currency_id)->symbol;
            $data['message']       = __("Voucher Created Successfully.");
            $data['btnText']       = __("Create Voucher Again");

            $data['transaction_id']  = $transaction->id;

            Session::forget('voucher_amount');
            Session::forget('voucher_currency');
            Session::forget('voucher_fees');

            return view('user_dashboard.voucher.success', $data);
        }
    }

    public function activate()
    {
        $data['menu']          = 'voucher';
        $data['content_title'] = 'Voucher';
        $data['icon']          = 'gift';

        return view('user_dashboard.voucher.activate', $data);
    }

    public function checkVoucherCode(Request $request)
    {
        // dd($request->code);
        $voucher = Voucher::where(['user_id' => Auth::user()->id, 'code' => $request->code])->select('redeemed','status')->first();

        $voucherCodeExists = Voucher::where(['code' => $request->code])->select('code')->exists();
        // dd($voucher);

        $data = [];
        if (isset($voucher))
        {
            if ($voucher->status == 'Success' && $voucher->redeemed == "Yes")
            {
                $data['status'] = true;
                $data['error']   = __('Voucher Already Activated!');
            }
            elseif ($voucher->status == 'Pending' && $voucher->redeemed =='Yes')
            {
                $data['status'] = 200;
                $data['error']   = __('Sorry, Cannot activate pending voucher!');
            }
            elseif ($voucher->status == 'Pending')
            {
                $data['status'] = 201;
                $data['error']   = __('Sorry, Cannot activate pending voucher!');
            }
        }
        elseif (!isset($voucherCodeExists) || empty($voucherCodeExists))
        {
            $data['status']  = 405;
            $data['error']   = __('Sorry, voucher not found!');
        }
        else
        {
            $data['status']  = 404;
            $data['error']   = "";
        }
        return json_encode($data);
    }

    public function activationComplete(Request $request)
    {
        $data['menu'] = 'voucher';
        $uid          = Auth::user()->id;
        if ($_POST)
        {
            // dd($request->all());

            $rules      = array('code' => 'required|check_voucher_code');
            $fieldNames = array('code' => 'Voucher Code');

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                Session::put('voucher_code', $request->code);
                $codeVoucher = Voucher::where(['code' => trim($request->code)])->first();
                if ($codeVoucher->redeemed == 'Yes')
                {
                    $this->helper->one_time_message('error', __('Voucher Already activated!'));
                    return back();
                }
                if ($codeVoucher->status != 'Success')
                {
                    $this->helper->one_time_message('error', __('Problem with voucher code!'));
                    return back();
                }

                Session::put('amount', $codeVoucher->amount);

                $data['amount']       = $codeVoucher->amount;
                $data['voucher_code'] = $request->code;
                $data['totalAmount']  = $codeVoucher->amount;
                $data['currency']     = $codeVoucher->currency->symbol;
                return view('user_dashboard.voucher.activeConfirmation', $data);
            }
        }
        else
        {
            $code = Session::get('voucher_code');
            if (!$code)
            {
                $this->helper->one_time_message('error', __('Need an activation code!'));
                return back();
            }
            Session::forget('voucher_code');

            $voucher = Voucher::where(['code' => trim($code)])->first();
            if ($voucher->redeemed == 'Yes')
            {
                $this->helper->one_time_message('error', 'Voucher Already activated!');
                return back();
            }
            if ($voucher->status != 'Success')
            {
                $this->helper->one_time_message('error', 'Problem with voucher code!');
                return back();
            }
            $voucher->activator_id = $uid;
            $voucher->redeemed     = 'Yes';
            $voucher->save();

            $wallet = Wallet::where(['user_id' => $uid, 'currency_id' => $voucher->currency_id])->first();
            if (empty($wallet))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = $uid;
                $wallet->currency_id = $voucher->currency_id;
                $wallet->balance     = $voucher->amount;
                $wallet->is_default  = 'No';
                $wallet->save();
            }
            else
            {
                $wallet->balance = ($wallet->balance + $voucher->amount);
                $wallet->save();
            }

            //
            $transaction_A = Transaction::where(['transaction_type_id' => Voucher_Created, 'transaction_reference_id' => $voucher->id])->first();

            $transaction_A->end_user_id = Auth::user()->id;
            $transaction_A->status      = $voucher->status;
            $transaction_A->save();

            $uuid                                  = unique_code();
            $transaction                           = new Transaction();
            $transaction->user_id                  = Auth::user()->id;
            $transaction->end_user_id              = $voucher->user_id;
            $transaction->currency_id              = $voucher->currency_id;
            $transaction->uuid                     = $uuid;
            $transaction->transaction_reference_id = $voucher->id;
            $transaction->transaction_type_id      = Voucher_Activated;
            $transaction->subtotal                 = $voucher->amount;
            $transaction->charge_percentage        = 0;
            $transaction->charge_fixed             = 0;
            $transaction->total                    = $voucher->amount;
            $transaction->status                   = $voucher->status;

            $transaction->save();

            // Mail for voucher activation
            $activator_info = EmailTemplate::where([
                'temp_id'     => 3,
                'language_id' => Session::get('default_language'),
            ])->select('subject', 'body')->first();

            $activator_subject = $activator_info->subject;

            $activator_msg = str_replace('{user_id}', $voucher->activator->first_name . ' ' . $voucher->activator->last_name, $activator_info->body);
            $activator_msg = str_replace('{amount}', moneyFormat($voucher->currency->symbol, formatNumber($voucher->amount)), $activator_msg);
            $activator_msg = str_replace('{uuid}', $uuid, $activator_msg);
            $activator_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $activator_msg);
            $activator_msg = str_replace('{code}', $code, $activator_msg);
            $activator_msg = str_replace('{soft_name}', Session::get('name'), $activator_msg);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($voucher->activator->email, $activator_subject, $activator_msg);
            }
            $data['totalAmount']   = $voucher->amount;
            $data['voucher_id']    = $voucher->id;
            $data['currency_code'] = Currency::find($voucher->currency_id)->symbol;
            $data['message']       = __("Voucher Activated Successfully.");
            $data['btnText']       = __("Active Another Voucher");

            $data['transaction_id']  = $transaction->id;

            return view('user_dashboard.voucher.success', $data);
        }
    }

    /**
     * Generate voucherPrintPdf
     */
    public function voucherPrintPdf($trans_id)
    {
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first();

        $data['transactionDetails'] = $transactionDetails = Transaction::where(['id' => $trans_id])->first();
        // dd($transactionDetails);

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
        $mpdf->WriteHTML(view('user_dashboard.voucher.voucherPrintPdf', $data));
        $mpdf->Output('voucher_' . time() . '.pdf', 'I'); // this will output data
    }
}
