<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\DepositsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DepositController extends Controller
{
    protected $helper;
    protected $deposit;

    public function __construct()
    {
        $this->helper  = new Common();
        $this->deposit = new Deposit();
    }

    public function index(DepositsDataTable $dataTable)
    {
        $data['menu'] = 'deposits';

        $data['d_status']     = $d_status     = $this->deposit->select('status')->groupBy('status')->get();
        $data['d_currencies'] = $d_currencies = $this->deposit->with('currency:id,code')->select('currency_id')->groupBy('currency_id')->get();
        $data['d_pm']         = $d_pm         = $this->deposit->with('payment_method:id,name')->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['pm']       = $_GET['payment_methods'];
            $data['user']     = $user     = $_GET['user_id'];

            $data['getName'] = $getName = $this->deposit->getDepositsUsersName($user);

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
            $data['from']     = null;
            $data['to']       = null;
            $data['status']   = 'all';
            $data['currency'] = 'all';
            $data['pm']       = 'all';
            $data['user']     = null;
        }
        return $dataTable->render('admin.deposits.list', $data);
    }

    public function depositsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->deposit->getDepositsUsersResponse($search);

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

    public function depositCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        // dd($from);

        $to = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;
        // dd($to);

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $pm = isset($_GET['payment_methods']) ? $_GET['payment_methods'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['deposits'] = $deposits = $this->deposit->getDepositsListForCsvPdf($from, $to, $status, $currency, $pm, $user);
        // dd($deposits);

        $datas = [];
        if (!empty($deposits))
        {
            foreach ($deposits as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                $datas[$key]['User'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";

                $datas[$key]['Amount'] = formatNumber($value->amount);

                $datas[$key]['Fees'] = ($value->charge_percentage == 0) && ($value->charge_fixed == 0) ? '-' : formatNumber($value->charge_percentage + $value->charge_fixed);

                $datas[$key]['Total'] = '+'.formatNumber($value->amount + ($value->charge_percentage + $value->charge_fixed));

                $datas[$key]['Currency'] = $value->currency->code;

                $datas[$key]['Payment Method'] = ($value->payment_method->name == 'Mts' ? getCompanyName() : $value->payment_method->name);

                $datas[$key]['Status'] = ($value->status == 'Blocked') ? 'Cancelled' : $value->status;
            }
        }
        else
        {
            $datas[0]['Date']           = '';
            $datas[0]['User']           = '';
            $datas[0]['Amount']         = '';
            $datas[0]['Fees']           = '';
            $datas[0]['Total']          = '';
            $datas[0]['Currency']       = '';
            $datas[0]['Payment Method'] = '';
            $datas[0]['Status']         = '';
        }
        // dd($datas);

        return Excel::create('deposit_list_' . time() . '', function ($excel) use ($datas)
        {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->sheet('mySheet', function ($sheet) use ($datas)
            {
                $sheet->cells('A1:H1', function ($cells)
                {
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($datas);
            });
        })->download();
    }

    public function depositPdf()
    {
        // $data['company_logo'] = \Session::get('company_logo');
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;

        $to = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $pm = isset($_GET['payment_methods']) ? $_GET['payment_methods'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['deposits'] = $deposits = $this->deposit->getDepositsListForCsvPdf($from, $to, $status, $currency, $pm, $user);

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

        $mpdf->WriteHTML(view('admin.deposits.deposits_report_pdf', $data));

        $mpdf->Output('deposits_report_' . time() . '.pdf', 'D');
    }

    public function edit($id)
    {
        $data['menu']    = 'deposits';
        $data['deposit'] = $deposit = Deposit::find($id);
        // dd($deposit);

        $data['transaction'] = $transaction = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage')
            ->where(['transaction_reference_id' => $deposit->id, 'status' => $deposit->status, 'transaction_type_id' => Deposit])
            ->first();
        // dd($transaction);

        return view('admin.deposits.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());

        //Deposit
        if ($request->transaction_type == 'Deposit')
        {
            if ($request->status == 'Pending') //requested status
            {
                if ($request->transaction_status == 'Pending')
                {
                    $this->helper->one_time_message('success', 'Deposit is already Pending!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Success')
                {
                    // dd('current status: Success, doing Pending');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    $tt = Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    // dd($tt);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->update([
                        'balance' => $current_balance->balance - $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    // dd('current status: blocked, doing pending');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
            }
            elseif ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Deposit is already Successfull!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Blocked') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->select('balance')->first();

                    $update_wallet_for_deposit = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->update([
                        'balance' => $current_balance->balance + $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Pending')
                {
                    // dd('current status: Pending, doing Success');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    // dd();
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->update([
                        'balance' => $current_balance->balance + $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Deposit is already Blocked!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Pending') //current status
                {
                    // dd('current status: Pending, doing Blocked');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
                elseif ($request->transaction_status == 'Success') //current status
                {
                    // dd('current status: Success, doing Blocked');
                    $deposits         = Deposit::find($request->id);
                    $deposits->status = $request->status;
                    $deposits->save();

                    Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    $current_balance = Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->select('balance')->first();

                    Wallet::where([
                        'user_id'     => $request->user_id,
                        'currency_id' => $request->currency_id,
                        // 'is_default'  => 'Yes',
                    ])->update([
                        'balance' => $current_balance->balance - $request->amount,
                    ]);
                    $this->helper->one_time_message('success', 'Deposit Updated Successfully!');
                    return redirect('admin/deposits');
                }
            }
        }
    }
}
