<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\MerchantPayment;
use Yajra\DataTables\Services\DataTable;

class MerchantPaymentsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($merchant_payment)
            {
                return dateFormat($merchant_payment->created_at);
            })
            ->editColumn('merchant_id', function ($merchant_payment)
            {
                $merchant = isset($merchant_payment->merchant) ? $merchant_payment->merchant->user->first_name.' '.$merchant_payment->merchant->user->last_name :"-";
                // return $merchant;
                $merchantWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $merchant_payment->merchant->user->id) . '">'.$merchant.'</a>' : $merchant;
                return $merchantWithLink;
            })
            ->editColumn('user_id', function ($merchant_payment)
            {
                if (isset($merchant_payment->user))
                {
                    $user = $merchant_payment->user->first_name.' '.$merchant_payment->user->last_name;
                    $userWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $merchant_payment->user->id) . '">'.$user.'</a>' : $user;
                }
                else
                {
                    $user = '-';
                    $userWithLink = $user;
                }
                return $userWithLink;
            })
            ->editColumn('amount', function ($merchant_payment)
            {
                return formatNumber($merchant_payment->amount);
            })
            ->addColumn('fees', function ($merchant_payment)
            {
                return ($merchant_payment->charge_percentage == 0) && ($merchant_payment->charge_fixed == 0) ? "-" : formatNumber($merchant_payment->charge_percentage + $merchant_payment->charge_fixed);
            })
            ->editColumn('total', function ($merchant_payment)
            {
                $total = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed + $merchant_payment->amount;
                if ($total > 0)
                {
                    if ($merchant_payment->status == 'Refund')
                    {
                        $total = '<td><span class="text-red">-' . formatNumber($total) . '</span></td>';
                    }
                    else
                    {
                        $total = '<td><span class="text-green">+' . formatNumber($total) . '</span></td>';
                    }
                }
                else
                {
                    $total = '<td><span class="text-red">' . formatNumber($total) . '</span></td>';
                }
                return $total;
            })
            ->editColumn('currency_id', function ($merchant_payment)
            {
                return $merchant_payment->currency->code;
            })
            ->editColumn('payment_method_id', function ($merchant_payment)
            {
                return ($merchant_payment->payment_method->name == "Mts") ? getCompanyName() : $merchant_payment->payment_method->name;
            })
            ->editColumn('status', function ($merchant_payment)
            {
                if ($merchant_payment->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($merchant_payment->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($merchant_payment->status == 'Refund')
                {
                    $status = '<span class="label label-warning">Refunded</span>';
                }
                elseif ($merchant_payment->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($merchant_payment)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_merchant_payment')) ?
                '<a href="' . url('admin/merchant_payments/edit/' . $merchant_payment->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['total', 'merchant_id', 'user_id', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status   = $_GET['status'];
            $currency = $_GET['currency'];
            $pm       = $_GET['payment_methods'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new MerchantPayment())->getMerchantPaymentsList($from, $to, $status, $currency, $pm);
            }
            else
            {
                $from         = setDateForDb($_GET['from']);
                $to           = setDateForDb($_GET['to']);
                $query = (new MerchantPayment())->getMerchantPaymentsList($from, $to, $status, $currency, $pm);
            }
        }
        else
        {
            $from     = null;
            $to       = null;
            $status   = 'all';
            $currency = 'all';
            $pm = 'all';
            $query = (new MerchantPayment())->getMerchantPaymentsList($from, $to, $status, $currency, $pm);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'merchant_payments.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

        ->addColumn(['data' => 'created_at', 'name' => 'merchant_payments.created_at', 'title' => 'Date'])

        ->addColumn(['data' => 'uuid', 'name' => 'merchant_payments.uuid', 'title' => 'UUID', 'visible' => false])

        ->addColumn(['data' => 'merchant_id', 'name' => 'merchant.user.last_name', 'title' => 'Merchant U Last Name', 'visible' => false])//relation
        ->addColumn(['data' => 'merchant_id', 'name' => 'merchant.user.first_name', 'title' => 'Merchant'])//relation

        ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => 'User Last Name', 'visible' => false])//relation
        ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => 'User ']) //relation

        ->addColumn(['data' => 'amount', 'name' => 'merchant_payments.amount', 'title' => 'Amount'])
        ->addColumn(['data' => 'fees', 'name' => 'fees', 'title' => 'Fees']) //custom
        ->addColumn(['data' => 'total', 'name' => 'merchant_payments.total', 'title' => 'Total'])

        ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => 'Currency']) //relation

        ->addColumn(['data' => 'payment_method_id', 'name' => 'payment_method.name', 'title' => 'Payment Method']) //relation

        ->addColumn(['data' => 'status', 'name' => 'merchant_payments.status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters($this->getBuilderParameters());
    }
}
