<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MerchantPaymentsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\EmailTemplate;
use App\Models\MerchantPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class MerchantPaymentController extends Controller
{
    protected $helper;
    protected $email;
    protected $merchant_payment;

    public function __construct()
    {
        $this->helper           = new Common();
        $this->email            = new EmailController();
        $this->merchant_payment = new MerchantPayment();
    }

    public function index(MerchantPaymentsDataTable $dataTable)
    {
        $data['menu']     = 'merchant';
        $data['sub_menu'] = 'merchant_payments';

        $data['merchant_payments_currencies'] = $merchant_payments_currencies = $this->merchant_payment->select('currency_id')->groupBy('currency_id')->get();

        $data['merchant_payments_status'] = $merchant_payments_status = $this->merchant_payment->select('status')->groupBy('status')->get();

        $data['merchant_payments_pm'] = $merchant_payments_pm = $this->merchant_payment->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['pm']       = $_GET['payment_methods'];

            if (empty($_GET['from']))
            {
                $data['from'] = null;
                $data['to']   = null;
            }
            else
            {
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
            $data['pm']       = 'all';
        }
        return $dataTable->render('admin.merchant_payments.list', $data);
    }

    public function merchantPaymentCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status   = isset($_GET['status']) ? $_GET['status'] : null;
        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;
        $pm       = isset($_GET['payment_methods']) ? $_GET['payment_methods'] : null;

        $data['merchant_payments'] = $merchant_payments = $this->merchant_payment->getMerchantPaymentsListForCsvPdf($from, $to, $status, $currency, $pm);
        // dd($deposits);

        $datas = [];
        if (!empty($merchant_payments))
        {
            foreach ($merchant_payments as $key => $value)
            {
                $datas[$key]['Date']     = dateFormat($value->created_at);
                $datas[$key]['Merchant'] = isset($value->merchant) ? $value->merchant->user->first_name . ' ' . $value->merchant->user->last_name : "-";
                $datas[$key]['User']     = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";
                $datas[$key]['Amount']   = formatNumber($value->amount);
                $datas[$key]['Fees']     = ($value->charge_percentage == 0) && ($value->charge_fixed == 0) ? '-' : formatNumber($value->charge_percentage + $value->charge_fixed);
                $datas[$key]['Total']    = '+'.formatNumber($value->amount + ($value->charge_percentage + $value->charge_fixed));
                $datas[$key]['Currency'] = $value->currency->code;

                $datas[$key]['Payment Method'] = ($value->payment_method->name == "Mts") ? "Pay Money" : $value->payment_method->name;

                $datas[$key]['Status'] = ($value->status == 'Blocked') ? 'Cancelled' : $value->status;
            }
        }
        else
        {
            $datas[0]['Date']           = '';
            $datas[0]['Merchant']       = '';
            $datas[0]['User']           = '';
            $datas[0]['Amount']         = '';
            $datas[0]['Fees']           = '';
            $datas[0]['Total']          = '';
            $datas[0]['Currency']       = '';
            $datas[0]['Payment Method'] = '';
            $datas[0]['Status']         = '';
        }
        // dd($datas);

        return Excel::create('merchant_payments_list_' . time() . '', function ($excel) use ($datas)
        {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->sheet('mySheet', function ($sheet) use ($datas)
            {
                $sheet->cells('A1:I1', function ($cells)
                {
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($datas);
            });
        })->download();
    }

    public function merchantPaymentPdf()
    {
        // $data['company_logo'] = Session::get('company_logo');
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;
        $status   = isset($_GET['status']) ? $_GET['status'] : null;
        $pm       = isset($_GET['payment_methods']) ? $_GET['payment_methods'] : null;

        $data['merchant_payments'] = $merchant_payments = $this->merchant_payment->getMerchantPaymentsListForCsvPdf($from, $to, $status, $currency, $pm);

        if (isset($from) && isset($to))
        {
            $data['date_range'] = $from . ' To ' . $to;
        }
        else
        {
            $data['date_range'] = 'N/A';
        }

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A3',
            'orientation' => 'P',
        ]);

        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;

        $mpdf->WriteHTML(view('admin.merchant_payments.merchant_payments_report_pdf', $data));

        $mpdf->Output('merchant_payments_report_' . time() . '.pdf', 'D');
    }

    public function edit($id)
    {
        $data['menu']     = 'merchant';
        $data['sub_menu'] = 'merchant_payments';

        $data['merchant_payment'] = $merchant_payment = MerchantPayment::find($id);
        // dd($merchant_payment);

        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::select('refund_reference')
            ->where(['uuid' => $merchant_payment->uuid])->first();

        if (!empty($transactionOfRefunded))
        {
            $data['merchantPaymentOfRefunded'] = $merchantPaymentOfRefunded = MerchantPayment::where(['uuid' => $transactionOfRefunded->refund_reference])->first(['id']);
        }

        $data['transaction'] = $transaction = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'user_type')
            ->where(['uuid' => $merchant_payment->uuid, 'status' => $merchant_payment->status])
            ->whereIn('transaction_type_id', [Payment_Sent, Payment_Received])
            ->first();
        // dd($transaction->transaction_type->name);

        // dd($transaction);
        return view('admin.merchant_payments.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());

        $userInfo = User::where(['id' => trim($request->paid_by_user_id)])->first();

        //if other language's subject and body not set, get en sub and body for mail
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 14, 'lang' => 'en'])->select('subject', 'body')->first();

        $merchant_status_mail_info = EmailTemplate::where([
            'temp_id'     => 14,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * SMS
         */
        $merchant_status_en_sms_info = EmailTemplate::where(['temp_id' => 14, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $merchant_status_sms_info    = EmailTemplate::where(['temp_id' => 14, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        if ($request->transaction_type == 'Payment_Sent')
        {
            // dd('Payment_Sent');
            if ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Pending!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'end_user_id'              => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount,
                    ]);

                    //Mail and Sms
                    if (isset($merchant_payment->merchant))
                    {
                        //mail
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'subtracted', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'from', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->amount), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'subtracted', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'from', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success')
                {
                    $this->helper->one_time_message('success', 'Transfer is already Successfull!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Success');
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->amount,
                    ]);

                    //Sender(user_id)
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }
                    }

                    //sms
                    if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                    {
                        if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                        {
                            $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                            $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                        }
                        else
                        {
                            $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                            $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                        }
                        $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->amount), $merchant_status_sms_info_msg);
                        $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'added', $merchant_status_sms_info_msg);
                        $merchant_status_sms_info_msg = str_replace('{from/to}', 'to', $merchant_status_sms_info_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($request->transaction_status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transfer is already Refund!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Success') //done
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = $request->paid_by_user_id;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    // $merchant_payment->charge_fixed      = 0;
                    $merchant_payment->charge_fixed      = $request->charge_fixed;
                    $merchant_payment->amount            = $request->amount;
                    // $merchant_payment->total             = '-' . ($request->charge_percentage + $request->amount);
                    $merchant_payment->total             = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //Payment_Sent old entry update
                    Transaction::where([
                        'user_id'                  => $request->paid_by_user_id,
                        'end_user_id'              => $merchant_payment->merchant->user->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => $request->paid_by_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Sent entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->paid_by_user_id;
                    $refund_t_A->end_user_id              = $merchant_payment->merchant->user->id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->mp_uuid;
                    $refund_t_A->transaction_reference_id = $merchant_payment->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Payment_Sent
                    $refund_t_A->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->subtotal                 = $request->charge_percentage + $request->charge_fixed + $request->amount;
                    $refund_t_A->charge_percentage        = 0;
                    $refund_t_A->charge_fixed             = 0;
                    $refund_t_A->total                    = $request->charge_percentage + $request->charge_fixed + $request->amount;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $merchant_payment->merchant->user->id;
                    $refund_t_B->end_user_id              = $request->paid_by_user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->mp_uuid;
                    $refund_t_B->transaction_reference_id = $merchant_payment->id;
                    $refund_t_B->transaction_type_id      = Payment_Received; //Payment_Received
                    $refund_t_B->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_B->percentage               = $request->percentage;
                    $refund_t_B->subtotal                 = $request->amount;
                    $refund_t_B->charge_percentage        = $request->charge_percentage;
                    $refund_t_B->charge_fixed             = $request->charge_fixed;
                    $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //add amount from paid_by_user wallet, if user exists
                    if (isset($merchant_payment->user_id))
                    {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance', 'user_id')->first();

                        Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + ($request->charge_percentage + $request->charge_fixed + $request->amount),
                        ]);
                    }

                    //Sender(user_id) //paid_by_user
                    if (isset($merchant_payment->user_id))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->charge_percentage + $request->charge_fixed + $request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($paid_by_user->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($paid_by_user->user->carrierCode) && !empty($paid_by_user->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, ($request->charge_percentage + $request->charge_fixed + $request->amount)), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'added', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'to', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $paid_by_user->user->carrierCode . $paid_by_user->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }

                    //deduct amount to merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount,
                    ]);

                    //Receiver(end_user_id) //merchant_user_wallet
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'subtracted', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'from', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->amount), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'subtracted', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'from', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
        }
        elseif ($request->transaction_type == 'Payment_Received')
        {
            // dd('Payment_Received');
            if ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Pending!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - ($request->amount),
                    ]);

                    //Sending mail & sms to Merchant
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'subtracted', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'from', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, ($request->amount)), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'subtracted', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'from', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success')
                {
                    $this->helper->one_time_message('success', 'Transfer is already Successfull!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Success');
                    $merchant_payment         = MerchantPayment::find($request->id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->amount,
                    ]);

                    //Sending mail & sms to Merchant
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->amount), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'added', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'to', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($request->transaction_status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transfer is already Refund!');
                    return redirect('admin/merchant_payments');
                }
                elseif ($request->transaction_status == 'Success') //done
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code                         = unique_code();
                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = isset($userInfo) ? $userInfo->id : null;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    // $merchant_payment->charge_fixed      = 0;
                    $merchant_payment->charge_fixed      = $request->charge_fixed;
                    $merchant_payment->amount            = $request->amount;
                    // $merchant_payment->total             = '-' . ($request->charge_percentage + $request->amount);
                    $merchant_payment->total             = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $merchant_payment->merchant->user->id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $merchant_payment->merchant->user->id;
                    $refund_t_B->end_user_id              = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->mp_uuid;
                    $refund_t_B->transaction_reference_id = $merchant_payment->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Payment_Received
                    $refund_t_B->user_type                = $request->user_type;
                    $refund_t_B->subtotal                 = $request->amount;
                    $refund_t_B->percentage               = $request->percentage;
                    $refund_t_B->charge_percentage        = $request->charge_percentage;
                    $refund_t_B->charge_fixed             = $request->charge_fixed;
                    $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->amount);
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //add amount from paid_by_user wallet
                    if (isset($merchant_payment->user_id))
                    {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance', 'user_id')->first();

                        Wallet::where([
                            'user_id'     => $request->paid_by_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + ($request->amount + $request->charge_percentage + $request->charge_fixed), //adding total amount to paid by user
                        ]);
                    }

                    //Sender(user_id) //paid_by_user
                    if (isset($merchant_payment->user_id))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount + $request->charge_percentage + $request->charge_fixed)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($paid_by_user->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($paid_by_user->user->carrierCode) && !empty($paid_by_user->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $paid_by_user->user->first_name . ' ' . $paid_by_user->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, ($request->amount + $request->charge_percentage + $request->charge_fixed)), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'added', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'to', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $paid_by_user->user->carrierCode . $paid_by_user->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }

                    //deduct amount to merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->amount, //subtracting only amount from merchant wallet
                    ]);

                    //Receiver(end_user_id) //merchant_user_wallet
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishSenderLanginfo->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishSenderLanginfo->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', $merchant_payment->status, $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->amount)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'subtracted', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'from', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->merchant->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->merchant->user->carrierCode) && !empty($merchant_payment->merchant->user->phone))
                        {
                            if (!empty($merchant_status_sms_info->subject) && !empty($merchant_status_sms_info->body))
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_sms_info->body);
                            }
                            else
                            {
                                $merchant_status_sms_info_sub = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_en_sms_info->subject);
                                $merchant_status_sms_info_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_en_sms_info->body);
                            }
                            $merchant_status_sms_info_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->amount), $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{added/subtracted}', 'subtracted', $merchant_status_sms_info_msg);
                            $merchant_status_sms_info_msg = str_replace('{from/to}', 'from', $merchant_status_sms_info_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $merchant_status_sms_info_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/merchant_payments');
                }
            }
        }
    }
}
