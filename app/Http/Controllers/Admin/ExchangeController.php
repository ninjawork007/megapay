<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CurrencyExchangesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExchangeController extends Controller
{
    protected $helper;
    protected $exchange;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->exchange = new CurrencyExchange();
    }

    public function index(CurrencyExchangesDataTable $dataTable)
    {
        $data['menu'] = 'exchanges';

        // $data['defaultCurrency'] = $defaultCurrency = Currency::find(\Session::get('default_currency'));

        $data['exchanges_status'] = $exchanges_status = $this->exchange->select('status')->groupBy('status')->get();

        $data['exchanges_currency'] = $exchanges_currency = $this->exchange->join('wallets', function ($join)
        {
            $join->on('wallets.id', '=', 'currency_exchanges.from_wallet')->orOn('wallets.id', '=', 'currency_exchanges.to_wallet');
        })
            ->groupBy('wallets.currency_id')->select('wallets.currency_id', 'wallets.id as wallet_id')->get();
        // dd($exchanges_currency);

        if (isset($_GET['btn']))
        {
            $data['status']   = $_GET['status'];
            $data['currency'] = $_GET['currency'];
            $data['user']     = $user     = $_GET['user_id'];

            $data['getName'] = $getName = $this->exchange->getExchangesUserName($user);

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
        return $dataTable
        // ->with('defaultCurrencySymbol', $defaultCurrency->symbol)
        ->render('admin.exchange.list', $data);
    }

    public function exchangesUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->exchange->getExchangesUsersResponse($search);

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

    public function exchangeCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['exchanges'] = $exchanges = $this->exchange->getExchangesListForCsvPdf($from, $to, $status, $currency, $user);
        // dd($exchanges);

        $datas = [];
        if (!empty($exchanges))
        {
            foreach ($exchanges as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                $datas[$key]['User'] = $value->first_name . ' ' . $value->last_name;

                if ($value->type == 'Out')
                {
                    if ($value->amount > 0)
                    {
                        $datas[$key]['Amount'] = formatNumber($value->amount);
                    }
                }
                elseif ($value->type == 'In')
                {
                    if ($value->amount > 0)
                    {
                        $datas[$key]['Amount'] = formatNumber($value->amount);
                    }
                }
                $datas[$key]['Fees'] = ($value->fee == 0) ? "-" : formatNumber($value->fee);

                //Total start
                if ($value->type == 'Out')
                {
                    if (($value->fee + $value->amount) > 0)
                    {
                        $datas[$key]['Total'] = '-'.formatNumber($value->fee + $value->amount);
                    }
                    else
                    {
                        $datas[$key]['Total'] = '-';
                    }
                }
                elseif ($value->type == 'In')
                {
                    if (($value->fee + $value->amount) > 0)
                    {
                        $datas[$key]['Total'] = '-'.formatNumber($value->fee + $value->amount);
                    }
                    else
                    {
                        $datas[$key]['Total'] = '-';
                    }
                }
                //Total end

                $datas[$key]['Rate'] = moneyFormat($value->tc_symbol, formatNumber($value->exchange_rate));

                if ($value->type == 'Out')
                {
                    $datas[$key]['From'] = $value->fc_code;
                }
                else
                {
                    $datas[$key]['From'] = $value->fc_code;
                }

                if ($value->type == 'In')
                {
                    $datas[$key]['To'] = $value->tc_code;
                }
                else
                {
                    $datas[$key]['To'] = $value->tc_code;
                }
                $datas[$key]['Status'] = ($value->status == 'Blocked') ? 'Cancelled' : $value->status;
            }
        }
        else
        {
            $datas[0]['Date']   = '';
            $datas[0]['User']   = '';
            $datas[0]['Amount'] = '';
            $datas[0]['Fees']   = '';
            $datas[0]['Total']   = '';
            $datas[0]['Rate']   = '';
            $datas[0]['From']   = '';
            $datas[0]['To']     = '';
            $datas[0]['Status'] = '';
        }
        return Excel::create('exchanges_list_' . time() . '', function ($excel) use ($datas)
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

    public function exchangePdf()
    {
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;

        $to = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $currency = isset($_GET['currency']) ? $_GET['currency'] : null;

        $user = isset($_GET['user_id']) ? $_GET['user_id'] : null;

        $data['exchanges'] = $exchanges = $this->exchange->getExchangesListForCsvPdf($from, $to, $status, $currency, $user);

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

        $mpdf->WriteHTML(view('admin.exchange.exchanges_report_pdf', $data));

        $mpdf->Output('exchanges_report_' . time() . '.pdf', 'D');
    }

    public function edit($id)
    {
        $data['menu']     = 'exchanges';
        $data['exchange'] = $exchange = CurrencyExchange::find($id);
        // dd($exchange->type);

        $data['transaction'] = $transaction = Transaction::select('transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'charge_percentage', 'charge_fixed', 'uuid')
            ->where(['transaction_reference_id' => $exchange->id, 'uuid' => $exchange->uuid])
            ->whereIn('transaction_type_id', [Exchange_From, Exchange_To])
            ->first();
        // dd($transaction);

        return view('admin.exchange.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());

        if ($request->type == "Out")
        {
            if ($request->status == 'Success')
            {
                if ($request->transaction_status == 'Success') //current status
                {
                    $this->helper->one_time_message('success', 'Exchange is already Successfull!');
                    return redirect('admin/exchanges');
                }
                elseif ($request->transaction_status == 'Blocked')
                {
                    // dd('Transferred => Request: Success, DB Status: Blocked');
                    $exchange         = CurrencyExchange::find($request->id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    // Exchange_From
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // Exchange_To
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
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
                        'balance' => $from_wallet->balance - $request->total,
                    ]);

                    //receiver wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        'balance' => $to_wallet->balance + ($request->amount * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', 'Exchange Updated Successfully!');
                    return redirect('admin/exchanges');
                }
            }
            elseif ($request->status == 'Blocked')
            {
                if ($request->transaction_status == 'Blocked') //current status
                {
                    $this->helper->one_time_message('success', 'Exchange is already Blocked!');
                    return redirect('admin/exchanges');
                }
                elseif ($request->transaction_status == 'Success') //current status
                {
                    // dd('Transferred => Request: Blocked, DB Status: Success');
                    $exchange         = CurrencyExchange::find($request->id);
                    $exchange->status = $request->status;
                    $exchange->save();

                    // // Exchange_From
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->fromWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => $request->transaction_type_id,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    // // Exchange_To
                    Transaction::where([
                        'user_id'                  => $request->user_id,
                        'currency_id'              => $exchange->toWallet->currency->id,
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id'      => Exchange_To,
                    ])->update([
                        'status' => $request->status,
                    ]);

                    //sent wallet entry update
                    $from_wallet = Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->select('balance')->first();
                    // dd($from_wallet);

                    Wallet::where([
                        'id'          => $exchange->from_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->fromWallet->currency->id,
                    ])->update([
                        'balance' => $from_wallet->balance + $request->total,
                    ]);

                    //received wallet entry update
                    $to_wallet = Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->select('balance')->first();

                    Wallet::where([
                        'id'          => $exchange->to_wallet,
                        'user_id'     => $request->user_id,
                        'currency_id' => $exchange->toWallet->currency->id,
                    ])->update([
                        'balance' => $to_wallet->balance - ($request->amount * $exchange->exchange_rate),
                    ]);
                    $this->helper->one_time_message('success', 'Exchange Updated Successfully!');
                    return redirect('admin/exchanges');
                }
            }
        }
        // elseif ($request->type == "In")
        // {
        //     if ($request->status == 'Success')
        //     {
        //         if ($request->transaction_status == 'Success') //current status
        //         {
        //             // dd('1');
        //             $this->helper->one_time_message('success', 'Exchange is already Successfull!');
        //         }
        //         elseif ($request->transaction_status == 'Blocked')
        //         {
        //             // dd('Transferred => Request: Success, DB Status: Blocked');
        //             $exchange         = CurrencyExchange::find($request->id);
        //             $exchange->status = $request->status;
        //             $exchange->save();

        //             // Exchange_From
        //             Transaction::where([
        //                 'user_id'                  => $request->user_id,
        //                 'currency_id'              => $exchange->fromWallet->currency->id,
        //                 'transaction_reference_id' => $request->transaction_reference_id,
        //                 'transaction_type_id'      => $request->transaction_type_id,
        //             ])->update([
        //                 'status' => $request->status,
        //             ]);

        //             // Exchange_To
        //             Transaction::where([
        //                 'user_id'                  => $request->user_id,
        //                 'currency_id'              => $exchange->toWallet->currency->id,
        //                 'transaction_reference_id' => $request->transaction_reference_id,
        //                 'transaction_type_id'      => Exchange_To,
        //             ])->update([
        //                 'status' => $request->status,
        //             ]);

        //             //sender wallet entry update
        //             $from_wallet = Wallet::where([
        //                 'id'          => $exchange->from_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->fromWallet->currency->id,
        //             ])->select('balance')->first();

        //             Wallet::where([
        //                 'id'          => $exchange->from_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->fromWallet->currency->id,
        //             ])->update([
        //                 'balance' => $from_wallet->balance - $request->amount,
        //             ]);

        //             //receiver wallet entry update
        //             $to_wallet = Wallet::where([
        //                 'id'          => $exchange->to_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->toWallet->currency->id,
        //             ])->select('balance')->first();
        //             // dd($to_wallet);

        //             Wallet::where([
        //                 'id'          => $exchange->to_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->toWallet->currency->id,
        //             ])->update([
        //                 'balance' => $to_wallet->balance + ($request->amount / $exchange->fromWallet->currency->rate),
        //             ]);
        //             $this->helper->one_time_message('success', 'Exchange Updated Successfully!');
        //         }
        //     }
        //     elseif ($request->status == 'Blocked')
        //     {
        //         if ($request->transaction_status == 'Blocked') //current status
        //         {
        //             $this->helper->one_time_message('success', 'Exchange is already Blocked!');
        //         }
        //         elseif ($request->transaction_status == 'Success') //current status
        //         {
        //             // dd('Transferred => Request: Blocked, DB Status: Success');
        //             $exchange         = CurrencyExchange::find($request->id);
        //             $exchange->status = $request->status;
        //             $exchange->save();

        //             // Exchange_From
        //             Transaction::where([
        //                 'user_id'                  => $request->user_id,
        //                 'currency_id'              => $exchange->fromWallet->currency->id,
        //                 'transaction_reference_id' => $request->transaction_reference_id,
        //                 'transaction_type_id'      => $request->transaction_type_id,
        //             ])->update([
        //                 'status' => $request->status,
        //             ]);

        //             // Exchange_To
        //             Transaction::where([
        //                 'user_id'                  => $request->user_id,
        //                 'currency_id'              => $exchange->toWallet->currency->id,
        //                 'transaction_reference_id' => $request->transaction_reference_id,
        //                 'transaction_type_id'      => Exchange_To,
        //             ])->update([
        //                 'status' => $request->status,
        //             ]);

        //             //sent wallet entry update
        //             $from_wallet = Wallet::where([
        //                 'id'          => $exchange->from_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->fromWallet->currency->id,
        //             ])->select('balance')->first();
        //             // dd($from_wallet);

        //             Wallet::where([
        //                 'id'          => $exchange->from_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->fromWallet->currency->id,
        //             ])->update([
        //                 'balance' => $from_wallet->balance + $request->amount,
        //             ]);

        //             //received wallet entry update
        //             $to_wallet = Wallet::where([
        //                 'id'          => $exchange->to_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->toWallet->currency->id,
        //             ])->select('balance')->first();
        //             // dd($to_wallet);

        //             Wallet::where([
        //                 'id'          => $exchange->to_wallet,
        //                 'user_id'     => $request->user_id,
        //                 'currency_id' => $exchange->toWallet->currency->id,
        //             ])->update([
        //                 'balance' => $to_wallet->balance - ($request->amount / $exchange->fromWallet->currency->rate),
        //             ]);
        //             $this->helper->one_time_message('success', 'Exchange Updated Successfully!');
        //         }
        //     }
        // }
    }
}
