<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\CurrencyExchange;
use App\User;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;

class CurrencyExchangesDataTable extends DataTable
{
    public function ajax()
    {
        $q = $this->query();
        return datatables()
            ->of($q)
            ->editColumn('created_at', function ($exchange)
            {
                return dateFormat($exchange->created_at);
            })
            ->editColumn('user_id', function ($exchange)
            {
                $sender = $exchange->first_name.' '.$exchange->last_name;

                $senderWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $exchange->user_id) . '">'.$sender.'</a>' : $sender;
                return $senderWithLink;
            })
            ->editColumn('amount', function ($exchange)
            {
                $amount = moneyFormat($this->defaultCurrencySymbol, formatNumber($exchange->amount));;
                return $amount;
            })
            ->editColumn('fee', function ($exchange)
            {
                return ($exchange->fee == 0) ? '-' : formatNumber($exchange->fee);
            })
            ->addColumn('total', function ($exchange)
            {
                if($exchange->type == 'Out')
                {
                    if (($exchange->fee + $exchange->amount) > 0)
                    {
                      $total = '<span class="text-red">-'. moneyFormat($this->defaultCurrencySymbol, formatNumber($exchange->fee + $exchange->amount)).'</span>';
                    }
                    else
                    {
                      $total = '<span class="text-green">+'. moneyFormat($this->defaultCurrencySymbol, formatNumber($exchange->fee + $exchange->amount)). '</span>';
                    }
                }
                elseif($exchange->type == 'In')
                {
                    if (($exchange->fee + $exchange->amount) > 0)
                    {
                      $total = '<span class="text-green">+'. moneyFormat($exchange->tc_symbol, formatNumber($exchange->fee + $exchange->amount)). '</span>';
                    }
                    else
                    {
                      $total = '<span class="text-red">'. moneyFormat($exchange->tc_symbol, formatNumber($exchange->fee + $exchange->amount)). '</span>';
                    }
                }
                return $total;
            })
            ->editColumn('exchange_rate', function ($exchange)
            {
                return moneyFormat($exchange->tc_symbol, formatNumber($exchange->exchange_rate));
            })
            ->addColumn('fc_code', function ($exchange)
            {
                return $exchange->fc_code;
            })
            ->addColumn('tc_code', function ($exchange)
            {
                return $exchange->tc_code;
            })
            ->editColumn('status', function ($exchange)
            {
                if ($exchange->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($exchange->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($exchange->status == 'Refund')
                {
                    $status = '<span class="label label-warning">Refunded</span>';
                }
                elseif ($exchange->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($exchange)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_exchange')) ?
                '<a href="' . url('admin/exchange/edit/' . $exchange->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['user_id','total', 'status', 'action','amount'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status = $_GET['status'];
            $currency = $_GET['currency'];
            $user = $_GET['user_id'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query    = (new CurrencyExchange())->getExchangesList($from, $to, $status, $currency, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query    = (new CurrencyExchange())->getExchangesList($from, $to, $status, $currency, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $currency = 'all';
            $user     = null;
            $query    = (new CurrencyExchange())->getExchangesList($from, $to, $status, $currency, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'uuid', 'name' => 'uuid', 'title' => 'UUID', 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'user_id', 'name' => 'user_id', 'title' => 'User'])

            ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'])

            ->addColumn(['data' => 'fee', 'name' => 'fee', 'title' => 'Fees'])

            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => 'Total']) //custom

            ->addColumn(['data' => 'exchange_rate', 'name' => 'exchange_rate', 'title' => 'Rate'])

            ->addColumn(['data' => 'fc_code', 'name' => 'fc_code', 'title' => 'From']) //custom

            ->addColumn(['data' => 'tc_code', 'name' => 'tc_code', 'title' => 'To']) //custom

            ->addColumn(['data' => 'status', 'name' => 'status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
