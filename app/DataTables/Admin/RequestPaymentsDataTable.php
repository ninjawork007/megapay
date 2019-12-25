<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\RequestPayment;
use Yajra\DataTables\Services\DataTable;

class RequestPaymentsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($request_payment)
            {
                return dateFormat($request_payment->created_at);
            })
            ->addColumn('sender', function ($request_payment)
            {
                $sender = isset($request_payment->user) ? $request_payment->user->first_name.' '.$request_payment->user->last_name : "-";

                $senderWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $request_payment->user->id) . '">'.$sender.'</a>' : $sender;
                return $senderWithLink;
            })
            ->editColumn('amount', function ($request_payment)
            {
                if ($request_payment->amount > 0)
                {
                    $amount = '<td><span class="text-green">+' . formatNumber($request_payment->amount) . '</span></td>';
                }
                else
                {
                    $amount = '<td><span class="text-red">' . ($request_payment->amount == 0) ?  "-" : formatNumber($request_payment->amount) . '</span></td>';
                }
                return $amount;
            })
            ->editColumn('accept_amount', function ($request_payment)
            {
                if ($request_payment->accept_amount > 0)
                {
                    $accept_amount = '<td><span class="text-green">+' . formatNumber($request_payment->accept_amount) . '</span></td>';
                }
                else
                {
                    $accept_amount = '<td><span class="text-red">' . ($request_payment->accept_amount == 0) ?  "-" : formatNumber($request_payment->accept_amount) . '</span></td>';
                }
                return $accept_amount;
            })
            ->editColumn('currency_id', function ($request_payment)
            {
                return $request_payment->currency->code;
            })
            ->addColumn('receiver', function ($request_payment)
            {
                if (isset($request_payment->receiver))
                {
                    $receiver = $request_payment->receiver->first_name.' '.$request_payment->receiver->last_name;
                    $receiverWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $request_payment->receiver->id) . '">'.$receiver.'</a>' : $receiver;
                }
                else
                {
                    if (!empty($request_payment->email))
                    {
                        $receiver = $request_payment->email;
                        $receiverWithLink = $receiver;
                    }
                    elseif (!empty($request_payment->phone))
                    {
                        $receiver         = $request_payment->phone;
                        $receiverWithLink = $receiver;
                    }
                    else
                    {
                        $receiver         = '-';
                        $receiverWithLink = $receiver;
                    }
                }
                return $receiverWithLink;
            })
            ->editColumn('status', function ($request_payment)
            {
                if ($request_payment->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($request_payment->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($request_payment->status == 'Refund')
                {
                    $status = '<span class="label label-warning">Refunded</span>';
                }
                elseif ($request_payment->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($request_payment)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_request_payment')) ?
                '<a href="' . url('admin/request_payments/edit/' . $request_payment->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['sender','amount','accept_amount','receiver','status', 'action'])
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
                $query = (new RequestPayment())->getRequestPaymentsList($from, $to, $status, $currency, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new RequestPayment())->getRequestPaymentsList($from, $to, $status, $currency, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $currency = 'all';
            $user     = null;
            $query    = (new RequestPayment())->getRequestPaymentsList($from, $to, $status, $currency, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'request_payments.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'request_payments.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'uuid', 'name' => 'request_payments.uuid', 'title' => 'UUID', 'visible' => false])

            ->addColumn(['data' => 'sender', 'name' => 'user.last_name', 'title' => 'User', 'visible' => false]) //relation
            ->addColumn(['data' => 'sender', 'name' => 'user.first_name', 'title' => 'User']) //relation

            ->addColumn(['data' => 'amount', 'name' => 'request_payments.amount', 'title' => 'Requested Amount'])

            ->addColumn(['data' => 'accept_amount', 'name' => 'request_payments.accept_amount', 'title' => 'Accepted Amount'])

            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => 'Currency'])//relation

            ->addColumn(['data' => 'receiver', 'name' => 'receiver.last_name', 'title' => 'Receiver', 'visible' => false]) //relation
            ->addColumn(['data' => 'receiver', 'name' => 'receiver.first_name', 'title' => 'Receiver']) //relation

            ->addColumn(['data' => 'status', 'name' => 'request_payments.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
