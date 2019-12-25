<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Transfer;
use Yajra\DataTables\Services\DataTable;

class MoneyTransfersDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($transfer)
            {
                return dateFormat($transfer->created_at);
            })
            ->addColumn('sender', function ($transfer)
            {
                $sender = isset($transfer->sender) ? $transfer->sender->first_name . ' ' . $transfer->sender->last_name : "-";

                $senderWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $transfer->sender->id) . '">' . $sender . '</a>' : $sender;
                return $senderWithLink;
            })
            ->editColumn('amount', function ($transfer)
            {
                return formatNumber($transfer->amount);
            })
            ->editColumn('fee', function ($transfer)
            {
                return ($transfer->fee == 0) ? '-' : formatNumber($transfer->fee);
            })
            ->addColumn('total', function ($transfer)
            {
                if ($transfer->amount + $transfer->fee > 0)
                {
                    $total = '<td><span class="text-red">-' . formatNumber($transfer->amount + $transfer->fee) . '</span></td>';
                }
                else
                {
                    $total = '<td><span class="text-green">+' . formatNumber($transfer->amount + $transfer->fee) . '</span></td>';
                }
                return $total;
            })
            ->editColumn('currency_id', function ($transfer)
            {
                return $transfer->currency->code;
            })
            ->addColumn('receiver', function ($transfer)
            {
                if (isset($transfer->receiver))
                {
                    $receiver         = $transfer->receiver->first_name . ' ' . $transfer->receiver->last_name;
                    $receiverWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $transfer->receiver->id) . '">' . $receiver . '</a>' : $receiver;
                }
                else
                {
                    if (!empty($transfer->email))
                    {
                        $receiver         = $transfer->email;
                        $receiverWithLink = $receiver;
                    }
                    elseif (!empty($transfer->phone))
                    {
                        $receiver         = $transfer->phone;
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
            ->editColumn('status', function ($transfer)
            {
                if ($transfer->status == 'Success')
                {
                    $status = '<span class="label label-success">Success</span>';
                }
                elseif ($transfer->status == 'Pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($transfer->status == 'Refund')
                {
                    $status = '<span class="label label-warning">Refunded</span>';
                }
                elseif ($transfer->status == 'Blocked')
                {
                    $status = '<span class="label label-danger">Cancelled</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($transfer)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_transfer')) ?
                '<a href="' . url('admin/transfers/edit/' . $transfer->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['sender', 'receiver', 'total', 'status', 'action'])
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
                $query = (new Transfer())->getTransfersList($from, $to, $status, $currency, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new Transfer())->getTransfersList($from, $to, $status, $currency, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $currency = 'all';
            $user     = null;
            $query    = (new Transfer())->getTransfersList($from, $to, $status, $currency, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'transfers.id', 'title' => 'ID', 'searchable' => false, 'visible' => false]) //hidden

            ->addColumn(['data' => 'uuid', 'name' => 'transfers.uuid', 'title' => 'UUID', 'visible' => false])

            ->addColumn(['data' => 'sender', 'name' => 'sender.last_name', 'title' => 'Last Name', 'visible' => false])         //custom

            ->addColumn(['data' => 'receiver', 'name' => 'receiver.last_name', 'title' => 'Last Name', 'visible' => false])     //custom

            ->addColumn(['data' => 'created_at', 'name' => 'transfers.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'sender', 'name' => 'sender.first_name', 'title' => 'User']) //custom

            ->addColumn(['data' => 'amount', 'name' => 'transfers.amount', 'title' => 'Amount'])

            ->addColumn(['data' => 'fee', 'name' => 'transfers.fee', 'title' => 'Fees'])

            ->addColumn(['data' => 'total', 'name' => 'total', 'title' => 'Total', 'searchable' => false]) //custom

            ->addColumn(['data' => 'currency_id', 'name' => 'currency.code', 'title' => 'Currency']) //custom

            ->addColumn(['data' => 'receiver', 'name' => 'receiver.first_name', 'title' => 'Receiver']) //custom

            ->addColumn(['data' => 'status', 'name' => 'transfers.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
