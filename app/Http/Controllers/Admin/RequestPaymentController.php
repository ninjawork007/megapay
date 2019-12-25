<?php

namespace App\Http\Controllers\Admin;

use App;
use App\DataTables\Admin\RequestPaymentsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\EmailTemplate;
use App\Models\RequestPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class RequestPaymentController extends Controller
{
    protected $helper;
    protected $email;
    protected $requestpayment;

    public function __construct()
    {
        $this->helper         = new Common();
        $this->email          = new EmailController();
        $this->requestpayment = new RequestPayment();
    }

    public function index(RequestPaymentsDataTable $dataTable)
    {
        $data['menu'] = 'request_payments';

        $data['requestpayments_status']     = $requestpayments_status     = $this->requestpayment->select('status')->groupBy('status')->get();
        $data['requestpayments_currencies'] = $requestpayments_currencies = $this->requestpayment->select('currency_id')->groupBy('currency_id')->get();

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['user']     = $user     = $_GET['user_id'];

            $data['getName'] = $getName = $this->requestpayment->getRequestPaymentsUserName($user);
            // dd($getName);

            if (empty($_GET['from']))
            {
                // dd('empty');
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
            $data['from']     = null;
            $data['to']       = null;
            $data['status']   = 'all';
            $data['currency'] = 'all';
            $data['user']     = null;
        }
        return $dataTable->render('admin.RequestPayment.list', $data);
    }

    public function requestpaymentCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['requestpayments'] = $requestpayments = $this->requestpayment->getRequestPaymentsListForCsvPdf($from, $to, $status, $currency, $user);

        $datas = [];
        if (!empty($requestpayments))
        {
            foreach ($requestpayments as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                $datas[$key]['User'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";

                $datas[$key]['Requested Amount'] = '+'.formatNumber($value->amount);

                $datas[$key]['Accepted Amount'] = ($value->accept_amount == 0) ? "-" : '+'.formatNumber($value->accept_amount);

                $datas[$key]['Currency'] = $value->currency->code;

                if ($value->receiver)
                {
                    $datas[$key]['Receiver'] = $value->receiver->first_name . ' ' . $value->receiver->last_name;
                }
                elseif ($value->email)
                {
                    $datas[$key]['Receiver'] = $value->email;
                }
                elseif ($value->phone)
                {
                    $datas[$key]['Receiver'] = $value->phone;
                }
                else
                {
                    $datas[$key]['Receiver'] = '-';
                }

                $datas[$key]['Status'] = (($value->status == 'Blocked') ? "Cancelled" : (($value->status == 'Refund') ? "Refunded" : $value->status));
            }
        }
        else
        {
            $datas[0]['Date']             = '';
            $datas[0]['User']             = '';
            $datas[0]['Requested Amount'] = '';
            $datas[0]['Accepted Amount']  = '';
            $datas[0]['Currency']         = '';
            $datas[0]['Receiver']         = '';
            $datas[0]['Status']           = '';
        }
        // dd($datas);

        return Excel::create('requestpayments_list_' . time() . '', function ($excel) use ($datas)
        {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->sheet('mySheet', function ($sheet) use ($datas)
            {
                $sheet->cells('A1:G1', function ($cells)
                {
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($datas);
            });
        })->download();
    }

    public function requestpaymentPdf()
    {
        // $data['company_logo'] = \Session::get('company_logo');
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['requestpayments'] = $requestpayments = $this->requestpayment->getRequestPaymentsListForCsvPdf($from, $to, $status, $currency, $user);

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

        $mpdf->WriteHTML(view('admin.RequestPayment.requestpayments_report_pdf', $data));

        $mpdf->Output('requestpayments_report_' . time() . '.pdf', 'D');
    }

    public function requestpaymentsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->requestpayment->getRequestPaymentsUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function edit($id)
    {
        $data['menu'] = 'request_payments';

        $data['request_payments'] = $request_payments = RequestPayment::find($id);
        // dd($request_payments);

        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::select('refund_reference')->where(['uuid' => $request_payments->uuid])->first();
        // dd($transactionOfRefunded);

        $data['requestPaymentsOfRefunded'] = $requestPaymentsOfRefunded = RequestPayment::where(['uuid' => $transactionOfRefunded->refund_reference])->first(['id']);
        // dd($transactionOfRefunded);

        //fetching (Request From) entry from transactions table
        // if ($request_payments->status == 'Success') {
        $data['transaction'] = $transaction = Transaction::select('transaction_type_id', 'status', 'percentage', 'charge_percentage', 'charge_fixed', 'transaction_reference_id', 'user_type')
            ->where([
                'transaction_reference_id' => $request_payments->id,
                'status'                   => $request_payments->status,
                'transaction_type_id'      => Request_To,
            ])
            ->first();
        // }

        // dd($transaction->percentage);

        // dd($transaction->transaction_type->name);

        return view('admin.RequestPayment.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());

        $userInfo = User::where(['email' => trim($request->request_payments_email)])->first();

        $englishSenderLanginfoForRPSucRef = EmailTemplate::where(['temp_id' => 8, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();//if other language's subject and body not set, get en sub and body for mail

        $englishSenderLanginfoForRPCancelPending = EmailTemplate::where(['temp_id' => 16, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

        /**
         * SMS
         */
        $rp_status_en_sms_info_suc_ref = EmailTemplate::where(['temp_id' => 8, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $rp_status_sms_info_suc_ref = EmailTemplate::where(['temp_id' => 8, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        $rp_status_en_sms_info_canc_pend = EmailTemplate::where(['temp_id' => 16, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $rp_status_sms_info_canc_pend = EmailTemplate::where(['temp_id' => 16, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();


        //Updating both Request_From and Request_To entries by using one type
        if ($request->transaction_type == 'Request_To')
        {
            if ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Request Payment is already Successfull!');
                    return redirect('admin/request_payments');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($request->transaction_status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Request Payment is already Refund!');
                    return redirect('admin/request_payments');
                }
                elseif ($request->transaction_status == 'Success') //done
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    $requestpayment = new RequestPayment();

                    $requestpayment->user_id = $request->user_id;

                    $requestpayment->receiver_id = isset($userInfo) ? $userInfo->id : null;

                    $requestpayment->currency_id = $request->currency_id;

                    $requestpayment->uuid = $unique_code;

                    $requestpayment->amount = $request->amount;

                    $requestpayment->accept_amount = $request->accept_amount;

                    $requestpayment->email = $request->request_payments_email;

                    $requestpayment->note = $request->note;

                    $requestpayment->status = $request->status;
                    // dd($requestpayment);
                    $requestpayment->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_From,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_From entry
                    $refund_t_A = new Transaction();

                    $refund_t_A->user_id     = $request->user_id;
                    $refund_t_A->end_user_id = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_A->currency_id = $request->currency_id;
                    $refund_t_A->uuid = $unique_code;
                    $refund_t_A->refund_reference = $request->uuid;
                    $refund_t_A->transaction_reference_id = $requestpayment->id;
                    $refund_t_A->transaction_type_id      = Request_From; //Request_From
                    $refund_t_A->user_type = $request->user_type;
                    $refund_t_A->subtotal = $request->accept_amount;
                    $refund_t_A->percentage = 0;
                    $refund_t_A->charge_percentage = 0;
                    $refund_t_A->charge_fixed = 0;
                    $refund_t_A->total = '-' . $refund_t_A->subtotal;
                    $refund_t_A->note   = $request->note;
                    $refund_t_A->status = $request->status;
                    // dd($refund_t_A);
                    $refund_t_A->save();

                    //New Request_To entry
                    $refund_t_B = new Transaction();

                    $refund_t_B->user_id     = isset($userInfo) ? $userInfo->id : null;
                    $refund_t_B->end_user_id = $request->user_id;

                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $requestpayment->id;

                    $refund_t_B->transaction_type_id = $request->transaction_type_id; //Request_To

                    $refund_t_B->user_type = $request->user_type;
                    // $refund_t_B->email               = $request->request_payments_email;

                    $refund_t_B->subtotal = $request->accept_amount;

                    $refund_t_B->percentage        = $request->percentage;
                    $refund_t_B->charge_percentage = $request->charge_percentage;
                    $refund_t_B->charge_fixed      = $request->charge_fixed;

                    $refund_t_B->total = ($request->charge_percentage + $request->charge_fixed + $refund_t_B->subtotal);

                    $refund_t_B->note = $request->note;

                    $refund_t_B->status = $request->status;

                    // dd($refund_t_B);
                    $refund_t_B->save();

                    //sender wallet entry update
                    $request_created_wallet = Wallet::where([
                        'user_id'     => $requestpayment->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $requestpayment->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $request_created_wallet->balance - $request->accept_amount,
                    ]);

                    if (isset($userInfo))
                    {
                        //receiver wallet entry update
                        $request_accepted_wallet = Wallet::where([
                            'user_id'     => isset($userInfo) ? $userInfo->id : null,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => isset($userInfo) ? $userInfo->id : null,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $request_accepted_wallet->balance + ($request->accept_amount + $request->charge_percentage + $request->charge_fixed),
                        ]);
                    }

                    // Sent Mail when request is 'refunded'
                    $t_ref_mail_info = EmailTemplate::where([
                        'temp_id'     => 8,
                        'language_id' => Session::get('default_language'),
                        'type' => 'email',
                    ])->select('subject', 'body')->first();

                    // Creator Mail
                    if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $t_ref_mail_info->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_ref_mail_info->body);
                    }
                    else
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $englishSenderLanginfoForRPSucRef->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishSenderLanginfoForRPSucRef->body);
                    }
                    $t_ref_msg_1 = str_replace('{uuid}', $requestpayment->uuid, $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? "Cancelled" : (($requestpayment->status == 'Refund') ? "Refunded" : $requestpayment->status), $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->accept_amount)), $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{added/subtracted}', 'subtracted', $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{from/to}', 'from', $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_ref_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($requestpayment->user->email, $t_ref_sub_1, $t_ref_msg_1);
                    }

                    //sms
                    if (!empty($requestpayment->user->carrierCode) && !empty($requestpayment->user->phone))
                    {
                        if (!empty($rp_status_sms_info_suc_ref->subject) && !empty($rp_status_sms_info_suc_ref->body))
                        {
                            $rp_status_sms_info_suc_ref_sub = str_replace('{uuid}', $requestpayment->uuid, $rp_status_sms_info_suc_ref->subject);
                            $rp_status_sms_info_suc_ref_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $rp_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $rp_status_sms_info_suc_ref_sub = str_replace('{uuid}', $requestpayment->uuid, $rp_status_en_sms_info_suc_ref->subject);
                            $rp_status_sms_info_suc_ref_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $rp_status_en_sms_info_suc_ref->body);
                        }
                        $rp_status_sms_info_suc_ref_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? "Cancelled" : (($requestpayment->status == 'Refund') ? "Refunded" : $requestpayment->status),
                            $rp_status_sms_info_suc_ref_msg);
                        $rp_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->accept_amount)), $rp_status_sms_info_suc_ref_msg);
                        $rp_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'subtracted', $rp_status_sms_info_suc_ref_msg);
                        $rp_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'from', $rp_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $rp_status_sms_info_suc_ref_msg);
                            }
                        }
                    }

                    if (isset($userInfo))
                    {
                        // Acceptor Mail
                        if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $t_ref_mail_info->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_ref_mail_info->body);
                        }
                        else
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $englishSenderLanginfoForRPSucRef->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishSenderLanginfoForRPSucRef->body);
                        }
                        $t_ref_msg_2 = str_replace('{uuid}', $requestpayment->uuid, $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? "Cancelled" : (($requestpayment->status == 'Refund') ? "Refunded" : $requestpayment->status), $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->accept_amount + $request->charge_percentage + $request->charge_fixed)), $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{added/subtracted}', 'added', $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{from/to}', 'to', $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_ref_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($requestpayment->receiver->email, $t_ref_sub_2, $t_ref_msg_2);
                        }

                        //sms
                        if (!empty($requestpayment->receiver->carrierCode) && !empty($requestpayment->receiver->phone))
                        {
                            if (!empty($rp_status_sms_info_suc_ref->subject) && !empty($rp_status_sms_info_suc_ref->body))
                            {
                                $rp_status_sms_info_suc_ref_sub = str_replace('{uuid}', $requestpayment->uuid, $rp_status_sms_info_suc_ref->subject);
                                $rp_status_sms_info_suc_ref_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $rp_status_sms_info_suc_ref->body);
                            }
                            else
                            {
                                $rp_status_sms_info_suc_ref_sub = str_replace('{uuid}', $requestpayment->uuid, $rp_status_en_sms_info_suc_ref->subject);
                                $rp_status_sms_info_suc_ref_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $rp_status_en_sms_info_suc_ref->body);
                            }
                            $rp_status_sms_info_suc_ref_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? "Cancelled" : (($requestpayment->status == 'Refund') ? "Refunded" : $requestpayment->status),
                                $rp_status_sms_info_suc_ref_msg);
                            $rp_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->accept_amount + $request->charge_percentage + $request->charge_fixed)), $rp_status_sms_info_suc_ref_msg);
                            $rp_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added', $rp_status_sms_info_suc_ref_msg);
                            $rp_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'to', $rp_status_sms_info_suc_ref_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $rp_status_sms_info_suc_ref_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Request Payment Updated Successfully!');
                    return redirect('admin/request_payments');
                }
            }
            elseif ($request->status == 'Blocked') //when current status is 'cancelled'
            {
                // dd('Request_Created => Request: Blocked, DB Status: Pendinggggggggggggggggggg');
                $request_created         = RequestPayment::find($request->id);
                $request_created->status = $request->status;
                $request_created->save();

                //Request From entry update
                Transaction::where([
                    'user_id'                  => $request->user_id,
                    'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_From,
                ])->update([
                    'status' => $request->status,
                ]);

                //Request To entry update
                Transaction::where([
                    'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                    'end_user_id'              => $request->user_id,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_To,
                ])->update([
                    'status' => $request->status,
                ]);

                // Sent Mail when request is 'blocked'
                $t_block_temp = EmailTemplate::where([
                    'temp_id'     => 16,
                    'language_id' => Session::get('default_language'),
                    'type' => 'email',
                ])->select('subject', 'body')->first();

                // Creator Mail
                if (!empty($t_block_temp->subject) && !empty($t_block_temp->body))
                {
                    //Subject
                    $t_block_sub_1 = str_replace('{uuid}', $request_created->uuid, $t_block_temp->subject);
                    //Body
                    $t_block_msg_1 = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $t_block_temp->body);
                }
                else
                {
                    //Subject
                    $t_block_sub_1 = str_replace('{uuid}', $request_created->uuid, $englishSenderLanginfoForRPCancelPending->subject);
                    //Body
                    $t_block_msg_1 = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $englishSenderLanginfoForRPCancelPending->body);
                }
                $t_block_msg_1 = str_replace('{uuid}', $request_created->uuid, $t_block_msg_1);
                $t_block_msg_1 = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status), $t_block_msg_1);
                $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                if (checkAppMailEnvironment())
                {
                    $this->email->sendEmail($request_created->user->email, $t_block_sub_1, $t_block_msg_1);
                }

                //sms
                if (!empty($request_created->user->carrierCode) && !empty($request_created->user->phone))
                {
                    if (!empty($rp_status_sms_info_canc_pend->subject) && !empty($rp_status_sms_info_canc_pend->body))
                    {
                        $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_sms_info_canc_pend->subject);
                        $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $rp_status_sms_info_canc_pend->body);
                    }
                    else
                    {
                        $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_en_sms_info_canc_pend->subject);
                        $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $rp_status_en_sms_info_canc_pend->body);
                    }
                    $rp_status_sms_info_canc_pend_msg = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status),
                        $rp_status_sms_info_canc_pend_msg);

                    if (checkAppSmsEnvironment())
                    {
                        if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                        {
                            sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_created->user->carrierCode . $request_created->user->phone, $rp_status_sms_info_canc_pend_msg);
                        }
                    }
                }

                if (isset($userInfo))
                {
                    // Receiver Mail
                    if (!empty($t_block_temp->subject) && !empty($t_block_temp->body))
                    {
                        //Subject
                        $t_block_sub_2 = str_replace('{uuid}', $request_created->uuid, $t_block_temp->subject);
                        //Body
                        $t_block_msg_2 = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $t_block_temp->body);
                    }
                    else
                    {
                        //Subject
                        $t_block_sub_2 = str_replace('{uuid}', $request_created->uuid, $englishSenderLanginfoForRPCancelPending->subject);
                        //Body
                        $t_block_msg_2 = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $englishSenderLanginfoForRPCancelPending->body);
                    }
                    $t_block_msg_2 = str_replace('{uuid}', $request_created->uuid, $t_block_msg_2);

                    //FIXED IN PM 2.1
                    $t_block_msg_2 = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status), $t_block_msg_2);
                    $t_block_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_2);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($request_created->receiver->email, $t_block_sub_2, $t_block_msg_2);
                    }

                    //sms
                    if (!empty($request_created->receiver->carrierCode) && !empty($request_created->receiver->phone))
                    {
                        if (!empty($rp_status_sms_info_canc_pend->subject) && !empty($rp_status_sms_info_canc_pend->body))
                        {
                            $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_sms_info_canc_pend->subject);
                            $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $rp_status_sms_info_canc_pend->body);
                        }
                        else
                        {
                            $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_en_sms_info_canc_pend->subject);
                            $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $rp_status_en_sms_info_canc_pend->body);
                        }
                        $rp_status_sms_info_canc_pend_msg = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status),
                            $rp_status_sms_info_canc_pend_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_created->receiver->carrierCode . $request_created->receiver->phone, $rp_status_sms_info_canc_pend_msg);
                            }
                        }
                    }
                }
                $this->helper->one_time_message('success', 'Request Payment Updated Successfully!');
                return redirect('admin/request_payments');
            }
            elseif ($request->status == 'Pending') //when current status is 'pending'
            {
                // dd('Request_Created => Request: Pending, DB Status: Blockedyyyyyyyyyyyyyyyyyyyyyyyyyyyy');
                $request_created         = RequestPayment::find($request->id);
                $request_created->status = $request->status;
                $request_created->save();

                //Request From entry update
                Transaction::where([
                    'user_id'                  => $request->user_id,
                    'end_user_id'              => isset($userInfo) ? $userInfo->id : null,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_From,
                ])->update([
                    'status' => $request->status,
                ]);

                //Request To entry update
                Transaction::where([
                    'user_id'                  => isset($userInfo) ? $userInfo->id : null,
                    'end_user_id'              => $request->user_id,
                    'transaction_reference_id' => $request_created->id,
                    'transaction_type_id'      => Request_To,
                ])->update([
                    'status' => $request->status,
                ]);

                // Sent Mail when request is 'Pending'
                $t_pending_temp_temp = EmailTemplate::where([
                    'temp_id'     => 16,
                    'language_id' => Session::get('default_language'),
                    'type' => 'email',
                ])->select('subject', 'body')->first();

                // Receiver Mail
                if (!empty($t_pending_temp_temp->subject) && !empty($t_pending_temp_temp->body))
                {
                    //Subject
                    $t_pending_sub_1 = str_replace('{uuid}', $request_created->uuid, $t_pending_temp_temp->subject);
                    //Body
                    $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $t_pending_temp_temp->body);
                }
                else
                {
                    //Subject
                    $t_pending_sub_1 = str_replace('{uuid}', $request_created->uuid, $englishSenderLanginfoForRPCancelPending->subject);
                    //Body
                    $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $englishSenderLanginfoForRPCancelPending->body);
                }
                $t_pending_msg_1 = str_replace('{uuid}', $request_created->uuid, $t_pending_msg_1);

                //FIXED IN PM 2.1
                $t_pending_msg_1 = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status), $t_pending_msg_1);
                $t_pending_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_1);

                if (checkAppMailEnvironment())
                {
                    $this->email->sendEmail($request_created->user->email, $t_pending_sub_1, $t_pending_msg_1);
                }

                //sms
                if (!empty($request_created->user->carrierCode) && !empty($request_created->user->phone))
                {
                    if (!empty($rp_status_sms_info_canc_pend->subject) && !empty($rp_status_sms_info_canc_pend->body))
                    {
                        $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_sms_info_canc_pend->subject);
                        $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $rp_status_sms_info_canc_pend->body);
                    }
                    else
                    {
                        $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_en_sms_info_canc_pend->subject);
                        $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->user->first_name . ' ' . $request_created->user->last_name, $rp_status_en_sms_info_canc_pend->body);
                    }
                    $rp_status_sms_info_canc_pend_msg = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status),
                        $rp_status_sms_info_canc_pend_msg);

                    if (checkAppSmsEnvironment())
                    {
                        if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                        {
                            sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_created->user->carrierCode . $request_created->user->phone, $rp_status_sms_info_canc_pend_msg);
                        }
                    }
                }

                // if (isset($request_created->receiver))
                if (isset($userInfo))
                {
                    // Receiver Mail
                    if (!empty($t_pending_temp_temp->subject) && !empty($t_pending_temp_temp->body))
                    {
                        //Subject
                        $t_pending_sub_2 = str_replace('{uuid}', $request_created->uuid, $t_pending_temp_temp->subject);
                        //Body
                        $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $t_pending_temp_temp->body);
                    }
                    else
                    {
                        //Subject
                        $t_pending_sub_2 = str_replace('{uuid}', $request_created->uuid, $englishSenderLanginfoForRPCancelPending->subject);
                        //Body
                        $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $englishSenderLanginfoForRPCancelPending->body);
                    }
                    $t_pending_msg_2 = str_replace('{uuid}', $request_created->uuid, $t_pending_msg_2);
                    $t_pending_msg_2 = str_replace('{status}', ($request_created->status == 'Blocked') ? 'Cancelled' : $request_created->status, $t_pending_msg_2);
                    $t_pending_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_2);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($request_created->receiver->email, $t_pending_sub_2, $t_pending_msg_2);
                    }

                    //sms
                    if (!empty($request_created->receiver->carrierCode) && !empty($request_created->receiver->phone))
                    {
                        if (!empty($rp_status_sms_info_canc_pend->subject) && !empty($rp_status_sms_info_canc_pend->body))
                        {
                            $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_sms_info_canc_pend->subject);
                            $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $rp_status_sms_info_canc_pend->body);
                        }
                        else
                        {
                            $rp_status_sms_info_canc_pend_sub = str_replace('{uuid}', $request_created->uuid, $rp_status_en_sms_info_canc_pend->subject);
                            $rp_status_sms_info_canc_pend_msg = str_replace('{user_id/receiver_id}', $request_created->receiver->first_name . ' ' . $request_created->receiver->last_name, $rp_status_en_sms_info_canc_pend->body);
                        }
                        $rp_status_sms_info_canc_pend_msg = str_replace('{status}', ($request_created->status == 'Blocked') ? "Cancelled" : (($request_created->status == 'Refund') ? "Refunded" : $request_created->status),
                            $rp_status_sms_info_canc_pend_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_created->receiver->carrierCode . $request_created->receiver->phone, $rp_status_sms_info_canc_pend_msg);
                            }
                        }
                    }
                }
                $this->helper->one_time_message('success', 'Request Payment Updated Successfully!');
                return redirect('admin/request_payments');
            }
        }
    }
}
