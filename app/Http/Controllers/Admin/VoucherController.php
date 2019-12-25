<?php

namespace App\Http\Controllers\Admin;

use App;
use App\DataTables\Admin\VouchersDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\EmailTemplate;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Session;

class VoucherController extends Controller
{
    protected $helper;
    protected $email;
    protected $voucher;

    public function __construct()
    {
        $this->helper  = new Common();
        $this->email   = new EmailController();
        $this->voucher = new Voucher();
    }

    public function index(VouchersDataTable $dataTable)
    {
        $data['menu'] = 'vouchers';

        $data['vouchers_status']   = $vouchers_status   = $this->voucher->select('status')->groupBy('status')->get();
        $data['vouchers_currency'] = $vouchers_currency = $this->voucher->select('currency_id')->groupBy('currency_id')->get();

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['user']     = $user     = $_GET['user_id'];

            $data['getName'] = $getName = $this->voucher->getVouchersUserName($user);

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
            $data['from'] = null;
            $data['to']   = null;

            $data['status']   = 'all';
            $data['currency'] = 'all';
            $data['user']     = null;
        }
        return $dataTable->render('admin.voucher.list', $data);
    }

    public function voucherCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['vouchers'] = $vouchers = $this->voucher->getVouchersListForCsvPdf($from, $to, $status, $currency, $user);
        // dd($vouchers);

        $datas = [];
        if (!empty($vouchers))
        {
            foreach ($vouchers as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                $datas[$key]['User'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";

                $datas[$key]['Code'] = $value->code;

                $datas[$key]['Amount'] = formatNumber($value->amount);

                $datas[$key]['Currency'] = $value->currency->code;

                $datas[$key]['Redeemed'] = $value->redeemed;

                $datas[$key]['Status'] = ($value->status == 'Blocked') ? 'Cancelled' : $value->status;
            }
        }
        else
        {
            $datas[0]['Date']     = '';
            $datas[0]['User']     = '';
            $datas[0]['Amount']   = '';
            $datas[0]['Code']     = '';
            $datas[0]['Currency'] = '';
            $datas[0]['Redeemed'] = '';
            $datas[0]['Status']   = '';
        }
        // dd($datas);

        return Excel::create('vouchers_list_' . time() . '', function ($excel) use ($datas)
        {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->sheet('mySheet', function ($sheet) use ($datas)
            {
                $sheet->cells('A1:F1', function ($cells)
                {
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($datas);
            });
        })->download();
    }

    public function voucherPdf()
    {
        $data['company_logo'] = \Session::get('company_logo');

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['vouchers'] = $vouchers = $this->voucher->getVouchersListForCsvPdf($from, $to, $status, $currency, $user);

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

        $mpdf->WriteHTML(view('admin.voucher.vouchers_report_pdf', $data));

        $mpdf->Output('vouchers_report_' . time() . '.pdf', 'D');
    }

    public function vouchersUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->voucher->getVouchersUsersResponse($search);

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
        $data['menu']    = 'vouchers';
        $data['voucher'] = $voucher = Voucher::find($id);
        // dd($voucher);

        $data['transaction'] = $transaction = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'charge_percentage', 'charge_fixed', 'uuid')
            ->where(['transaction_reference_id' => $voucher->id, 'status' => $voucher->status])
            ->whereIn('transaction_type_id', [Voucher_Created, Voucher_Activated])
            ->orderBy('id', 'desc')
            ->first();
        // dd($transaction);

        return view('admin.voucher.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->transaction_type);

        //Voucher_Activated
        if ($request->transaction_type == 'Voucher_Activated')
        {
            if ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'Voucher is already Pending!');
                }
                elseif ($request->transaction_status == 'Success')
                {
                    // dd('Voucher => Request: Pending, DB Status: Success');
                    $voucher         = Voucher::find($request->id);
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
                        'balance' => $activator_wallet->balance - $request->amount,
                    ]);

                    // Voucher Activation Mail
                    // Mail when, [ request: Pending, status: Success ]
                    $t_pending_temp = EmailTemplate::where([
                        'temp_id'     => 7,
                        'language_id' => Session::get('default_language'),
                    ])->select('subject', 'body')->first();

                    if (isset($t_pending_temp))
                    {
                        // subject
                        $t_pending_sub = str_replace('{uuid}', $voucher->uuid, $t_pending_temp->subject);
                        // body
                        $t_pending_msg = str_replace('{activator_id}', $voucher->activator->first_name . ' ' . $voucher->activator->last_name, $t_pending_temp->body);
                        $t_pending_msg = str_replace('{uuid}', $voucher->uuid, $t_pending_msg);
                        $t_pending_msg = str_replace('{status}', $voucher->status, $t_pending_msg);
                        $t_pending_msg = str_replace('{code}', $voucher->code, $t_pending_msg);
                        $t_pending_msg = str_replace('{amount}', $request->amount, $t_pending_msg);
                        $t_pending_msg = str_replace('{added/subtracted}', 'subtracted', $t_pending_msg);
                        $t_pending_msg = str_replace('{from/to}', 'from', $t_pending_msg);
                        $t_pending_msg = str_replace('{soft_name}', Session::get('name'), $t_pending_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($voucher->activator->email, $t_pending_sub, $t_pending_msg);
                        }
                    }
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Voucher is already Successfull!');
                }
                elseif ($request->transaction_status == 'Pending') //current status
                {
                    // dd('Voucher => Request: Success, DB Status: Pending');
                    $voucher         = Voucher::find($request->id);
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
                        'balance' => $activator_wallet->balance + $request->amount,
                    ]);

                    /*  Voucher Activation Mail
                    Mail when, [ request: Success, status: Pending ]
                     */
                    $t_success_temp = EmailTemplate::where([
                        'temp_id'     => 7,
                        'language_id' => Session::get('default_language'),
                    ])->select('subject', 'body')->first();

                    if (isset($t_success_temp))
                    {
                        // subject
                        $t_success_sub = str_replace('{uuid}', $voucher->uuid, $t_success_temp->subject);
                        // body
                        $t_success_msg = str_replace('{user_id}', $voucher->activator->first_name . ' ' . $voucher->activator->last_name, $t_success_temp->body);
                        $t_success_msg = str_replace('{uuid}', $voucher->uuid, $t_success_msg);
                        $t_success_msg = str_replace('{status}', $voucher->status, $t_success_msg);
                        $t_success_msg = str_replace('{code}', $voucher->code, $t_success_msg);
                        $t_success_msg = str_replace('{amount}', $request->amount, $t_success_msg);
                        $t_success_msg = str_replace('{added/subtracted}', 'added', $t_success_msg);
                        $t_success_msg = str_replace('{from/to}', 'to', $t_success_msg);
                        $t_success_msg = str_replace('{soft_name}', Session::get('name'), $t_success_msg);

                        if (checkAppMailEnvironment())
                        {
                            $this->email->sendEmail($voucher->activator->email, $t_success_sub, $t_success_msg);
                        }
                    }
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                }
            }
        }
        // Voucher_Created
        if ($request->transaction_type == 'Voucher_Created')
        {
            if ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked')
                {
                    $this->helper->one_time_message('success', 'Voucher is already Blocked!');
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    // dd('Voucher => Request: Blocked, DB Status: Pending');
                    $voucher         = Voucher::find($request->id);
                    $voucher->status = $request->status;
                    // dd($voucher);
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
                        'balance' => $creator_wallet->balance + $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                }
            }
            elseif ($request->status == 'Pending')
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'Voucher is already Pending!');
                }
                elseif ($request->transaction_status == 'Success')
                {
                    // dd('Voucher => Request: Pending, DB Status: Success');
                    $voucher         = Voucher::find($request->id);
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
                        'balance' => $creator_wallet->balance - trim($request->amount, '-'),
                    ]);
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    // dd('Voucher => Request: Pending, DB Status: Blocked');
                    $voucher         = Voucher::find($request->id);
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
                        'balance' => $creator_wallet->balance - $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Voucher Updated Successfully!');
                }
            }
        }
        return redirect('admin/vouchers');
    }

}
