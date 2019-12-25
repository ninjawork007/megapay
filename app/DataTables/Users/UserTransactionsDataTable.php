<?php

namespace App\DataTables\Users;

use App\Models\Transaction;
use App\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Services\DataTable;

class UserTransactionsDataTable extends DataTable
{
    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->addColumn('action', function ($transaction)
            {
                $show = '<a href="' . url('transactions/' . $transaction->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>&nbsp;';
                return $show;
            })
            ->addColumn('total_fees', function ($transaction)
            {
                return $transaction->charge_percentage . ' + ' . $transaction->charge_fixed;
            })
            ->editColumn('total', function ($transaction)
            {
                return $transaction->total;
            })
            ->editColumn('payment_method_name', function ($transaction)
            {
                if ($transaction->payment_method_name == null)
                {
                    return "&#45;";
                }
                else
                {
                    return $transaction->payment_method_name;
                }
            })
            ->editColumn('status', function ($transaction)
            {
                if ($transaction->status == 'Success')
                {
                    return "<span class='label label-success'>Success</span>";
                }

                if ($transaction->status == 'Pending')
                {
                    return "<span class='label label-primary'>Pending</span>";
                }

                if ($transaction->status == 'Refund')
                {
                    return "<span class='label label-success'>Refund</span>";
                }

                if ($transaction->status == 'Blocked')
                {
                    return "<span class='label label-danger'>Blocked</span>";
                }
            })
            ->editColumn('t_created_at', function ($transaction)
            {
                return dateFormat($transaction->t_created_at);
            })
            ->editColumn('total', function ($transaction)
            {
                $no = decimalFormat($transaction->total);
                if ($no > 0)
                {
                    $no = "<span class='text-green'>+$no</span>";
                }
                else
                {
                    $no = "<span class='text-red'>$no</span>";
                }
                return $no;
            })
            ->editColumn('f_name', function ($user)
            {
                return $user->f_name . ' ' . $user->l_name;
            })
            ->rawColumns(['status', 'action', 'total'])
            ->make(true);
    }

    public function query()
    {
        $query = Transaction::join('currencies', function ($join)
            {
            $join->on('currencies.id', '=', 'transactions.currency_id');
            })
            ->join('users', function ($join)
            {
                $join->on('users.id', '=', 'transactions.user_id');
            })
            ->leftJoin('payment_methods', function ($join)
            {
                $join->on('payment_methods.id', '=', 'transactions.payment_method_id');
            })
            ->where(['transactions.user_id' => Auth::user()->id])
            ->orderBy('id', 'desc')
            ->select([
                'transactions.id as id',

                'users.first_name as f_name',

                'users.last_name as l_name',

                'payment_methods.name as payment_method_name',

                'transactions.type as type',

                'transactions.subtotal as subtotal',

                'transactions.charge_percentage as charge_percentage',

                'transactions.charge_fixed as charge_fixed',

                'transactions.total as total',

                'transactions.status as status',

                'transactions.created_at as t_created_at',

                'currencies.code as curr_code',

                'currencies.symbol as curr_symbol',
            ]);
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()

            ->addColumn(['data' => 'payment_method_name', 'name' => 'payment_methods.name', 'title' => 'Payment Method'])

            ->addColumn(['data' => 'type', 'name' => 'transactions.type', 'title' => 'Type'])

            ->addColumn(['data' => 'subtotal', 'name' => 'transactions.subtotal', 'title' => 'Subtotal'])

            ->addColumn(['data' => 'total_fees', 'name' => 'total_fees', 'title' => 'Charge(% + Fixed)'])

            ->addColumn(['data' => 'total', 'name' => 'transactions.total', 'title' => 'Sum'])

            ->addColumn(['data' => 'curr_code', 'name' => 'currencies.code', 'title' => 'Currency'])

            ->addColumn(['data' => 'status', 'name' => 'transactions.status', 'title' => 'Status'])

            ->addColumn(['data' => 't_created_at', 'name' => 'transactions.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters($this->getBuilderParameters());
    }

    protected function getColumns()
    {
        return [
            'id',
            'add your columns',
            'created_at',
            'updated_at',
        ];
    }

    protected function filename()
    {
        return 'usertransactionsdatatable_' . time();
    }
}
