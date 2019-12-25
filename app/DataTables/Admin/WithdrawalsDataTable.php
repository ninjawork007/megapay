<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Withdrawal;
use Yajra\DataTables\Services\DataTable;

class WithdrawalsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($withdrawal)
            {
                return dateFormat($withdrawal->created_at);
            })
            ->addColumn('user_id', function ($withdrawal)
            {
                $sender = isset($withdrawal->user) ? $withdrawal->user->first_name.' '.$withdrawal->user->last_name :"-";

                $senderWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $withdrawal->user->id) . '">'.$sender.'</a>' : $sender;
                return $senderWithLink;
            })
            ->editColumn('amount', function ($withdrawal)
            {
                return formatNumber($withdrawal->amount);
            })
            ->addColumn('fees', function ($withdrawal)
            {
                return ($withdrawal->charge_percentage == 0) && ($withdrawal->charge_fixed == 0) ? '-' : formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed);
            })
            ->addColumn('total', function ($withdrawal)
            {
                if (($withdrawal->charge_percentage + $withdrawal->charge_fixed + $withdrawal->amount) > 0)
                {
                    $total = '<td><span class="text-red">-' . formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed + $withdrawal->amount) . '</span></td>';
                }
                else
                {
                    $total = '<td><span class="text-greem">+' . formatNumber($withdrawal->charge_percentage + $withdrawal->charge_fixed + $withdrawal->amount) . '</span></td>';
                }
                return $total;
            })
            ->editColumn('currency_id', function ($withdrawal)
            {
                return $withdrawal->currency->code;
            })
            ->editColumn('payment_method_id', function ($withdrawal)
            {
                return ($withdrawal->payment_method->name == "Mts") ? getCompanyName() : $withdrawal->payment_method->name;
                // return $withdrawal->payment_method_id;
            })
            ->editColumn('payment_method_info', function ($withdrawal)
            {
                if ($withdrawal->payment_method->name != "Bank")
                {
                    $data =  !empty($withdrawal->payment_method_info) ? $withdrawal->payment_method_info : '-';
                }
                else
                {
                    $data = !empty($withdrawal->withdrawal_detail) ?
                    $withdrawal->withdrawal_detail->account_name.' '.'('.('*****'.substr($withdrawal->withdrawal_detail->account_number,-4)).')'.' '.$withdrawal->withdrawal_detail->bank_name : '-';
                }
                return $data;
            })
            ->editColumn('status', function ($withdrawal)
            {
                if ($withdrawal->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($withdrawal->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($withdrawal->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($withdrawal)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_withdrawal')) ?
                '<a href="' . url('admin/withdrawals/edit/' . $withdrawal->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['user_id','total', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status   = $_GET['status'];
            $currency = $_GET['currency'];
            $pm       = $_GET['payment_methods'];
            $user     = $_GET['user_id'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Withdrawal())->getWithdrawalsList($from, $to, $status, $currency, $pm, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new Withdrawal())->getWithdrawalsList($from, $to, $status, $currency, $pm, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $currency = 'all';
            $pm       = 'all';
            $user     = null;
            $query    = (new Withdrawal())->getWithdrawalsList($from, $to, $status, $currency, $pm, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'withdrawals.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'withdrawals.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'uuid', 'name' => 'withdrawals.uuid', 'title' => 'UUID', 'visible' => false])

            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => 'User','visible' => false])//relation
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => 'User'])//relation

            ->addColumn(['data' => 'amount', 'name' => 'withdrawals.amount', 'title' => 'Amount'])

            ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => 'Fees']) //custom
            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => 'Total']) //custom

            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => 'Currency'])//relation
            ->addColumn(['data' => 'payment_method_id', 'name' => 'payment_method.name', 'title' => 'Payment Method'])//relation

            ->addColumn(['data' => 'payment_method_info', 'name' => 'withdrawals.payment_method_info', 'title' => 'Method Info'])
            ->addColumn(['data' => 'status', 'name' => 'withdrawals.status', 'title' => 'Status'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
