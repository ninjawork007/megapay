<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Voucher;
use App\User;
use Yajra\DataTables\Services\DataTable;

class VouchersDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())

            ->editColumn('created_at', function ($voucher)
            {
                return dateFormat($voucher->created_at);
            })
            ->editColumn('user_id', function ($voucher)
            {
                $user = isset($voucher->user) ? $voucher->user->first_name.' '.$voucher->user->last_name :"-";

                $userWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $voucher->user->id) . '">'.$user.'</a>' : $user;
                return $userWithLink;
            })
            ->editColumn('amount', function ($voucher)
            {
                if ($voucher->amount > 0)
                {
                    $amount = '<td><span class="text-green">+' . formatNumber($voucher->amount) . '</span></td>';
                }
                else
                {
                    $amount = '<td><span class="text-red">' . formatNumber($voucher->amount) . '</span></td>';
                }
                return $amount;
            })
            ->editColumn('currency_id', function ($voucher)
            {
                return $voucher->currency->code;
            })
            ->editColumn('status', function ($voucher)
            {
                if ($voucher->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($voucher->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($voucher->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($voucher)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_voucher')) ?
                '<a href="' . url('admin/vouchers/edit/' . $voucher->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['user_id','amount','status','action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status   = $_GET['status'];
            $currency = $_GET['currency'];
            $user     = $_GET['user_id'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Voucher())->getVouchersList($from, $to, $status, $currency, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new Voucher())->getVouchersList($from, $to, $status, $currency, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $currency = 'all';
            $user     = null;
            $query    = (new Voucher())->getVouchersList($from, $to, $status, $currency, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'vouchers.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'vouchers.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'user_id', 'name' => 'vouchers.user_id', 'title' => 'User'])

            ->addColumn(['data' => 'code', 'name' => 'vouchers.code', 'title' => 'Code'])

            ->addColumn(['data' => 'amount', 'name' => 'vouchers.amount', 'title' => 'Amount'])

            ->addColumn(['data' => 'currency_id', 'name' => 'vouchers.currency_id', 'title' => 'Currency'])

            ->addColumn(['data' => 'redeemed', 'name' => 'vouchers.redeemed', 'title' => 'Redeemed'])

            ->addColumn(['data' => 'status', 'name' => 'vouchers.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
