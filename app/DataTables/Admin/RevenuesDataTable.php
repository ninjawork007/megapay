<?php

namespace App\DataTables\Admin;

use App\Models\Transaction;
use Yajra\DataTables\Services\DataTable;

class RevenuesDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($revenue)
            {
                return dateFormat($revenue->created_at);
            })
            ->editColumn('transaction_type_id', function ($revenue)
            {
                return ($revenue->transaction_type->name == "Withdrawal") ? "Payout" : str_replace('_', ' ', $revenue->transaction_type->name);
            })
            ->editColumn('charge_percentage', function ($revenue)
            {
                return ($revenue->charge_percentage == 0) ?  '-' : formatNumber($revenue->charge_percentage);
            })
            ->editColumn('charge_fixed', function ($revenue)
            {
                return ($revenue->charge_fixed == 0) ?  '-' : formatNumber($revenue->charge_fixed);
            })
            ->addColumn('total', function ($revenue)
            {
                $total = ($revenue->charge_percentage == 0) && ($revenue->charge_fixed == 0) ? '-' : $revenue->charge_percentage + $revenue->charge_fixed;
                if ($total > 0)
                {
                    $total = '<td><span class="text-green">+' . formatNumber($total) . '</span></td>';
                }
                else
                {
                    $total = '<td><span class="text-red">' . ($total) . '</span></td>';
                }
                return $total;
            })
            ->editColumn('currency_id', function ($revenue)
            {
                return $revenue->currency->code;
            })
            ->editColumn('status', function ($revenue)
            {
                if ($revenue->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($revenue->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($revenue->status == 'Refund')
                {
                    $status = '<span class="label label-warning">Refunded</span>';
                }
                elseif ($revenue->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->rawColumns(['total', 'status'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $currency = $_GET['currency'];
            $type     = $_GET['type'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Transaction())->getRevenuesList($from, $to, $currency, $type);
            }
            else
            {
                $from         = setDateForDb($_GET['from']);
                $to           = setDateForDb($_GET['to']);
                $query = (new Transaction())->getRevenuesList($from, $to, $currency, $type);
            }
        }
        else
        {
            $from     = null;
            $to       = null;
            $currency = 'all';
            $type     = 'all';
            $query = (new Transaction())->getRevenuesList($from, $to, $currency, $type);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'transactions.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'transactions.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'transaction_type_id', 'name' => 'transaction_type.name', 'title' => 'Transaction Type']) //relation

            ->addColumn(['data' => 'charge_percentage', 'name' => 'transactions.charge_percentage', 'title' => 'Percentage Charge'])

            ->addColumn(['data' => 'charge_fixed', 'name' => 'transactions.charge_fixed', 'title' => 'Fixed Charge'])

            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => 'Total']) //custom

            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => 'Currency']) //relation

            ->addColumn(['data' => 'status', 'name' => 'transactions.status', 'title' => 'Status'])
            ->parameters($this->getBuilderParameters());
    }
}
