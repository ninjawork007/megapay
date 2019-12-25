<?php

namespace App\Http\Controllers\Admin;

use App;
use App\DataTables\Admin\TransactionsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\Deposit;
use App\Models\Dispute;
use App\Models\EmailTemplate;
use App\Models\MerchantPayment;
use App\Models\PaymentMethod;
use App\Models\RequestPayment;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class TransactionController extends Controller
{
    protected $helper;
    protected $email;
    protected $transaction;

    public function __construct()
    {
        $this->helper      = new Common();
        $this->email       = new EmailController();
        $this->transaction = new Transaction();
    }

    public function index(TransactionsDataTable $dataTable)
    {
        $data['menu'] = 'transactions';

        $data['t_status']   = $t_status   = Transaction::select('status')->groupBy('status')->get();
        $data['t_currency'] = $t_currency = Transaction::with('currency:id,code')->select('currency_id')->groupBy('currency_id')->get();

        $data['t_type'] = $t_type = Transaction::with('transaction_type:id,name')->select('transaction_type_id')
        ->whereNotIn('transaction_type_id', [Voucher_Created, Voucher_Activated])->groupBy('transaction_type_id')->get();

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['type']     = $_GET['type'];
            $data['user']     = $user     = $_GET['user_id'];

            $data['getName'] = $getName = $this->transaction->getTransactionsUsersName($user);

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
            $data['from']     = null;
            $data['to']       = null;
            $data['status']   = 'all';
            $data['currency'] = 'all';
            $data['type']     = 'all';
            $data['user']     = null;
        }
        return $dataTable->render('admin.transactions.index', $data);
    }

    public function transactionCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        // dd($from);

        $to = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;
        // dd($to);

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $type = isset($_GET['type']) ? $_GET['type'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['transaction'] = $transaction = $this->transaction->getTransactionsListForCsvPDF($from, $to, $status, $currency, $type, $user);
        // dd($transaction);

        $datas = [];
        if (!empty($transaction))
        {
            foreach ($transaction as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                // User
                if ($value->transaction_type_id == Deposit)
                {
                    $datas[$key]['User'] = isset($value->deposit->user) ? $value->deposit->user->first_name . ' ' . $value->deposit->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Transferred)
                {
                    $datas[$key]['User'] = isset($value->transfer->sender) ? $value->transfer->sender->first_name . ' ' . $value->transfer->sender->last_name : "-";
                }
                elseif ($value->transaction_type_id == Bank_Transfer)
                {
                    $datas[$key]['User'] = isset($value->transfer->sender) ? $value->transfer->sender->first_name . ' ' . $value->transfer->sender->last_name : "-";
                }
                elseif ($value->transaction_type_id == Received)
                {
                    $datas[$key]['User'] = isset($value->transfer->sender) ? $value->transfer->sender->first_name . ' ' . $value->transfer->sender->last_name : "-";
                }
                elseif ($value->transaction_type_id == Exchange_From)
                {
                    $datas[$key]['User'] = isset($value->currency_exchange->user) ? $value->currency_exchange->user->first_name . ' ' . $value->currency_exchange->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Exchange_To)
                {
                    $datas[$key]['User'] = isset($value->currency_exchange->user) ? $value->currency_exchange->user->first_name . ' ' . $value->currency_exchange->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Voucher_Created)
                {
                    $datas[$key]['User'] = isset($value->voucher->user) ? $value->voucher->user->first_name . ' ' . $value->voucher->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Voucher_Activated)
                {
                    $datas[$key]['User'] = isset($value->voucher->user) ? $value->voucher->user->first_name . ' ' . $value->voucher->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Request_From)
                {
                    $datas[$key]['User'] = isset($value->request_payment->user) ? $value->request_payment->user->first_name . ' ' . $value->request_payment->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Request_To)
                {
                    $datas[$key]['User'] = isset($value->request_payment->user) ? $value->request_payment->user->first_name . ' ' . $value->request_payment->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Withdrawal)
                {
                    $datas[$key]['User'] = isset($value->withdrawal->user) ? $value->withdrawal->user->first_name . ' ' . $value->withdrawal->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Payment_Sent)
                {
                    $datas[$key]['User'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Payment_Received)
                {
                    $datas[$key]['User'] = isset($value->end_user) ? $value->end_user->first_name . ' ' . $value->end_user->last_name : "-";
                }

                $datas[$key]['Type'] = ($value->transaction_type->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $value->transaction_type->name);

                $datas[$key]['Amount'] = formatNumber($value->subtotal);

                $datas[$key]['Fees'] = ($value->charge_percentage == 0) && ($value->charge_fixed == 0) ? '-' : formatNumber($value->charge_percentage + $value->charge_fixed);

                $datas[$key]['Total'] = formatNumber($value->total);

                $datas[$key]['Currency'] = $value->currency->code;

                //Receiver
                if ($value->transaction_type_id == Deposit)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Transferred)
                {
                    if ($value->transfer->receiver)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->receiver->first_name . ' ' . $value->transfer->receiver->last_name;
                    }
                    elseif ($value->transfer->email)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->email;
                    }
                    elseif ($value->transfer->phone)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->phone;
                    }
                    else
                    {
                        $datas[$key]['Receiver'] = '-';
                    }
                }
                elseif ($value->transaction_type_id == Bank_Transfer)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Received)
                {
                    if ($value->transfer->receiver)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->receiver->first_name . ' ' . $value->transfer->receiver->last_name;
                    }
                    elseif ($value->transfer->email)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->email;
                    }
                    elseif ($value->transfer->phone)
                    {
                        $datas[$key]['Receiver'] = $value->transfer->phone;
                    }
                    else
                    {
                        $datas[$key]['Receiver'] = '-';
                    }
                }
                elseif ($value->transaction_type_id == Exchange_From)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Exchange_To)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Voucher_Created)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Voucher_Activated)
                {
                    $datas[$key]['Receiver'] = isset($value->voucher->activator) ? $value->voucher->activator->first_name . ' ' . $value->voucher->activator->last_name : "-";
                }
                elseif ($value->transaction_type_id == Request_From)
                {
                    $datas[$key]['Receiver'] = isset($value->request_payment->receiver) ? $value->request_payment->receiver->first_name . ' ' . $value->request_payment->receiver->last_name : $value->request_payment->email;
                }
                elseif ($value->transaction_type_id == Request_To)
                {
                    $datas[$key]['Receiver'] = isset($value->request_payment->receiver) ? $value->request_payment->receiver->first_name . ' ' . $value->request_payment->receiver->last_name : $value->request_payment->email;
                }
                elseif ($value->transaction_type_id == Withdrawal)
                {
                    $datas[$key]['Receiver'] = "-";
                }
                elseif ($value->transaction_type_id == Payment_Sent)
                {
                    $datas[$key]['Receiver'] = isset($value->end_user) ? $value->end_user->first_name . ' ' . $value->end_user->last_name : "-";
                }
                elseif ($value->transaction_type_id == Payment_Received)
                {
                    $datas[$key]['Receiver'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";
                }
                $datas[$key]['Status'] = (($value->status == 'Blocked') ? "Cancelled" : (($value->status == 'Refund') ? "Refunded" : $value->status));
            }
        }
        else
        {
            $datas[0]['Date']     = '';
            $datas[0]['User']     = '';
            $datas[0]['Type']     = '';
            $datas[0]['Amount']   = '';
            $datas[0]['Fees']     = '';
            $datas[0]['Total']    = '';
            $datas[0]['Currency'] = '';
            $datas[0]['Receiver'] = '';
            $datas[0]['Status']   = '';
        }
        // dd($datas);

        return Excel::create('transaction_list_' . time() . '', function ($excel) use ($datas)
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

    public function transactionPdf()
    {
        // $data['company_logo'] = Session::get('company_logo');
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        // dd($from);

        $to = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;
        // dd($to);

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $type = isset($_GET['type']) ? $_GET['type'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['transactions'] = $transactions = $this->transaction->getTransactionsListForCsvPDF($from, $to, $status, $currency, $type, $user);
        // dd($transactions);

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

        $mpdf->WriteHTML(view('admin.transactions.transactions_report_pdf', $data));

        $mpdf->Output('transactions_report_' . time() . '.pdf', 'D');
    }

    /**
     * @param Request $request
     */
    public function transactionsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->transaction->getTransactionsUsersResponse($search);

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
        $data['menu']         = 'transactions';
        $data['transactions'] = $transactions = Transaction::find($id);
        // dd($transactions->phone);

        $data['transactionOfRefunded'] = $transactionOfRefunded = Transaction::where(['uuid' => $transactions->refund_reference, 'transaction_type_id' => $transactions->transaction_type_id])->first(['id']);
        // dd($transactionOfRefunded);

        $data['dispute'] = $dispute = Dispute::where(['transaction_id' => $id])->select('status')->latest()->first();

        return view('admin.transactions.edit', $data);
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());

        $t                             = Transaction::find($request->id);
        $transferred_row               = Transaction::where(['transaction_type_id' => Transferred, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])->first();
        $exchange_from                 = Transaction::where(['transaction_type_id' => Exchange_From, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])->first();
        $requestToTypeTransactionEntry = Transaction::where(['transaction_type_id' => Request_To, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])
            ->select('percentage', 'charge_percentage', 'charge_fixed')->first();
        $userInfo         = User::where(['id' => trim($request->user_id)])->first();
        $getEndUser       = User::where(['id' => trim($request->end_user_id)])->first();
        $getPaymentMethod = PaymentMethod::where(['id' => base64_decode($request->payment_method_id)])->first(['name']);



        //Deposit
        //implemented Responsable Interface (vendor/laravel/framework/src/Illuminate/Contracts/Support/Responsable.php - feature of laravel 5.5)
        // $this->depositResponse->toResponse($request);

        //Deposit
        if ($request->type == 'Deposit')
        {
            if ($request->status == 'Pending') //requested status
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();
                    // dd($current_balance);

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('current status: blocked, doing pending');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    $update_wallet_for_deposit = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending')
                {
                    // dd('current status: Pending, doing Success');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + $request->subtotal,
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Blocked');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = Deposit::find($request->transaction_reference_id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - $request->subtotal,
                    ]);
                    // dd('done');
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }


        /**
         * Withdrawal - Email Tempalte
         */                                                                                                                                                 //Withdrawal
        $english_withdrawal_email_temp = EmailTemplate::where(['temp_id' => 10, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first(); //if other language's not set, get en sub and body
        $withdrawal_email_temp = EmailTemplate::where([
            'temp_id'     => 10,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * Withdrawal - SMS Tempalte
         */
        $t_withdrawal_status_en_sms_info_suc_ref = EmailTemplate::where(['temp_id' => 10, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_withdrawal_status_sms_info_suc_ref    = EmailTemplate::where(['temp_id' => 10, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        if ($request->type == 'Withdrawal')
        {
            if ($request->status == 'Success') //requested status
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $withdrawal         = Withdrawal::find($request->transaction_reference_id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - trim($request->total, '-'),
                    ]);

                    // Withdrawal Mail
                    if (!empty($withdrawal_email_temp->subject) && !empty($withdrawal_email_temp->body))
                    {
                        $w_success_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_success_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    else
                    {
                        $w_success_sub = str_replace('{uuid}', $withdrawal->uuid, $english_withdrawal_email_temp->subject);
                        $w_success_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $english_withdrawal_email_temp->body);
                    }
                    $w_success_msg = str_replace('{uuid}', $withdrawal->uuid, $w_success_msg);
                    $w_success_msg = str_replace('{status}', $withdrawal->status, $w_success_msg);
                    $w_success_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $w_success_msg);
                    $w_success_msg = str_replace('{added/subtracted}', 'added', $w_success_msg);
                    $w_success_msg = str_replace('{from/to}', 'to', $w_success_msg);
                    $w_success_msg = str_replace('{soft_name}', Session::get('name'), $w_success_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_success_sub, $w_success_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))),
                            $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'to', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending')
                {
                    // dd('current status: Pending, doing Success');
                    $withdrawal = Withdrawal::find($request->transaction_reference_id);
                    // dd($withdrawal->transaction);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    // Withdrawal Mail
                    if (!empty($withdrawal_email_temp->subject) && !empty($withdrawal_email_temp->body))
                    {
                        $w_success_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_success_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    else
                    {
                        $w_success_sub = str_replace('{uuid}', $withdrawal->uuid, $english_withdrawal_email_temp->subject);
                        $w_success_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $english_withdrawal_email_temp->body);
                    }
                    $w_success_msg = str_replace('{uuid}', $withdrawal->uuid, $w_success_msg);
                    $w_success_msg = str_replace('{status}', $withdrawal->status, $w_success_msg);
                    $w_success_msg = str_replace('{amount}', 'No amount', $w_success_msg);
                    $w_success_msg = str_replace('{added/subtracted}', 'added/subtracted', $w_success_msg);
                    $w_success_msg = str_replace('{from/to}', 'from', $w_success_msg);
                    $w_success_msg = str_replace('{soft_name}', Session::get('name'), $w_success_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_success_sub, $w_success_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', 'No amount', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added/subtracted', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'from', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending') //requested status
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $withdrawal         = Withdrawal::find($request->transaction_reference_id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    // Withdrawal Mail
                    if (!empty($withdrawal_email_temp->subject) && !empty($withdrawal_email_temp->body))
                    {
                        $w_pending_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_pending_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    else
                    {
                        $w_pending_sub = str_replace('{uuid}', $withdrawal->uuid, $english_withdrawal_email_temp->subject);
                        $w_pending_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $english_withdrawal_email_temp->body);
                    }
                    $w_pending_msg = str_replace('{uuid}', $withdrawal->uuid, $w_pending_msg);
                    $w_pending_msg = str_replace('{status}', $withdrawal->status, $w_pending_msg);
                    $w_pending_msg = str_replace('{amount}', 'No amount', $w_pending_msg);
                    $w_pending_msg = str_replace('{added/subtracted}', 'added/subtracted', $w_pending_msg);
                    $w_pending_msg = str_replace('{from/to}', 'from', $w_pending_msg);
                    $w_pending_msg = str_replace('{soft_name}', Session::get('name'), $w_pending_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_pending_sub, $w_pending_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', 'No amount', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added/subtracted', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'from', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('current status: blocked, doing pending');
                    $withdrawal = Withdrawal::find($request->transaction_reference_id);
                    // dd($withdrawal->transaction);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance - trim($request->total, '-'),
                    ]);

                    // Withdrawal Mail
                    if (!empty($withdrawal_email_temp->subject) && !empty($withdrawal_email_temp->body))
                    {
                        $w_pending_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_pending_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    else
                    {
                        $w_pending_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_pending_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    $w_pending_msg = str_replace('{uuid}', $withdrawal->uuid, $w_pending_msg);
                    $w_pending_msg = str_replace('{status}', $withdrawal->status, $w_pending_msg);
                    $w_pending_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $w_pending_msg);
                    $w_pending_msg = str_replace('{added/subtracted}', 'subtracted', $w_pending_msg);
                    $w_pending_msg = str_replace('{from/to}', 'from', $w_pending_msg);
                    $w_pending_msg = str_replace('{soft_name}', Session::get('name'), $w_pending_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_pending_sub, $w_pending_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'subtracted', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'from', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked') //requested status
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd(trim($request->total, '-'));
                    // dd('current status: Pending, doing Blocked');
                    $withdrawal         = Withdrawal::find($request->transaction_reference_id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + trim($request->total, '-'),
                    ]);

                    // Withdrawal Mail
                    if (!empty($withdrawal_email_temp->subject) && !empty($withdrawal_email_temp->body))
                    {
                        $w_cancel_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                        $w_cancel_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    }
                    else
                    {
                        $w_cancel_sub = str_replace('{uuid}', $withdrawal->uuid, $english_withdrawal_email_temp->subject);
                        $w_cancel_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $english_withdrawal_email_temp->body);
                    }
                    $w_cancel_msg = str_replace('{uuid}', $withdrawal->uuid, $w_cancel_msg);
                    $w_cancel_msg = str_replace('{status}', ($withdrawal->status == 'Blocked') ? 'Cancelled' : $withdrawal->status, $w_cancel_msg);
                    $w_cancel_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $w_cancel_msg);
                    $w_cancel_msg = str_replace('{added/subtracted}', 'added', $w_cancel_msg);
                    $w_cancel_msg = str_replace('{from/to}', 'to', $w_cancel_msg);
                    $w_cancel_msg = str_replace('{soft_name}', Session::get('name'), $w_cancel_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_cancel_sub, $w_cancel_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', ($withdrawal->status == 'Blocked') ? 'Cancelled' : $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'to', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $withdrawal         = Withdrawal::find($request->transaction_reference_id);
                    $withdrawal->status = $request->status;
                    $withdrawal->save();

                    $transactions         = Transaction::find($request->id);
                    $transactions->status = $request->status;
                    $transactions->save();

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $current_balance->balance + trim($request->total, '-'),
                    ]);

                    // Withdrawal Mail
                    $w_cancel_sub = str_replace('{uuid}', $withdrawal->uuid, $withdrawal_email_temp->subject);
                    $w_cancel_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $withdrawal_email_temp->body);
                    $w_cancel_msg = str_replace('{uuid}', $withdrawal->uuid, $w_cancel_msg);
                    $w_cancel_msg = str_replace('{status}', ($withdrawal->status == 'Blocked') ? 'Cancelled' : $withdrawal->status, $w_cancel_msg);
                    $w_cancel_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $w_cancel_msg); //fixed in pm 1.9
                    $w_cancel_msg = str_replace('{added/subtracted}', 'added', $w_cancel_msg);
                    $w_cancel_msg = str_replace('{from/to}', 'to', $w_cancel_msg);
                    $w_cancel_msg = str_replace('{soft_name}', Session::get('name'), $w_cancel_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($withdrawal->user->email, $w_cancel_sub, $w_cancel_msg);
                    }

                    //SMS - withdrawal
                    if (!empty($withdrawal->user->carrierCode) && !empty($withdrawal->user->phone))
                    {
                        if (!empty($t_withdrawal_status_sms_info_suc_ref->subject) && !empty($t_withdrawal_status_sms_info_suc_ref->body))
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_sms_info_suc_ref->body);
                        }
                        else
                        {
                            $t_withdrawal_status_sms_info_suc_ref_sub = str_replace('{uuid}', $withdrawal->uuid, $t_withdrawal_status_en_sms_info_suc_ref->subject);
                            $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{user_id}', $withdrawal->user->first_name . ' ' . $withdrawal->user->last_name, $t_withdrawal_status_en_sms_info_suc_ref->body);
                        }
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{status}', ($withdrawal->status == 'Blocked') ? 'Cancelled' : $withdrawal->status, $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{amount}', moneyFormat($withdrawal->currency->symbol, formatNumber(trim($request->total, '-'))), $t_withdrawal_status_sms_info_suc_ref_msg); //fixed in pm 1.9
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{added/subtracted}', 'added', $t_withdrawal_status_sms_info_suc_ref_msg);
                        $t_withdrawal_status_sms_info_suc_ref_msg = str_replace('{from/to}', 'to', $t_withdrawal_status_sms_info_suc_ref_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $withdrawal->user->carrierCode . $withdrawal->user->phone, $t_withdrawal_status_sms_info_suc_ref_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }
        /***/

        /* Bank Transfer - Email Tempalte*/

        //if other language's not set, get en for mail
        $englishBankTransferTempInfoTransferredReceived = EmailTemplate::where(['temp_id' => 7, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $bankTransferEmailTemp                            = EmailTemplate::where([
            'temp_id'     => 7,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();
        //

        /* Bank Transfer - SMS Tempalte*/
        $t_bank_trans_rec_status_en_sms_temp = EmailTemplate::where(['temp_id' => 7, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_trans_rec_status_bank_sms_temp    = EmailTemplate::where(['temp_id' => 7, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();
        //

        //Bank_Transfer
        if ($request->type == 'Bank Transfer')
        {
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //done
                {
                    $this->helper->one_time_message('success', 'Bank Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //done
                {
                    // dd('current status: Pending, doing Success');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance - trim($request->total, '-'),
                        ]);

                        if (!empty($bankTransferEmailTemp->subject) && !empty($bankTransferEmailTemp->body))
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $bankTransferEmailTemp->subject);
                            $t_success_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $bankTransferEmailTemp->body);
                        }
                        else
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $englishBankTransferTempInfoTransferredReceived->subject);
                            $t_success_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishBankTransferTempInfoTransferredReceived->body);
                        }
                        $t_success_msg = str_replace('{uuid}', $transfers->uuid, $t_success_msg);
                        $t_success_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg);
                        $t_success_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_success_msg);
                        $t_success_msg = str_replace('{added/subtracted}', 'subtracted', $t_success_msg);
                        $t_success_msg = str_replace('{from/to}', 'from', $t_success_msg);
                        $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_success_sub, $t_success_msg);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_bank_sms_temp->subject) && !empty($t_trans_rec_status_bank_sms_temp->body))
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_bank_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_bank_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_bank_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_bank_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_bank_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_bank_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //done
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance - trim($request->total, '-'),
                        ]);

                        // Sent Mail when status is 'Success'
                        if (!empty($bankTransferEmailTemp->subject) && !empty($bankTransferEmailTemp->body))
                        {
                            $t_success_sub_1 = str_replace('{uuid}', $transfers->uuid, $bankTransferEmailTemp->subject);
                            $t_success_msg_1 = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $bankTransferEmailTemp->body);
                        }
                        else
                        {
                            $t_success_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishBankTransferTempInfoTransferredReceived->subject);
                            $t_success_msg_1 = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishBankTransferTempInfoTransferredReceived->body);
                        }
                        $t_success_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_success_msg_1);
                        $t_success_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg_1);

                        $t_success_msg_1 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_success_msg_1);

                        $t_success_msg_1 = str_replace('{added/subtracted}', 'subtracted', $t_success_msg_1);
                        $t_success_msg_1 = str_replace('{from/to}', 'from', $t_success_msg_1);
                        $t_success_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_success_msg_1);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_success_sub_1, $t_success_msg_1);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_bank_sms_temp->subject) && !empty($t_trans_rec_status_bank_sms_temp->body))
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_bank_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_bank_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_bank_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_bank_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_bank_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_bank_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //done
                {
                    $this->helper->one_time_message('success', 'Bank Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('current status: Success, doing Pending');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance + trim($request->total, '-'),
                        ]);

                        if (!empty($bankTransferEmailTemp->subject) && !empty($bankTransferEmailTemp->body))
                        {
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $bankTransferEmailTemp->subject);
                            $t_pending_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $bankTransferEmailTemp->body);
                        }
                        else
                        {
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $englishBankTransferTempInfoTransferredReceived->subject);
                            $t_pending_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishBankTransferTempInfoTransferredReceived->body);
                        }
                        $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                        $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);
                        $t_pending_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_pending_msg);
                        $t_pending_msg = str_replace('{added/subtracted}', 'added', $t_pending_msg);
                        $t_pending_msg = str_replace('{from/to}', 'to', $t_pending_msg);
                        $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_pending_sub, $t_pending_msg);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_bank_sms_temp->subject) && !empty($t_trans_rec_status_bank_sms_temp->body))
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_bank_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_bank_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_bank_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_bank_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_bank_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_bank_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //done
                {
                    // dd('Transferred => Request: Pending, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    if (!empty($t->bank))
                    {
                        //Received entry update
                        Transaction::where([
                            'user_id'                  => $transfers->sender_id,
                            'transaction_reference_id' => $transfers->id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $transfers->status,
                        ]);
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //done
                {
                    $this->helper->one_time_message('success', 'Bank Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance + trim($request->total, '-'),
                        ]);

                        // Sent Mail when status is 'blocked'
                        if (!empty($bankTransferEmailTemp->subject) && !empty($bankTransferEmailTemp->body))
                        {
                            $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $bankTransferEmailTemp->subject);
                            $t_block_msg_1 = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $bankTransferEmailTemp->body); //
                        }
                        else
                        {
                            $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishBankTransferTempInfoTransferredReceived->subject);
                            $t_block_msg_1 = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishBankTransferTempInfoTransferredReceived->body); //
                        }
                        $t_block_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_block_msg_1);
                        $t_block_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg_1);

                        $t_block_msg_1 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_block_msg_1);

                        $t_block_msg_1 = str_replace('{added/subtracted}', 'added', $t_block_msg_1);
                        $t_block_msg_1 = str_replace('{from/to}', 'to', $t_block_msg_1);
                        $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_block_sub_1, $t_block_msg_1);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_bank_sms_temp->subject) && !empty($t_trans_rec_status_bank_sms_temp->body))
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_bank_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_bank_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_bank_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_bank_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_bank_sms_temp_msg = str_replace('{sender_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_bank_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_bank_sms_temp_msg);
                            $t_trans_rec_status_bank_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_bank_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_bank_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //done
                {
                    // dd('Transferred => Request: Blocked, DB Status: Pending');

                    // dd($request->transaction_type_id);
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        //Received entry update
                        Transaction::where([
                            'user_id'                  => $transfers->sender_id,
                            'transaction_reference_id' => $transfers->id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $transfers->status,
                        ]);
                    }
                    $this->helper->one_time_message('success', 'Bank Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }
        //

        /**
         * Transferred/Received - Email Tempalte
         */

        //if other language's not set, get en for mail
        $englishTempInfoTransferredReceived = EmailTemplate::where(['temp_id' => 6, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $transferredReceivedEmailTemp = EmailTemplate::where([
            'temp_id'     => 6,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * Transferred/Received - SMS Tempalte
         */
        $t_trans_rec_status_en_sms_temp = EmailTemplate::where(['temp_id' => 6, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_trans_rec_status_sms_temp    = EmailTemplate::where(['temp_id' => 6, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        //Transferred
        if ($request->type == 'Transferred')
        {
            // dd('h');
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Success');
                    $transfers = Transfer::find($request->transaction_reference_id);
                    // dd($transfers);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        // dd('b');
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance + trim($request->total, '-'),
                        ]);

                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_success_msg = str_replace('{uuid}', $transfers->uuid, $t_success_msg);
                        $t_success_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg);
                        $t_success_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_success_msg);
                        $t_success_msg = str_replace('{added/subtracted}', 'added', $t_success_msg);
                        $t_success_msg = str_replace('{from/to}', 'to', $t_success_msg);
                        $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_success_sub, $t_success_msg);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        if (isset($transfers->receiver))
                        {
                            //add amount to receiver wallet only
                            $receiver_wallet = Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->select('balance')->first();
                            // dd($receiver_wallet->balance);

                            Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->update([
                                'balance' => $receiver_wallet->balance + $request->subtotal,
                            ]);
                        }

                        // Sent Mail when status is 'Success'
                        if (isset($transfers->receiver))
                        {
                            if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                            {
                                $t_success_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                                $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                            }
                            else
                            {
                                $t_success_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                                $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                            }
                            $t_success_msg = str_replace('{uuid}', $transfers->uuid, $t_success_msg);
                            $t_success_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg);
                            $t_success_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_success_msg);
                            $t_success_msg = str_replace('{added/subtracted}', 'added', $t_success_msg);
                            $t_success_msg = str_replace('{from/to}', 'to', $t_success_msg);
                            $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                            if (checkAppMailEnvironment())
                            {
                                $this->email->sendEmail($transfers->receiver->email, $t_success_sub, $t_success_msg);
                            }

                            //sms
                            if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                            {
                                if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                                {
                                    $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                    $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                                }
                                else
                                {
                                    $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                    $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                                }
                                $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                    $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                                if (checkAppSmsEnvironment())
                                {
                                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                    {
                                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                    }
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }

                    // Sent Mail when status is 'Success'
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        $t_success_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        $t_success_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    }
                    else
                    {
                        $t_success_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        $t_success_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                    }
                    $t_success_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg_1);

                    $t_success_msg_1 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_success_msg_1);

                    $t_success_msg_1 = str_replace('{added/subtracted}', 'subtracted', $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{from/to}', 'from', $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_success_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_success_sub_1, $t_success_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            $t_success_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            $t_success_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            $t_success_sub_2 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            $t_success_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_success_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{added/subtracted}', 'added', $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{from/to}', 'to', $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_success_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_success_sub_2, $t_success_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Pending');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    if (!empty($t->bank))
                    {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //sender wallet entry update
                        $sender_wallet = Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $sender_wallet->balance - trim($request->total, '-'),
                        ]);

                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                        $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);
                        $t_pending_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_pending_msg);
                        $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                        $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                        $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->sender->email, $t_pending_sub, $t_pending_msg);
                        }

                        //sms
                        if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Transferred entry update
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        //Received entry update
                        Transaction::where([
                            'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Received,
                        ])->update([
                            'status' => $request->status,
                        ]);

                        if (isset($transfers->receiver))
                        {
                            //deduct amount from receiver wallet only
                            $receiver_wallet = Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->select('balance')->first();

                            Wallet::where([
                                'user_id'     => $transfers->receiver->id,
                                'currency_id' => $request->currency_id,
                            ])->update([
                                'balance' => $receiver_wallet->balance - $request->subtotal,
                            ]);
                        }

                        // Mail when, [ request: Pending, status: Success ]
                        if (isset($transfers->receiver))
                        {

                            if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                            {
                                // subject
                                $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                                // body
                                $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                            }
                            else
                            {
                                // subject
                                $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                                // body
                                $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                            }
                            $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                            $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);

                            $t_pending_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_pending_msg);

                            $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                            $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                            $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                            if (checkAppMailEnvironment())
                            {
                                $this->email->sendEmail($transfers->receiver->email, $t_pending_sub, $t_pending_msg);
                            }

                            //sms
                            if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                            {
                                if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                                {
                                    $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                    $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                                }
                                else
                                {
                                    $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                    $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                                }
                                $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                    $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                                if (checkAppSmsEnvironment())
                                {
                                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                    {
                                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                    }
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Pending, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    // Mail when, [ request: Pending, status: Blocked ]
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        // subject
                        $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        // body
                        $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    }
                    else
                    {
                        // subject
                        $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        // body
                        $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                    }
                    $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                    $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);

                    $t_pending_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_pending_msg);

                    $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                    $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                    $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_pending_sub, $t_pending_msg);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Sent Mail when status is 'blocked'
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        $t_block_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body); //
                    }
                    else
                    {
                        $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        $t_block_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body); //
                    }
                    $t_block_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg_1);

                    $t_block_msg_1 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_block_msg_1);

                    $t_block_msg_1 = str_replace('{added/subtracted}', 'added', $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{from/to}', 'to', $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_block_sub_1, $t_block_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        $t_block_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        $t_block_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        $t_block_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{added/subtracted}', 'subtracted', $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{from/to}', 'from', $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_block_sub_2, $t_block_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending')
                {
                    // dd('Transferred => Request: Blocked, DB Status: Pending');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    // Sent Mail when status is 'blocked'
                    $t_block_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                    $t_block_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    $t_block_msg = str_replace('{uuid}', $transfers->uuid, $t_block_msg);
                    $t_block_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg);

                    $t_block_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_block_msg);

                    $t_block_msg = str_replace('{added/subtracted}', 'added', $t_block_msg);
                    $t_block_msg = str_replace('{from/to}', 'to', $t_block_msg);
                    $t_block_msg = str_replace('{soft_name}', Session::get('name'), $t_block_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_block_sub, $t_block_msg);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Refund!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    $transfers = new Transfer();

                    $transfers->sender_id   = $request->user_id;
                    $transfers->receiver_id = $request->end_user_id;

                    $transfers->currency_id = $request->currency_id;
                    $transfers->uuid        = $unique_code;

                    $transfers->fee    = $request->charge_percentage + $request->charge_fixed;
                    $transfers->amount = $request->subtotal;

                    $transfers->note   = $t->transfer->note;
                    $transfers->email  = $t->transfer->email;
                    $transfers->status = $request->status;
                    // dd($transfers);
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Transferred entry
                    $refund_t_A = new Transaction();

                    $refund_t_A->user_id     = $request->user_id;
                    $refund_t_A->end_user_id = $request->end_user_id;

                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $transfers->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Transferred
                    $refund_t_A->user_type                = $t->user_type;
                    $refund_t_A->email                    = $t->transfer->email;

                    $refund_t_A->subtotal          = $request->subtotal;
                    $refund_t_A->percentage        = $request->percentage;
                    $refund_t_A->charge_percentage = $request->charge_percentage;
                    $refund_t_A->charge_fixed      = $request->charge_fixed;
                    $refund_t_A->total             = $request->charge_percentage + $request->charge_fixed + $request->subtotal;

                    $refund_t_A->note   = $t->transfer->note;
                    $refund_t_A->status = $request->status;

                    // dd($refund_t_A);
                    $refund_t_A->save();

                    //New Received entry
                    $refund_t_B = new Transaction();

                    $refund_t_B->user_id     = $request->end_user_id;
                    $refund_t_B->end_user_id = $request->user_id;

                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $transfers->id;
                    $refund_t_B->transaction_type_id      = Received; //Received
                    $refund_t_B->user_type                = $t->user_type;
                    $refund_t_B->email                    = $t->transfer->email;

                    $refund_t_B->subtotal = $request->subtotal;
                    $refund_t_B->total    = '-' . $request->subtotal;

                    $refund_t_B->note   = $t->transfer->note;
                    $refund_t_B->status = $request->status;

                    // dd($refund_t_B);
                    $refund_t_B->save();

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Mail when refunded
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        $t_refund_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        $t_refund_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    }
                    else
                    {
                        $t_refund_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        $t_refund_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                    }
                    $t_refund_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_refund_msg_1);

                    $t_refund_msg_1 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_refund_msg_1);

                    $t_refund_msg_1 = str_replace('{added/subtracted}', 'added', $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{from/to}', 'to', $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_refund_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_refund_sub_1, $t_refund_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber(trim($request->total, '-'))), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            // subject
                            $t_refund_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            // body
                            $t_refund_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            // subject
                            $t_refund_sub_2 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            // body
                            $t_refund_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_refund_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_refund_msg_2);

                        $t_refund_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_refund_msg_2);

                        $t_refund_msg_2 = str_replace('{added/subtracted}', 'subtracted', $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{from/to}', 'from', $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_refund_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_refund_sub_2, $t_refund_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        //Received
        if ($request->type == 'Received')
        {
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('Received => Request: Success, DB Status: Pending');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //add amount to receiver wallet only
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }

                    // Sent Mail when request is 'Success'
                    if (isset($transfers->receiver))
                    {
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            $t_success_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            $t_success_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_success_msg = str_replace('{uuid}', $transfers->uuid, $t_success_msg);
                        $t_success_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg);

                        $t_success_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_success_msg);

                        $t_success_msg = str_replace('{added/subtracted}', 'added', $t_success_msg);
                        $t_success_msg = str_replace('{from/to}', 'to', $t_success_msg);
                        $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_success_sub, $t_success_msg);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance + $request->subtotal,
                        ]);
                    }

                    // Sent Mail when status is 'Success'
                    // Sender Mail
                    // subject
                    $t_success_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                    // body
                    $t_success_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    $t_success_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{added/subtracted}', 'subtracted', $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{from/to}', 'from', $t_success_msg_1);
                    $t_success_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_success_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_success_sub_1, $t_success_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                            $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        // Receiver Mail
                        // subject
                        $t_success_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        // body
                        $t_success_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        $t_success_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_success_msg_2);

                        $t_success_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_success_msg_2);

                        $t_success_msg_2 = str_replace('{added/subtracted}', 'added', $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{from/to}', 'to', $t_success_msg_2);
                        $t_success_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_success_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_success_sub_2, $t_success_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status),
                                $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('Received => Request: Pending, DB Status: Success');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //deduct amount from receiver wallet only
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Mail when, [ request: Pending, status: Success ]
                    if (isset($transfers->receiver))
                    {
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            // subject
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            // body
                            $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            // subject
                            $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            // body
                            $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                        $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);

                        $t_pending_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_pending_msg);

                        $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                        $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                        $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_pending_sub, $t_pending_msg);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Pending, DB Status: Blocked');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    // Mail when, [ request: Pending, status: Blocked ]
                    // subject
                    $t_pending_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                    // body
                    $t_pending_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    $t_pending_msg = str_replace('{uuid}', $transfers->uuid, $t_pending_msg);
                    $t_pending_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_pending_msg);
                    $t_pending_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_pending_msg);
                    $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                    $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                    $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_pending_sub, $t_pending_msg);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($request->total + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Sent Mail when status is 'blocked'
                    // Transfer Mail
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        // subject
                        $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        // body
                        $t_block_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    }
                    else
                    {
                        // subject
                        $t_block_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        // body
                        $t_block_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                    }
                    $t_block_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{added/subtracted}', 'added', $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{from/to}', 'to', $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_block_sub_1, $t_block_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        // Receiver Mail
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            // subject
                            $t_block_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            // body
                            $t_block_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            // subject
                            $t_block_sub_2 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            // body
                            $t_block_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_block_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg_2);

                        $t_block_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_block_msg_2);

                        $t_block_msg_2 = str_replace('{added/subtracted}', 'subtracted', $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{from/to}', 'from', $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_block_sub_2, $t_block_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending')
                {
                    // dd('Transferred => Request: Blocked, DB Status: Pending');
                    $transfers         = Transfer::find($request->transaction_reference_id);
                    $transfers->status = $request->status;
                    $transfers->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + (trim($request->total, '-') + ($transferred_row->charge_percentage + $transferred_row->charge_fixed)),
                    ]);

                    // Sent Mail when status is 'blocked'
                    // Cancel Mail
                    // subject
                    $t_block_sub = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                    // body
                    $t_block_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    $t_block_msg = str_replace('{uuid}', $transfers->uuid, $t_block_msg);
                    $t_block_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_block_msg);
                    $t_block_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_block_msg);
                    $t_block_msg = str_replace('{added/subtracted}', 'added', $t_block_msg);
                    $t_block_msg = str_replace('{from/to}', 'to', $t_block_msg);
                    $t_block_msg = str_replace('{soft_name}', Session::get('name'), $t_block_msg);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_block_sub, $t_block_msg);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->total)), '-'), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Refund!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    $transfers              = new Transfer();
                    $transfers->sender_id   = $request->end_user_id;
                    $transfers->receiver_id = $request->user_id;
                    $transfers->currency_id = $request->currency_id;
                    $transfers->uuid        = $unique_code;
                    $transfers->fee         = $transferred_row->charge_percentage + $transferred_row->charge_fixed;
                    $transfers->amount      = $request->subtotal;
                    $transfers->note        = $t->transfer->note;
                    $transfers->email       = $t->transfer->email;
                    $transfers->status      = $request->status;
                    $transfers->save();

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Transferred,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Transferred entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->end_user_id;
                    $refund_t_A->end_user_id              = $request->user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $transfers->id;
                    $refund_t_A->transaction_type_id      = Transferred; //Transferred
                    $refund_t_A->user_type                = $t->user_type;
                    $refund_t_A->email                    = $t->transfer->email;
                    $refund_t_A->subtotal                 = $request->subtotal;
                    $refund_t_A->percentage               = $transferred_row->percentage;
                    $refund_t_A->charge_percentage        = $transferred_row->charge_percentage;
                    $refund_t_A->charge_fixed             = $transferred_row->charge_fixed;
                    $refund_t_A->total                    = $transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal;
                    $refund_t_A->note                     = $t->transfer->note;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->user_id;
                    $refund_t_B->end_user_id              = $request->end_user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $transfers->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Received
                    $refund_t_B->user_type                = $t->user_type;
                    $refund_t_B->email                    = $t->transfer->email;
                    $refund_t_B->subtotal                 = $request->subtotal;
                    $refund_t_B->total                    = '-' . $request->subtotal;
                    $refund_t_B->note                     = $t->transfer->note;
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $transfers->sender->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + ($transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal),
                    ]);

                    if (isset($transfers->receiver))
                    {
                        //receiver wallet entry update
                        $receiver_wallet = Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $transfers->receiver->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $receiver_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Mail when refunded
                    if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                    {
                        $t_refund_sub_1 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                        $t_refund_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $transferredReceivedEmailTemp->body);
                    }
                    else
                    {
                        $t_refund_sub_1 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                        $t_refund_msg_1 = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $englishTempInfoTransferredReceived->body);
                    }
                    $t_refund_msg_1 = str_replace('{uuid}', $transfers->uuid, $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal)), '-'), $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{added/subtracted}', 'added', $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{from/to}', 'to', $t_refund_msg_1);
                    $t_refund_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_refund_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($transfers->sender->email, $t_refund_sub_1, $t_refund_msg_1);
                    }

                    //sms
                    if (!empty($transfers->sender->carrierCode) && !empty($transfers->sender->phone))
                    {
                        if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_sms_temp->body);
                        }
                        else
                        {
                            $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->sender->first_name . ' ' . $transfers->sender->last_name, $t_trans_rec_status_en_sms_temp->body);
                        }
                        $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', trim(moneyFormat($transferred_row->currency->symbol, formatNumber($transferred_row->charge_percentage + $transferred_row->charge_fixed + $refund_t_A->subtotal)), '-'), $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_trans_rec_status_sms_temp_msg);
                        $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'to', $t_trans_rec_status_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->sender->carrierCode . $transfers->sender->phone, $t_trans_rec_status_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($transfers->receiver))
                    {
                        if (!empty($transferredReceivedEmailTemp->subject) && !empty($transferredReceivedEmailTemp->body))
                        {
                            $t_refund_sub_2 = str_replace('{uuid}', $transfers->uuid, $transferredReceivedEmailTemp->subject);
                            $t_refund_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $transferredReceivedEmailTemp->body);
                        }
                        else
                        {
                            $t_refund_sub_2 = str_replace('{uuid}', $transfers->uuid, $englishTempInfoTransferredReceived->subject);
                            $t_refund_msg_2 = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $englishTempInfoTransferredReceived->body);
                        }
                        $t_refund_msg_2 = str_replace('{uuid}', $transfers->uuid, $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_refund_msg_2);

                        $t_refund_msg_2 = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_refund_msg_2);

                        $t_refund_msg_2 = str_replace('{added/subtracted}', 'subtracted', $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{from/to}', 'from', $t_refund_msg_2);
                        $t_refund_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_refund_msg_2);
                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($transfers->receiver->email, $t_refund_sub_2, $t_refund_msg_2);
                        }

                        //sms
                        if (!empty($transfers->receiver->carrierCode) && !empty($transfers->receiver->phone))
                        {
                            if (!empty($t_trans_rec_status_sms_temp->subject) && !empty($t_trans_rec_status_sms_temp->body))
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_sms_temp->body);
                            }
                            else
                            {
                                $t_trans_rec_status_sms_temp_sub = str_replace('{uuid}', $transfers->uuid, $t_trans_rec_status_en_sms_temp->subject);
                                $t_trans_rec_status_sms_temp_msg = str_replace('{sender_id/receiver_id}', $transfers->receiver->first_name . ' ' . $transfers->receiver->last_name, $t_trans_rec_status_en_sms_temp->body);
                            }
                            $t_trans_rec_status_sms_temp_msg = str_replace('{status}', ($transfers->status == 'Blocked') ? "Cancelled" : (($transfers->status == 'Refund') ? "Refunded" : $transfers->status), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{amount}', moneyFormat($transfers->currency->symbol, formatNumber($request->subtotal)), $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_trans_rec_status_sms_temp_msg);
                            $t_trans_rec_status_sms_temp_msg = str_replace('{from/to}', 'from', $t_trans_rec_status_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $transfers->receiver->carrierCode . $transfers->receiver->phone, $t_trans_rec_status_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        //Exchange_From
        if ($request->type == 'Exchange From')
        {
            $exFromSubtotal = number_format((float) $request->subtotal, 2, '.', ''); //fix
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $exchange         = CurrencyExchange::find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();
                    // dd();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance - trim($request->total, '-'),
                    ]);

                    //receiver wallet entry update
                    $receiver_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        // 'balance' => $receiver_wallet->balance + trim($request->total, '-') * $exchange->exchange_rate,
                        'balance' => $receiver_wallet->balance + ($exFromSubtotal * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd(number_format($request->subtotal, 2));

                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $exchange = CurrencyExchange::find($request->transaction_reference_id);
                    // dd($exchange->toWallet->currency->rate);
                    $exchange->status = $request->status;
                    $exchange->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sender wallet entry update
                    $sender_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $sender_wallet->balance + trim($request->total, '-'),
                    ]);

                    //receiver wallet entry update
                    $receiver_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        // 'balance' => $receiver_wallet->balance - (trim($request->total, '-') * $exchange->exchange_rate),
                        'balance' => $receiver_wallet->balance - ($exFromSubtotal * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        //Exchange_To
        if ($request->type == 'Exchange To')
        {
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked')
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $exchange         = CurrencyExchange::find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $to_wallet->balance + $request->total,
                    ]);

                    //sender wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance - trim($exchange_from->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $exchange         = CurrencyExchange::find($request->transaction_reference_id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $to_wallet->balance - $request->total,
                    ]);

                    //sender wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance + trim($exchange_from->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        /**
         * Payment Sent/Payment Received - Email Template
         */
        $englishTempInfoPaymentSentPaymentReceived = EmailTemplate::where(['temp_id' => 14, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first(); //if other language's not set, get en
        $merchant_status_mail_info                 = EmailTemplate::where([
            'temp_id'     => 14,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * Payment Sent/Payment Received - SMS Template
         */
        $t_paysentreceived_en_sms_temp = EmailTemplate::where(['temp_id' => 14, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_paysentreceived_sms_temp    = EmailTemplate::where(['temp_id' => 14, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        $getPaymentReceivedMerchantTransaction = Transaction::where(['transaction_type_id' => Payment_Received, 'uuid' => $request->uuid, 'transaction_reference_id' => $request->transaction_reference_id])
            ->first(['charge_percentage']);

        /**
         * Payment_Sent
         */
        if ($request->type == 'Payment Sent')
        {
            // dd($request->all());
            if ($request->status == 'Pending')
            {
                if ($t->status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('current status: Success, doing Pending');
                    $merchant_payment         = MerchantPayment::find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'end_user_id'              => $request->user_id,
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
                        'balance' => $merchant_user_wallet->balance - ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage),
                    ]);

                    //merchant
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'from', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($t->status == 'Success')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //done
                {
                    // dd('current status: Pending, doing Success');
                    $merchant_payment         = MerchantPayment::find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    Transaction::where([
                        'end_user_id'              => $request->user_id,
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
                        'balance' => $merchant_user_wallet->balance + ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage),
                    ]);

                    // Mail
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
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, ($request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage)), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'to', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Refunded!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd($request->all());

                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = $request->user_id;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $getPaymentReceivedMerchantTransaction->charge_percentage;
                    // $merchant_payment->charge_fixed      = 0;
                    $merchant_payment->charge_fixed      = $getPaymentReceivedMerchantTransaction->charge_fixed;//new
                    // $merchant_payment->amount            = $request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage;
                    $merchant_payment->amount            = $request->subtotal - ($getPaymentReceivedMerchantTransaction->charge_percentage + $getPaymentReceivedMerchantTransaction->charge_fixed);//new
                    $merchant_payment->total             = '-' . $request->subtotal;
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //Payment_Sent old entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Payment_Received old entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Payment_Received,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Payment_Sent entry
                    $refund_t_A                           = new Transaction();
                    $refund_t_A->user_id                  = $request->user_id;
                    $refund_t_A->end_user_id              = $request->end_user_id;
                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $request->transaction_reference_id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Payment_Sent
                    $refund_t_A->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    $refund_t_A->subtotal                 = $request->subtotal;
                    $refund_t_A->percentage               = $request->percentage;
                    $refund_t_A->charge_percentage        = 0;
                    $refund_t_A->charge_fixed             = 0;
                    $refund_t_A->total                    = $request->subtotal;
                    $refund_t_A->status                   = $request->status;
                    $refund_t_A->save();

                    //New Payment_Received entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->end_user_id;
                    $refund_t_B->end_user_id              = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                    $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                    $refund_t_B->transaction_type_id      = Payment_Received; //Payment_Received
                    $refund_t_B->user_type                = isset($userInfo) ? 'registered' : 'unregistered';
                    // $refund_t_B->subtotal                 = $request->subtotal - $getPaymentReceivedMerchantTransaction->charge_percentage;
                    $refund_t_B->subtotal                 = $request->subtotal - ($getPaymentReceivedMerchantTransaction->charge_percentage + $getPaymentReceivedMerchantTransaction->charge_fixed); //new
                    $refund_t_B->percentage               = $request->percentage;
                    $refund_t_B->charge_percentage        = $getPaymentReceivedMerchantTransaction->charge_percentage;
                    // $refund_t_B->charge_fixed             = 0;
                    $refund_t_B->charge_fixed             = $getPaymentReceivedMerchantTransaction->charge_fixed;//new
                    $refund_t_B->total                    = '-' . $request->subtotal;
                    $refund_t_B->status                   = $request->status;
                    $refund_t_B->save();

                    //add amount to paid_by_user wallet
                    if (isset($merchant_payment->user_id))
                    {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $merchant_payment->user->id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $merchant_payment->user->id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $paid_by_user->balance + $request->subtotal,
                        ]);
                    }

                    //Sender(user_id) //paid_by_user
                    if (isset($merchant_payment->user_id))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }

                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->user->carrierCode) && !empty($merchant_payment->user->phone))
                        {
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->subtotal), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'to', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->user->carrierCode . $merchant_payment->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }

                    //deduct amount from merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $merchant_payment->amount,
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
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }

                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($merchant_payment->amount)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $merchant_payment->amount), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'from', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        /**
         * Payment_Received
         */
        if ($request->type == 'Payment Received')
        {
            // dd($request->all());

            if ($request->status == 'Pending')
            {
                if ($t->status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('current status: Success, doing Pending');
                    $merchant_payment         = MerchantPayment::find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    if ($getPaymentMethod->name != 'Mts')
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }
                    else
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }

                    //deduct amount from receiver wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->subtotal,
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
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }
                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->subtotal), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'from', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($t->status == 'Success')
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //done
                {
                    // dd('current status: Pending, doing Success');
                    $merchant_payment         = MerchantPayment::find($request->transaction_reference_id);
                    $merchant_payment->status = $request->status;
                    $merchant_payment->save();

                    if ($getPaymentMethod->name != 'Mts')
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }
                    else
                    {
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'status' => $request->status,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'status' => $request->status,
                        ]);
                    }

                    // add amount to merchant_user_wallet wallet only
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance + $request->subtotal,
                    ]);

                    // Mail
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
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }

                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->subtotal), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'to', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'MerchantPayment is already Refunded!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('current status: Success, doing Refund');
                    $unique_code = unique_code();

                    //MerchantPayment
                    $merchant_payment                    = new MerchantPayment();
                    $merchant_payment->merchant_id       = base64_decode($request->merchant_id);
                    $merchant_payment->currency_id       = $request->currency_id;
                    $merchant_payment->payment_method_id = base64_decode($request->payment_method_id);
                    $merchant_payment->user_id           = isset($getEndUser) ? $getEndUser->id : null;
                    $merchant_payment->gateway_reference = base64_decode($request->gateway_reference);
                    $merchant_payment->order_no          = $request->order_no;
                    $merchant_payment->item_name         = $request->item_name;
                    $merchant_payment->uuid              = $unique_code;
                    $merchant_payment->charge_percentage = $request->charge_percentage;
                    // $merchant_payment->charge_fixed      = 0;
                    $merchant_payment->charge_fixed      = $request->charge_fixed;
                    $merchant_payment->amount            = $request->subtotal;
                    // $merchant_payment->total             = '-' . ($request->charge_percentage + $request->subtotal);
                    $merchant_payment->total             = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                    $merchant_payment->status            = $request->status;
                    $merchant_payment->save();

                    //update refund reference
                    if ($getPaymentMethod->name != 'Mts')
                    {
                        //Transaction- not mts
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);
                    }
                    else
                    {
                        //Transaction- mts
                        Transaction::where([
                            'user_id'                  => $request->user_id,
                            'end_user_id'              => isset($getEndUser) ? $getEndUser->id : null,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => $request->transaction_type_id, //Payment_Received
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);

                        Transaction::where([
                            'user_id'                  => isset($getEndUser) ? $getEndUser->id : null,
                            'end_user_id'              => $request->user_id,
                            'transaction_reference_id' => $request->transaction_reference_id,
                            'transaction_type_id'      => Payment_Sent,
                        ])->update([
                            'refund_reference' => $unique_code,
                        ]);
                    }

                    // $merchantInfo = Merchant::find($merchantPayment->merchant_id);

                    if ($getPaymentMethod->name != 'Mts')
                    {
                        //Transaction- not mts

                        //New Payment_Received entry
                        $refund_t_B              = new Transaction();
                        $refund_t_B->user_id     = $request->user_id;
                        $refund_t_B->end_user_id = null;

                        $refund_t_B->currency_id              = $request->currency_id;
                        $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_B->uuid                     = $unique_code;
                        $refund_t_B->refund_reference         = $request->uuid;
                        $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Payment_Received
                        $refund_t_B->subtotal                 = $request->subtotal;
                        $refund_t_B->percentage               = $request->percentage;
                        $refund_t_B->charge_percentage        = $request->charge_percentage;
                        $refund_t_B->charge_fixed             = $request->charge_fixed;
                        $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                        $refund_t_B->status                   = $request->status;
                        $refund_t_B->save();
                    }
                    else
                    {
                        //Transaction- mts

                        //New Payment_Sent entry
                        $refund_t_A                           = new Transaction();
                        $refund_t_A->user_id                  = $request->end_user_id;
                        $refund_t_A->end_user_id              = $request->user_id;
                        $refund_t_A->currency_id              = $request->currency_id;
                        $refund_t_A->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_A->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_A->uuid                     = $unique_code;
                        $refund_t_A->refund_reference         = $request->uuid;
                        $refund_t_A->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_A->transaction_type_id      = Payment_Sent; //Payment_Sent
                        $refund_t_A->subtotal                 = $request->total;
                        $refund_t_A->percentage               = $request->percentage;
                        $refund_t_A->charge_percentage        = 0;
                        $refund_t_A->charge_fixed             = 0;
                        $refund_t_A->total                    = $request->total;
                        $refund_t_A->status                   = $request->status;
                        $refund_t_A->save();

                        //New Payment_Received entry
                        $refund_t_B                           = new Transaction();
                        $refund_t_B->user_id                  = $request->user_id;
                        $refund_t_B->end_user_id              = $request->end_user_id;
                        $refund_t_B->currency_id              = $request->currency_id;
                        $refund_t_B->payment_method_id        = base64_decode($request->payment_method_id);
                        $refund_t_B->merchant_id              = base64_decode($request->merchant_id);
                        $refund_t_B->uuid                     = $unique_code;
                        $refund_t_B->refund_reference         = $request->uuid;
                        $refund_t_B->transaction_reference_id = $request->transaction_reference_id;
                        $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Payment_Received
                        $refund_t_B->subtotal                 = $request->subtotal;
                        $refund_t_B->percentage               = $request->percentage;
                        $refund_t_B->charge_percentage        = $request->charge_percentage;
                        $refund_t_B->charge_fixed             = $request->charge_fixed;
                        $refund_t_B->total                    = '-' . ($request->charge_percentage + $request->charge_fixed + $request->subtotal);
                        $refund_t_B->status                   = $request->status;
                        $refund_t_B->save();
                    }

                    //add amount to paid_by_user wallet, if exists
                    if (isset($merchant_payment->user_id))
                    {
                        $paid_by_user = Wallet::where([
                            'user_id'     => $merchant_payment->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $merchant_payment->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            // 'balance' => $paid_by_user->balance + $request->total,
                            'balance' => $paid_by_user->balance + ($request->charge_percentage + $request->charge_fixed + $request->subtotal),
                        ]);
                    }

                    //deduct amount from merchant_user_wallet wallet
                    $merchant_user_wallet = Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $merchant_payment->merchant->user->id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $merchant_user_wallet->balance - $request->subtotal,
                    ]);

                    //Sender(end_user_id) //paid_by_user
                    if (isset($merchant_payment->user_id))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }

                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->total)), $m_mail_body);
                        $m_mail_body = str_replace('{added/subtracted}', 'added', $m_mail_body);
                        $m_mail_body = str_replace('{from/to}', 'to', $m_mail_body);
                        $m_mail_body = str_replace('{soft_name}', Session::get('name'), $m_mail_body);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($merchant_payment->user->email, $m_mail_sub, $m_mail_body);
                        }

                        //sms
                        if (!empty($merchant_payment->user->carrierCode) && !empty($merchant_payment->user->phone))
                        {
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->user->first_name . ' ' . $merchant_payment->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->total), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'to', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->user->carrierCode . $merchant_payment->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }

                    //Receiver(user_id) //merchant_user_wallet
                    if (isset($merchant_payment->merchant))
                    {
                        if (!empty($merchant_status_mail_info->subject) && !empty($merchant_status_mail_info->body))
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $merchant_status_mail_info->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $merchant_status_mail_info->body);
                        }
                        else
                        {
                            $m_mail_sub  = str_replace('{uuid}', $merchant_payment->uuid, $englishTempInfoPaymentSentPaymentReceived->subject);
                            $m_mail_body = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $englishTempInfoPaymentSentPaymentReceived->body);
                        }

                        $m_mail_body = str_replace('{uuid}', $merchant_payment->uuid, $m_mail_body);
                        $m_mail_body = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $m_mail_body);
                        $m_mail_body = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, formatNumber($request->subtotal)), $m_mail_body);
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
                            if (!empty($t_paysentreceived_sms_temp->subject) && !empty($t_paysentreceived_sms_temp->body))
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_sms_temp->body);
                            }
                            else
                            {
                                $t_paysentreceived_sms_temp_sub = str_replace('{uuid}', $merchant_payment->uuid, $t_paysentreceived_en_sms_temp->subject);
                                $t_paysentreceived_sms_temp_msg = str_replace('{paidByUser/merchantUser}', $merchant_payment->merchant->user->first_name . ' ' . $merchant_payment->merchant->user->last_name, $t_paysentreceived_en_sms_temp->body);
                            }
                            $t_paysentreceived_sms_temp_msg = str_replace('{status}', ($merchant_payment->status == 'Blocked') ? "Cancelled" : (($merchant_payment->status == 'Refund') ? "Refunded" : $merchant_payment->status), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{amount}', moneyFormat($merchant_payment->currency->symbol, $request->subtotal), $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_paysentreceived_sms_temp_msg);
                            $t_paysentreceived_sms_temp_msg = str_replace('{from/to}', 'from', $t_paysentreceived_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $merchant_payment->merchant->user->carrierCode . $merchant_payment->merchant->user->phone, $t_paysentreceived_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Merchant Payment Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }


        /**
         * Request From/Request To - Email Template
         */
        $englishTempInfoRequestFromRequestToSuccessRefund = EmailTemplate::where(['temp_id' => 8, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $t_ref_mail_info                                  = EmailTemplate::where([
            'temp_id'     => 8,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        $englishTempInfoRequestFromRequestToCancelPending = EmailTemplate::where(['temp_id' => 16, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
        $requestPaymentEmailTemp = EmailTemplate::where([
            'temp_id'     => 16,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * Request From/Request To - SMS Template
         */
        $t_rp_suc_ref_en_sms_temp = EmailTemplate::where(['temp_id' => 8, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_rp_suc_ref_sms_temp    = EmailTemplate::where(['temp_id' => 8, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        $t_rp_can_pen_en_sms_temp = EmailTemplate::where(['temp_id' => 16, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        $t_rp_can_pen_sms_temp    = EmailTemplate::where(['temp_id' => 16, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        if ($request->type == 'Request From')
        {
            // dd('Request From');
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Refund!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('doing Refund'); //start from here
                    $unique_code = unique_code();

                    $requestpayment = new RequestPayment();

                    $requestpayment->user_id = $request->user_id;

                    $requestpayment->receiver_id = $request->end_user_id;

                    $requestpayment->currency_id = $request->currency_id;

                    $requestpayment->uuid = $unique_code;

                    $requestpayment->amount = $t->request_payment->amount;

                    $requestpayment->accept_amount = $request->subtotal;

                    $requestpayment->email = $t->request_payment->email;

                    $requestpayment->note = $t->request_payment->note;

                    $requestpayment->status = $request->status;
                    // dd($requestpayment);
                    $requestpayment->save();

                    //Transferred entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Received entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_To,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_From entry
                    $refund_t_A = new Transaction();

                    $refund_t_A->user_id     = $request->user_id;
                    $refund_t_A->end_user_id = $request->end_user_id;

                    $refund_t_A->currency_id = $request->currency_id;

                    $refund_t_A->uuid = $unique_code;

                    $refund_t_A->refund_reference = $request->uuid;

                    $refund_t_A->transaction_reference_id = $requestpayment->id;
                    $refund_t_A->transaction_type_id      = $request->transaction_type_id; //Request_From

                    $refund_t_A->user_type = $t->user_type;

                    $refund_t_A->subtotal = $request->subtotal;

                    $refund_t_A->total = '-' . $refund_t_A->subtotal;

                    $refund_t_A->note   = $t->request_payment->note;
                    $refund_t_A->status = $request->status;

                    // dd($refund_t_A);
                    $refund_t_A->save();

                    //New Request_To entry
                    $refund_t_B                           = new Transaction();
                    $refund_t_B->user_id                  = $request->end_user_id;
                    $refund_t_B->end_user_id              = $request->user_id;
                    $refund_t_B->currency_id              = $request->currency_id;
                    $refund_t_B->uuid                     = $unique_code;
                    $refund_t_B->refund_reference         = $request->uuid;
                    $refund_t_B->transaction_reference_id = $requestpayment->id;
                    $refund_t_B->transaction_type_id      = Request_To; //Request_To

                    $refund_t_B->user_type = $t->user_type;
                    // $refund_t_B->email               = $request->request_payments_email;

                    $refund_t_B->subtotal = $request->subtotal;

                    $refund_t_B->percentage        = $requestToTypeTransactionEntry->percentage;
                    $refund_t_B->charge_percentage = $requestToTypeTransactionEntry->charge_percentage;
                    $refund_t_B->charge_fixed      = $requestToTypeTransactionEntry->charge_fixed;

                    $refund_t_B->total = ($requestToTypeTransactionEntry->charge_percentage + $requestToTypeTransactionEntry->charge_fixed + $refund_t_B->subtotal);

                    $refund_t_B->note = $t->request_payment->note;

                    $refund_t_B->status = $request->status;

                    // dd($refund_t_B);
                    $refund_t_B->save();

                    //sender wallet entry update
                    $request_created_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $request_created_wallet->balance - $request->subtotal,
                    ]);

                    if (isset($request->end_user_id))
                    {
                        //receiver wallet entry update
                        $request_accepted_wallet = Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $request_accepted_wallet->balance + $refund_t_B->total,
                        ]);
                    }

                    // Sent Mail when request is 'refunded'
                    // Creator Mail
                    if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $unique_code, $t_ref_mail_info->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_ref_mail_info->body);
                    }
                    else
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $unique_code, $englishTempInfoRequestFromRequestToSuccessRefund->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishTempInfoRequestFromRequestToSuccessRefund->body);
                    }

                    $t_ref_msg_1 = str_replace('{uuid}', $unique_code, $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_ref_msg_1);

                    $t_ref_msg_1 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->subtotal)), $t_ref_msg_1);

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
                        if (!empty($t_rp_suc_ref_sms_temp->subject) && !empty($t_rp_suc_ref_sms_temp->body))
                        {
                            $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_sms_temp->subject);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_suc_ref_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_en_sms_temp->subject);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_suc_ref_en_sms_temp->body);
                        }
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_rp_suc_ref_sms_temp_msg);

                        $t_rp_suc_ref_sms_temp_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->subtotal)), $t_rp_suc_ref_sms_temp_msg);

                        $t_rp_suc_ref_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_rp_suc_ref_sms_temp_msg);
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{from/to}', 'from', $t_rp_suc_ref_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $t_rp_suc_ref_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($request->end_user_id))
                    {
                        // Acceptor Mail
                        if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $unique_code, $t_ref_mail_info->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_ref_mail_info->body);
                        }
                        else
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $unique_code, $englishTempInfoRequestFromRequestToSuccessRefund->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishTempInfoRequestFromRequestToSuccessRefund->body);
                        }

                        $t_ref_msg_2 = str_replace('{uuid}', $unique_code, $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_ref_msg_2);

                        $t_ref_msg_2 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($refund_t_B->total)), $t_ref_msg_2);

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
                            if (!empty($t_rp_suc_ref_sms_temp->subject) && !empty($t_rp_suc_ref_sms_temp->body))
                            {
                                $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_sms_temp->subject);
                                $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_suc_ref_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_en_sms_temp->subject);
                                $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_suc_ref_en_sms_temp->body);
                            }
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_rp_suc_ref_sms_temp_msg);

                            $t_rp_suc_ref_sms_temp_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($refund_t_B->total)), $t_rp_suc_ref_sms_temp_msg);

                            $t_rp_suc_ref_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_rp_suc_ref_sms_temp_msg);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{from/to}', 'to', $t_rp_suc_ref_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $t_rp_suc_ref_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('Request_From => Request: Blocked, DB Status: Pending');
                    $requestpayment         = RequestPayment::find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Sent Mail when request is 'blocked'
                    if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                    {
                        //Subject
                        $t_block_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                        //Body
                        $t_block_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $requestPaymentEmailTemp->body);
                    }
                    else
                    {
                        //Subject
                        $t_block_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                        //Body
                        $t_block_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                    }
                    $t_block_msg_1 = str_replace('{uuid}', $requestpayment->uuid, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($requestpayment->user->email, $t_block_sub_1, $t_block_msg_1);
                    }

                    //sms
                    if (!empty($requestpayment->user->carrierCode) && !empty($requestpayment->user->phone))
                    {
                        if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                        }
                        $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $t_rp_can_pen_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($requestpayment->receiver))
                    {
                        if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                        {
                            //Subject
                            $t_block_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                            //Body
                            $t_block_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $requestPaymentEmailTemp->body);
                        }
                        else
                        {
                            //Subject
                            $t_block_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                            //Body
                            $t_block_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                        }
                        $t_block_msg_2 = str_replace('{uuid}', $requestpayment->uuid, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($requestpayment->receiver->email, $t_block_sub_2, $t_block_msg_2);
                        }

                        //sms
                        if (!empty($requestpayment->receiver->carrierCode) && !empty($requestpayment->receiver->phone))
                        {
                            if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_en_sms_temp->body);
                            }
                            $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $t_rp_can_pen_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('Request_From => Request: Blocked, DB Status: Pending');
                    $request_payment         = RequestPayment::find($request->transaction_reference_id);
                    $request_payment->status = $request->status;
                    $request_payment->save();

                    //Request From entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //Request To entry update
                    Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Sent Mail when request is 'Pending'
                    if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                    {
                        //Subject
                        $t_pending_sub_1 = str_replace('{uuid}', $request_payment->uuid, $requestPaymentEmailTemp->subject);
                        //Body
                        $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $requestPaymentEmailTemp->body);
                    }
                    else
                    {
                        //Subject
                        $t_pending_sub_1 = str_replace('{uuid}', $request_payment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                        //Body
                        $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                    }
                    $t_pending_msg_1 = str_replace('{uuid}', $request_payment->uuid, $t_pending_msg_1);
                    $t_pending_msg_1 = str_replace('{status}', ($request_payment->status == 'Blocked') ? 'Cancelled' : $request_payment->status, $t_pending_msg_1);
                    $t_pending_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($request_payment->user->email, $t_pending_sub_1, $t_pending_msg_1);
                    }

                    //sms
                    if (!empty($request_payment->user->carrierCode) && !empty($request_payment->user->phone))
                    {
                        if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $t_rp_can_pen_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                        }
                        $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_payment->user->carrierCode . $request_payment->user->phone, $t_rp_can_pen_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($request_payment->receiver))
                    {
                        if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                        {
                            //Subject
                            $t_pending_sub_2 = str_replace('{uuid}', $request_payment->uuid, $requestPaymentEmailTemp->subject);
                            //Body
                            $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $request_payment->receiver->first_name . ' ' . $request_payment->receiver->last_name, $requestPaymentEmailTemp->body);
                        }
                        else
                        {
                            //Subject
                            $t_pending_sub_2 = str_replace('{uuid}', $request_payment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                            //Body
                            $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $request_payment->receiver->first_name . ' ' . $request_payment->receiver->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                        }

                        $t_pending_msg_2 = str_replace('{uuid}', $request_payment->uuid, $t_pending_msg_2);
                        $t_pending_msg_2 = str_replace('{status}', ($request_payment->status == 'Blocked') ? 'Cancelled' : $request_payment->status, $t_pending_msg_2);
                        $t_pending_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($request_payment->receiver->email, $t_pending_sub_2, $t_pending_msg_2);
                        }

                        //sms
                        if (!empty($request_payment->user->carrierCode) && !empty($request_payment->user->phone))
                        {
                            if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $t_rp_can_pen_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $request_payment->user->first_name . ' ' . $request_payment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                            }
                            $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request_payment->user->carrierCode . $request_payment->user->phone, $t_rp_can_pen_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        /**
         * Request_To
         */
        if ($request->type == 'Request To')
        {
            // dd('working in Request To');
            if ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Refund')
            {
                if ($t->status == 'Refund') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Refund!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //done
                {
                    // dd('doing Refund');
                    $unique_code = unique_code();

                    $requestpayment = new RequestPayment();

                    $requestpayment->user_id     = $request->end_user_id;
                    $requestpayment->receiver_id = $request->user_id;

                    $requestpayment->currency_id = $request->currency_id;

                    $requestpayment->uuid = $unique_code;

                    $requestpayment->amount        = $t->request_payment->amount;
                    $requestpayment->accept_amount = $request->subtotal;

                    $requestpayment->email = $t->request_payment->email;

                    $requestpayment->note = $t->request_payment->note;

                    $requestpayment->status = $request->status;
                    // dd($requestpayment);
                    $requestpayment->save();

                    //Request_From entry update
                    Transaction::where([
                        'user_id'                  => $request->end_user_id,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_From,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //Request_To entry update
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => $request->end_user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'refund_reference' => $unique_code,
                    ]);

                    //New Request_From entry
                    $refund_t_A = new Transaction();

                    $refund_t_A->user_id     = $request->end_user_id;
                    $refund_t_A->end_user_id = $request->user_id;

                    $refund_t_A->currency_id              = $request->currency_id;
                    $refund_t_A->uuid                     = $unique_code;
                    $refund_t_A->refund_reference         = $request->uuid;
                    $refund_t_A->transaction_reference_id = $requestpayment->id;

                    $refund_t_A->transaction_type_id = Request_From; //Request_From

                    $refund_t_A->user_type = $t->user_type;

                    $refund_t_A->subtotal = $request->subtotal;
                    $refund_t_A->total    = '-' . $refund_t_A->subtotal;

                    $refund_t_A->note = $t->request_payment->note;

                    $refund_t_A->status = $request->status;

                    // dd($refund_t_A);
                    $refund_t_A->save();

                    //New Request_To entry
                    $refund_t_B = new Transaction();

                    $refund_t_B->user_id     = $request->user_id;
                    $refund_t_B->end_user_id = $request->end_user_id;

                    $refund_t_B->currency_id = $request->currency_id;

                    $refund_t_B->uuid = $unique_code;

                    $refund_t_B->refund_reference = $request->uuid;

                    $refund_t_B->transaction_reference_id = $requestpayment->id;
                    $refund_t_B->transaction_type_id      = $request->transaction_type_id; //Request_To

                    $refund_t_B->user_type = $t->user_type;

                    $refund_t_B->subtotal          = $request->subtotal;
                    $refund_t_B->percentage        = $request->percentage;
                    $refund_t_B->charge_percentage = $request->charge_percentage;
                    $refund_t_B->charge_fixed      = $request->charge_fixed;
                    $refund_t_B->total             = ($request->charge_percentage + $request->charge_fixed + $refund_t_B->subtotal);

                    $refund_t_B->note   = $t->request_payment->note;
                    $refund_t_B->status = $request->status;

                    // dd($refund_t_B);
                    $refund_t_B->save();

                    //Acceptor wallet entry update
                    $request_accepted_wallet = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $request_accepted_wallet->balance + $refund_t_B->total,
                    ]);

                    if (isset($request->end_user_id))
                    {
                        //Creator wallet entry update
                        $request_created_wallet = Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        Wallet::where([
                            'user_id'     => $request->end_user_id,
                            'currency_id' => $request->currency_id,
                        ])->update([
                            'balance' => $request_created_wallet->balance - $request->subtotal,
                        ]);
                    }

                    // Sent Mail & Sms when request is 'refunded'

                    // Acceptor Mail
                    if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $unique_code, $t_ref_mail_info->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_ref_mail_info->body);
                    }
                    else
                    {
                        // subject
                        $t_ref_sub_1 = str_replace('{uuid}', $unique_code, $englishTempInfoRequestFromRequestToSuccessRefund->subject);
                        // body
                        $t_ref_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishTempInfoRequestFromRequestToSuccessRefund->body);
                    }

                    $t_ref_msg_1 = str_replace('{uuid}', $unique_code, $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_ref_msg_1);

                    $t_ref_msg_1 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($refund_t_B->total)), $t_ref_msg_1);

                    $t_ref_msg_1 = str_replace('{added/subtracted}', 'added', $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{from/to}', 'to', $t_ref_msg_1);
                    $t_ref_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_ref_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($requestpayment->receiver->email, $t_ref_sub_1, $t_ref_msg_1);
                    }

                    //sms
                    if (!empty($requestpayment->receiver->carrierCode) && !empty($requestpayment->receiver->phone))
                    {
                        if (!empty($t_rp_suc_ref_sms_temp->subject) && !empty($t_rp_suc_ref_sms_temp->body))
                        {
                            $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_sms_temp->subject);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_suc_ref_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_can_pen_en_sms_temp->subject);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_en_sms_temp->body);
                        }
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_rp_suc_ref_sms_temp_msg);
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($refund_t_B->total)), $t_rp_suc_ref_sms_temp_msg);
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{added/subtracted}', 'added', $t_rp_suc_ref_sms_temp_msg);
                        $t_rp_suc_ref_sms_temp_msg = str_replace('{from/to}', 'to', $t_rp_suc_ref_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $t_rp_suc_ref_sms_temp_msg);
                            }
                        }
                    }

                    // Creator Mail
                    if (isset($request->end_user_id))
                    {
                        if (!empty($t_ref_mail_info->subject) && !empty($t_ref_mail_info->body))
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $unique_code, $t_ref_mail_info->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_ref_mail_info->body);
                        }
                        else
                        {
                            // subject
                            $t_ref_sub_2 = str_replace('{uuid}', $unique_code, $englishTempInfoRequestFromRequestToSuccessRefund->subject);
                            // body
                            $t_ref_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishTempInfoRequestFromRequestToSuccessRefund->body);
                        }
                        $t_ref_msg_2 = str_replace('{uuid}', $unique_code, $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->subtotal)), $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{added/subtracted}', 'subtracted', $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{from/to}', 'from', $t_ref_msg_2);
                        $t_ref_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_ref_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($requestpayment->user->email, $t_ref_sub_2, $t_ref_msg_2);
                        }

                        //sms
                        if (!empty($requestpayment->user->carrierCode) && !empty($requestpayment->user->phone))
                        {
                            if (!empty($t_rp_suc_ref_sms_temp->subject) && !empty($t_rp_suc_ref_sms_temp->body))
                            {
                                $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_suc_ref_sms_temp->subject);
                                $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_suc_ref_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_suc_ref_sms_temp_sub = str_replace('{uuid}', $unique_code, $t_rp_can_pen_en_sms_temp->subject);
                                $t_rp_suc_ref_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                            }
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{status}', ($request->status == 'Refund') ? 'Refunded' : $request->status, $t_rp_suc_ref_sms_temp_msg);

                            $t_rp_suc_ref_sms_temp_msg = str_replace('{amount}', moneyFormat($requestpayment->currency->symbol, formatNumber($request->subtotal)), $t_rp_suc_ref_sms_temp_msg);

                            $t_rp_suc_ref_sms_temp_msg = str_replace('{added/subtracted}', 'subtracted', $t_rp_suc_ref_sms_temp_msg);
                            $t_rp_suc_ref_sms_temp_msg = str_replace('{from/to}', 'from', $t_rp_suc_ref_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $t_rp_suc_ref_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('Request_From => Request: Blocked, DB Status: Pending');
                    $requestpayment         = RequestPayment::find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Sent Mail when request is 'blocked'
                    if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                    {
                        //Subject
                        $t_block_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                        //Body
                        $t_block_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $requestPaymentEmailTemp->body);
                    }
                    else
                    {
                        //Subject
                        $t_block_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                        //Body
                        $t_block_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                    }

                    $t_block_msg_1 = str_replace('{uuid}', $requestpayment->uuid, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_block_msg_1);
                    $t_block_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($requestpayment->user->email, $t_block_sub_1, $t_block_msg_1);
                    }

                    //sms
                    if (!empty($requestpayment->user->carrierCode) && !empty($requestpayment->user->phone))
                    {
                        if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                        }
                        $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $t_rp_can_pen_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($requestpayment->receiver))
                    {
                        if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                        {
                            //Subject
                            $t_block_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                            //Body
                            $t_block_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $requestPaymentEmailTemp->body);
                        }
                        else
                        {
                            //Subject
                            $t_block_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                            //Body
                            $t_block_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                        }

                        $t_block_msg_2 = str_replace('{uuid}', $requestpayment->uuid, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_block_msg_2);
                        $t_block_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_block_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($requestpayment->receiver->email, $t_block_sub_2, $t_block_msg_2);
                        }

                        //sms
                        if (!empty($requestpayment->receiver->carrierCode) && !empty($requestpayment->receiver->phone))
                        {
                            if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_en_sms_temp->body);
                            }
                            $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $t_rp_can_pen_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('Request_From => Request: Blocked, DB Status: Pending');
                    $requestpayment         = RequestPayment::find($request->transaction_reference_id);
                    $requestpayment->status = $request->status;
                    $requestpayment->save();

                    // Request_To
                    $transaction_creator = Transaction::where([
                        'user_id'                  => $request->user_id,
                        'end_user_id'              => isset($request->end_user_id) ? $request->end_user_id : null,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Request_From
                    $transaction_acceptor = Transaction::where([
                        'user_id'                  => isset($request->end_user_id) ? $request->end_user_id : null,
                        'end_user_id'              => $request->user_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Request_From,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Sent Mail when request is 'Pending'
                    if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                    {
                        //Subject
                        $t_pending_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                        //Body
                        $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $requestPaymentEmailTemp->body);
                    }
                    else
                    {
                        //Subject
                        $t_pending_sub_1 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                        //Body
                        $t_pending_msg_1 = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                    }

                    $t_pending_msg_1 = str_replace('{uuid}', $requestpayment->uuid, $t_pending_msg_1);
                    $t_pending_msg_1 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_pending_msg_1);
                    $t_pending_msg_1 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_1);

                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($requestpayment->user->email, $t_pending_sub_1, $t_pending_msg_1);
                    }

                    //sms
                    if (!empty($requestpayment->user->carrierCode) && !empty($requestpayment->user->phone))
                    {
                        if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_sms_temp->body);
                        }
                        else
                        {
                            $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                            $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->user->first_name . ' ' . $requestpayment->user->last_name, $t_rp_can_pen_en_sms_temp->body);
                        }
                        $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                        if (checkAppSmsEnvironment())
                        {
                            if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                            {
                                sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->user->carrierCode . $requestpayment->user->phone, $t_rp_can_pen_sms_temp_msg);
                            }
                        }
                    }

                    if (isset($requestpayment->receiver))
                    {
                        if (!empty($requestPaymentEmailTemp->subject) && !empty($requestPaymentEmailTemp->body))
                        {
                            //Subject
                            $t_pending_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $requestPaymentEmailTemp->subject);
                            //Body
                            $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $requestPaymentEmailTemp->body);
                        }
                        else
                        {

                            //Subject
                            $t_pending_sub_2 = str_replace('{uuid}', $requestpayment->uuid, $englishTempInfoRequestFromRequestToCancelPending->subject);
                            //Body
                            $t_pending_msg_2 = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $englishTempInfoRequestFromRequestToCancelPending->body);
                        }

                        $t_pending_msg_2 = str_replace('{uuid}', $requestpayment->uuid, $t_pending_msg_2);
                        $t_pending_msg_2 = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_pending_msg_2);
                        $t_pending_msg_2 = str_replace('{soft_name}', Session::get('name'), $t_pending_msg_2);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($requestpayment->receiver->email, $t_pending_sub_2, $t_pending_msg_2);
                        }

                        //sms
                        if (!empty($requestpayment->receiver->carrierCode) && !empty($requestpayment->receiver->phone))
                        {
                            if (!empty($t_rp_can_pen_sms_temp->subject) && !empty($t_rp_can_pen_sms_temp->body))
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_sms_temp->body);
                            }
                            else
                            {
                                $t_rp_can_pen_sms_temp_sub = str_replace('{uuid}', $requestpayment->uuid, $t_rp_can_pen_en_sms_temp->subject);
                                $t_rp_can_pen_sms_temp_msg = str_replace('{user_id/receiver_id}', $requestpayment->receiver->first_name . ' ' . $requestpayment->receiver->last_name, $t_rp_can_pen_en_sms_temp->body);
                            }
                            $t_rp_can_pen_sms_temp_msg = str_replace('{status}', ($requestpayment->status == 'Blocked') ? 'Cancelled' : $requestpayment->status, $t_rp_can_pen_sms_temp_msg);

                            if (checkAppSmsEnvironment())
                            {
                                if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                                {
                                    sendSMS(getNexmoDetails()->default_nexmo_phone_number, $requestpayment->receiver->carrierCode . $requestpayment->receiver->phone, $t_rp_can_pen_sms_temp_msg);
                                }
                            }
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        //Voucher_Created
        if ($request->type == 'Voucher Created')
        {
            if ($request->status == 'Blocked')
            {
                if ($t->status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Cancelled!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('Voucher => Request: Blocked, DB Status: Pending');
                    $voucher         = Voucher::find($request->transaction_reference_id);
                    $voucher->status = $request->status;
                    $voucher->save();

                    Transaction::where([
                        'user_id'                  => $voucher->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // creator_wallet entry update
                    $creator_wallet = Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $creator_wallet->balance + trim($request->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Blocked') //current status
                {
                    // dd('Voucher => Request: Pending, DB Status: Blocked');
                    $voucher         = Voucher::find($request->transaction_reference_id);
                    $voucher->status = $request->status;
                    $voucher->save();

                    Transaction::where([
                        'user_id'                  => $voucher->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // creator_wallet entry update
                    $creator_wallet = Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $creator_wallet->balance - trim($request->total, '-'),
                    ]);

                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success')
                {
                    // dd('Voucher => Request: Pending, DB Status: Success');
                    $voucher         = Voucher::find($request->transaction_reference_id);
                    $voucher->status = $request->status;
                    $voucher->save();

                    Transaction::where([
                        'user_id'                  => $voucher->user_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // creator_wallet entry update
                    $creator_wallet = Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $voucher->user_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $creator_wallet->balance - trim($request->total, '-'),
                    ]);
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }

        /**
         * Voucher Activated - Email Template
         */
        $voucher_email_temp = EmailTemplate::where([
            'temp_id'     => 7,
            'language_id' => Session::get('default_language'),
        ])->select('subject', 'body')->first();

        //Voucher_Activated
        if ($request->type == 'Voucher Activated')
        {
            if ($request->status == 'Pending')
            {
                if ($t->status == 'Pending') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Pending!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Success') //current status
                {
                    // dd('Voucher => Request: Blocked, DB Status: Pending');
                    $voucher         = Voucher::find($request->transaction_reference_id);
                    $voucher->status = $request->status;
                    $voucher->save();

                    Transaction::where([
                        'user_id'                  => $voucher->activator_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    //activator_wallet entry update
                    $activator_wallet = Wallet::where([
                        'user_id'     => $voucher->activator_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $voucher->activator_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $activator_wallet->balance - $request->total,
                    ]);

                    // Voucher Activation Mail
                    // Mail when, [ request: Pending, status: Success ]
                    if (isset($voucher_email_temp))
                    {
                        // subject
                        $t_pending_sub = str_replace('{uuid}', $voucher->uuid, $voucher_email_temp->subject);
                        // body
                        $t_pending_msg = str_replace('{activator_id}', $voucher->activator->first_name . ' ' . $voucher->activator->last_name, $voucher_email_temp->body);
                        $t_pending_msg = str_replace('{uuid}', $voucher->uuid, $t_pending_msg);
                        $t_pending_msg = str_replace('{status}', $voucher->status, $t_pending_msg);
                        $t_pending_msg = str_replace('{code}', $voucher->code, $t_pending_msg);
                        $t_pending_msg = str_replace('{amount}', $request->total, $t_pending_msg);
                        $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                        $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                        $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($voucher->activator->email, $t_pending_sub, $t_pending_msg);
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($t->status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Transaction is already Successfull!');
                    return redirect('admin/transactions');
                }
                elseif ($t->status == 'Pending') //current status
                {
                    // dd('Voucher => Request: Blocked, DB Status: Pending');
                    $voucher         = Voucher::find($request->transaction_reference_id);
                    $voucher->status = $request->status;
                    $voucher->save();

                    Transaction::where([
                        'user_id'                  => $voucher->activator_id,
                        'currency_id'              => $request->currency_id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //activator_wallet entry update
                    $activator_wallet = Wallet::where([
                        'user_id'     => $voucher->activator_id,
                        'currency_id' => $request->currency_id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $voucher->activator_id,
                        'currency_id' => $request->currency_id,
                    ])->update([
                        'balance' => $activator_wallet->balance + $request->total,
                    ]);

                    /*  Voucher Activation Mail
                    Mail when, [ request: Success, status: Pending ]
                     */
                    if (isset($voucher_email_temp))
                    {
                        // subject
                        $t_success_sub = str_replace('{uuid}', $voucher->uuid, $voucher_email_temp->subject);
                        // body
                        $t_success_msg = str_replace('{activator_id}', $voucher->activator->first_name . ' ' . $voucher->activator->last_name, $voucher_email_temp->body);
                        $t_success_msg = str_replace('{uuid}', $voucher->uuid, $t_success_msg);
                        $t_success_msg = str_replace('{status}', $voucher->status, $t_success_msg);
                        $t_success_msg = str_replace('{code}', $voucher->code, $t_success_msg);
                        $t_success_msg = str_replace('{amount}', $request->total, $t_success_msg);
                        $t_success_msg = str_replace('{added/subtracted}', 'added', $t_success_msg);
                        $t_success_msg = str_replace('{from/to}', 'to', $t_success_msg);
                        $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($voucher->activator->email, $t_success_sub, $t_success_msg);
                        }
                    }
                    $this->helper->one_time_message('success', 'Transaction Updated Successfully!');
                    return redirect('admin/transactions');
                }
            }
        }
    }
}
