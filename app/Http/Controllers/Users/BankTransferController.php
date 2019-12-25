<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\FeesLimit;
use App\Models\File;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BankTransferController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function create(Request $request)
    {
        //set the session for validating the action
        setActionSession();

        if (!$_POST)
        {
            // $data['menu'] = 'bank_transfer';
            $data['menu']    = 'send_receive';
            $data['submenu'] = 'bank_transfer';

            $walletList = Wallet::where(['user_id' => auth()->user()->id])->get();

            $checkWhetherCurrencyIsActivated = FeesLimit::where(['transaction_type_id' => Bank_Transfer, 'has_transaction' => 'Yes'])->get(['currency_id', 'has_transaction']);
            $data['walletList']              = $selectedWallet              = $this->walletList($walletList, $checkWhetherCurrencyIsActivated);

            $data['transInfo']['wallet'] = $walletList[0]['id'];

            return view('user_dashboard.banktransfer.create', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());

            $rules = array(
                'amount' => 'required|numeric',
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
                $data['countries'] = Country::get(['id', 'name']);

                $data['walletList'] = Wallet::where(['user_id' => auth()->user()->id])->get();

                $amount      = $request->amount;
                $wallet_id   = $request->wallet;
                $user_id     = auth()->user()->id;
                $wallets     = Wallet::where(['id' => $wallet_id, 'user_id' => $user_id])->first();
                $currency_id = $wallets->currency_id;

                $currency               = Currency::where(['id' => $currency_id])->first();
                $request['currency_id'] = $currency_id;
                $request['currSymbol']  = $currency->symbol;
                $request['totalAmount'] = $request['amount'] + $request['fee'];
                session(['transInfo' => $request->all()]);
                $data['transInfo'] = $request->all();

                //You cannot send money to yourself
                if ($request->has('receiver'))
                {
                    if ($request->receiver == auth()->user()->email)
                    {
                        $data['error'] = __('You Cannot Send Money To Yourself!');
                        return view('user_dashboard.banktransfer.create', $data);
                    }
                }

                //User Wallet Balance Check starts here
                if ((@$wallets->balance) < (@$request->amount))
                {
                    $data['error'] = __("You don't have sufficient balance!");
                    return view('user_dashboard.banktransfer.create', $data);
                }

                $feesDetails = FeesLimit::where(['transaction_type_id' => Bank_Transfer, 'currency_id' => $currency_id])->first(['max_limit', 'min_limit']);
                //Code for Amount Limit starts here
                if (@$feesDetails->max_limit == null)
                {
                    if ((@$amount < @$feesDetails->min_limit))
                    {
                        $data['error'] = __('Minimum amount ') . $feesDetails->min_limit;
                        return view('user_dashboard.banktransfer.create', $data);
                    }
                }
                else
                {
                    if ((@$amount < @$feesDetails->min_limit) || (@$amount > @$feesDetails->max_limit))
                    {
                        $data['error'] = __('Minimum amount ') . $feesDetails->min_limit . __(' and Maximum amount ') . $feesDetails->max_limit;
                        return view('user_dashboard.banktransfer.create', $data);
                    }
                }
            }
            return view('user_dashboard.banktransfer.confirmation', $data);
        }
    }

    public function walletList($activeWallet, $feesLimitWallet)
    {
        $selectedWallet = [];
        // $i              = 0;
        foreach ($activeWallet as $aWallet)
        {
            foreach ($feesLimitWallet as $flWallet)
            {
                if ($aWallet->currency_id == $flWallet->currency_id && $flWallet->has_transaction == 'Yes')
                {
                    $selectedWallet[$aWallet->id]['id']            = $aWallet->id;
                    $selectedWallet[$aWallet->id]['currency_id']   = $aWallet->currency_id;
                    $selectedWallet[$aWallet->id]['currency_code'] = $aWallet->currency->code;
                    // $i++;
                }
            }
        }
        return $selectedWallet;
    }

    //Code for Amount Limit Check
    public function amountLimitCheck(Request $request)
    {
        $amount    = $request->amount;
        $wallet_id = $request->wallet_id;
        $user_id   = auth()->user()->id;

        $wallets = Wallet::where(['id' => $wallet_id, 'user_id' => $user_id])->first();

        $currency_id = $wallets->currency_id;
        $feesDetails = FeesLimit::where(['transaction_type_id' => $request->transaction_type_id, 'currency_id' => $currency_id])->first();

        //Code for Amount Limit starts here
        if (@$feesDetails->max_limit == null)
        {
            if ((@$amount < @$feesDetails->min_limit))
            {
                $success['message'] = __('Minimum amount ') . $feesDetails->min_limit;
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
                $success['message'] = __('Minimum amount ') . $feesDetails->min_limit . __(' and Maximum amount ') . $feesDetails->max_limit;
                $success['status']  = '401';
            }
            else
            {
                $success['status'] = 200;
            }
        }
        //Code for Fees Limit Starts here
        if (empty($feesDetails))
        {
            $feesPercentage            = 0;
            $feesFixed                 = 0;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;
            $success['totalFees']      = ($totalFess);
            $success['totalFeesHtml']  = formatNumber($totalFess);
            $success['totalAmount']    = $totalAmount;
            $success['pFees']          = $feesPercentage;
            $success['fFees']          = $feesFixed;
            $success['min']            = 0;
            $success['max']            = 0;
            $success['balance']        = $wallets->balance;
        }
        else
        {
            $feesPercentage            = $amount * ($feesDetails->charge_percentage / 100);
            $feesFixed                 = $feesDetails->charge_fixed;
            $totalFess                 = $feesPercentage + $feesFixed;
            $totalAmount               = $amount + $totalFess;
            $success['feesPercentage'] = $feesPercentage;
            $success['feesFixed']      = $feesFixed;

            $success['totalFees']     = ($totalFess);
            $success['totalFeesHtml'] = formatNumber($totalFess);

            $success['totalAmount'] = $totalAmount;
            $success['pFees']       = $feesDetails->charge_percentage;
            $success['fFees']       = $feesDetails->charge_fixed;
            $success['min']         = $feesDetails->min_limit;
            $success['max']         = $feesDetails->max_limit;
            $success['balance']     = $wallets->balance;
        }
        //Code for Fees Limit Ends here
        return response()->json(['success' => $success]);
    }

    public function banktransferComplete(Request $request)
    {
        // dd($sessionValue, $request->all());

        //initializing session
        actionSessionCheck();

        $this->validate($request, [
            'account_name'        => 'required',
            'account_number'      => 'required|numeric',
            'bank_branch_name'    => 'required',
            'bank_branch_city'    => 'required',
            'swift_code'          => 'required|numeric',
            'bank_branch_address' => 'required',
            'bank_name'           => 'required',
            'attached_file'       => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,gif,bmp|max:10000',
        ]);

        $sessionValue = Session::get('transInfo');

        $unique_code = unique_code();

        // File
        if ($request->hasFile('attached_file'))
        {
            if (!empty($request->attached_file))
            {
                $fileName     = $request->file('attached_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());

                if ($file_extn == 'docx' || $file_extn == 'rtf' || $file_extn == 'doc' || $file_extn == 'pdf' || $file_extn == 'png'
                    || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'gif' || $file_extn == 'bmp')
                {

                    $path       = 'uploads/files/bank_attached_files/transfers';
                    $uploadPath = public_path($path);
                    $fileName->move($uploadPath, $uniqueName);
                    $file               = new File();
                    $file->user_id      = auth()->user()->id;
                    $file->filename     = $uniqueName;
                    $file->originalname = $originalName;
                    $file->type         = $file_extn;
                    $file->save();
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid File Format!');
                }
            }
        }

        $bank                      = new Bank();
        $bank->user_id             = auth()->user()->id;
        $bank->currency_id         = $sessionValue['currency_id'];
        $bank->country_id          = $request->country;
        $bank->bank_name           = $request->bank_name;
        $bank->bank_branch_name    = $request->bank_branch_name;
        $bank->bank_branch_city    = $request->bank_branch_city;
        $bank->bank_branch_address = $request->bank_branch_address;
        $bank->account_name        = $request->account_name;
        $bank->account_number      = $request->account_number;
        $bank->swift_code          = $request->swift_code;
        $bank->save();

        $transfer                = new Transfer();
        $transfer->sender_id     = auth()->user()->id;
        $request_wallet_currency = Wallet::find($sessionValue['wallet'])->currency->id;
        $transfer->currency_id   = $request_wallet_currency;
        $transfer->bank_id       = $bank->id;

        if (!empty($request->attached_file))
        {
            $transfer->file_id = $file->id;
        }

        $transfer->uuid   = $unique_code;
        $transfer->fee    = $sessionValue['fee'];
        $transfer->amount = $sessionValue['amount'];
        $transfer->status = 'Pending';
        $transfer->save();

        //Sender Transaction save starts here
        $transaction              = new Transaction();
        $transaction->user_id     = auth()->user()->id;
        $transaction->currency_id = $request_wallet_currency;
        $transaction->bank_id     = $bank->id;

        if (!empty($request->attached_file))
        {
            $transaction->file_id = $file->id;
        }

        $transaction->uuid                     = $unique_code;
        $transaction->transaction_reference_id = $transfer->id;
        $transaction->transaction_type_id      = Bank_Transfer;
        $transaction->subtotal                 = $sessionValue['amount'];
        $feesDetails                           = FeesLimit::where(['transaction_type_id' => Bank_Transfer, 'currency_id' => $sessionValue['currency_id']])->first(['charge_percentage', 'charge_fixed']);
        $transaction->percentage               = @$feesDetails->charge_percentage ? @$feesDetails->charge_percentage : 0;
        $transaction->charge_percentage        = @$feesDetails->charge_percentage ? ($sessionValue['amount']) * (@$feesDetails->charge_percentage / 100) : 0;
        $transaction->charge_fixed             = @$feesDetails->charge_fixed ? @$feesDetails->charge_fixed : 0;
        $total_with_fee                        = $sessionValue['amount'] + $sessionValue['fee'];
        $transaction->total                    = '-' . ($total_with_fee);
        $transaction->status                   = $transfer->status;
        $transaction->save();

        /**
         * Mail for sender
         */
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 3, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $sender_info           = EmailTemplate::where(['temp_id' => 3, 'language_id' => Session::get('default_language'), 'type' => 'email'])->select('subject', 'body')->first();
        if (!empty($sender_info->subject) && !empty($sender_info->body))
        {
            $sender_subject = $sender_info->subject;
            $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $sender_info->body);
        }
        else
        {
            $sender_subject = $englishSenderLanginfo->subject;
            $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLanginfo->body);
        }
        $sender_msg = str_replace('{uuid}', $unique_code, $sender_msg);
        $sender_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $sender_msg);
        $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);
        $sender_msg = str_replace('{fee}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['percentage_fee'] + $sessionValue['fixed_fee'])), $sender_msg);
        $sender_msg = str_replace('{bank_name}', $bank->bank_name, $sender_msg);
        $sender_msg = str_replace('{branch_name}', $bank->bank_branch_name, $sender_msg);
        $sender_msg = str_replace('{account_name}', $bank->account_name, $sender_msg);
        $sender_msg = str_replace('{soft_name}', Session::get('name'), $sender_msg);
        if (checkAppMailEnvironment())
        {
            $this->email->sendEmail($transfer->sender->email, $sender_subject, $sender_msg);
        }

        /**
         * SMS for sender
         */
        $englishSenderLangSMSinfo = EmailTemplate::where(['temp_id' => 3, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $senderSmsInfo            = EmailTemplate::where(['temp_id' => 3, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();
        if (!empty($senderSmsInfo->subject) && !empty($senderSmsInfo->body))
        {
            $sender_subject = $senderSmsInfo->subject;
            $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $senderSmsInfo->body);
        }
        else
        {
            $sender_subject = $englishSenderLangSMSinfo->subject;
            $sender_msg     = str_replace('{sender_id}', $transfer->sender->first_name . ' ' . $transfer->sender->last_name, $englishSenderLangSMSinfo->body);
        }
        $sender_msg = str_replace('{amount}', moneyFormat($transfer->currency->symbol, formatNumber($sessionValue['amount'])), $sender_msg);

        if (!empty($transfer->sender->carrierCode) && !empty($transfer->sender->phone))
        {
            if (checkAppSmsEnvironment())
            {
                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                {
                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfer->sender->carrierCode . $transfer->sender->phone, $sender_msg);
                }
            }
        }
        $data['menu']                  = 'send_receive';
        $data['submenu']               = 'send';
        $data['transInfo']             = $sessionValue;
        $data['transInfo']['trans_id'] = $transaction->id;
        $data['content_title']         = 'Money Transfer';

        //clearing session
        clearActionSession();

        return view('user_dashboard.banktransfer.success', $data);
    }

    /**
     * Generate pdf for print
     */
    public function banktransferPrintPdf($trans_id)
    {
        // dd($trans_id);
        $data['companyInfo'] = Setting::where(['type' => 'general', 'name' => 'logo'])->first();

        // dd($data['companyInfo']);

        $data['transactionDetails'] = $transactionDetails = Transaction::where(['id' => $trans_id])->first();
        // dd($transactionDetails);

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
        $mpdf = new \Mpdf\Mpdf([
            'mode'                 => 'utf-8',
            'format'               => 'A3',
            'orientation'          => 'P',
            'shrink_tables_to_fit' => 0,
        ]);
        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;
        $mpdf->SetJS('this.print();');
        $mpdf->WriteHTML(view('user_dashboard.banktransfer.transferPaymentPdf', $data));
        $mpdf->Output('sendMoney_' . time() . '.pdf', 'I'); // this will output data
    }
}
