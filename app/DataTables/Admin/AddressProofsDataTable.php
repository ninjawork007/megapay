<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\DocumentVerification;
use Yajra\DataTables\Services\DataTable;

class AddressProofsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($documentVerification)
            {
                return dateFormat($documentVerification->created_at);
            })
            ->addColumn('user_id', function ($documentVerification)
            {
                $sender = isset($documentVerification->user) ? $documentVerification->user->first_name.' '.$documentVerification->user->last_name :"-";

                $senderWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $documentVerification->user->id) . '">'.$sender.'</a>' : $sender;
                return $senderWithLink;
            })
            ->editColumn('status', function ($documentVerification)
            {
                if ($documentVerification->status == 'approved')
                {
                    $status = '<span class="label label-success">Approved</span>';
                }
                elseif ($documentVerification->status == 'pending')
                {
                    $status = '<span class="label label-primary">Pending</span>';
                }
                elseif ($documentVerification->status == 'rejected')
                {
                    $status = '<span class="label label-danger">Rejected</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($documentVerification)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_address_verfication')) ?
                '<a href="' . url('admin/address-proofs/edit/' . $documentVerification->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['user_id', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status   = $_GET['status'];
            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new DocumentVerification())->getAddressVerificationsList($from, $to, $status);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new DocumentVerification())->getAddressVerificationsList($from, $to, $status);
            }
        }
        else
        {
            $from = null;
            $to   = null;
            $status   = 'all';
            $query    = (new DocumentVerification())->getAddressVerificationsList($from, $to, $status);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'document_verifications.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'document_verifications.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => 'User','visible' => false])//relation
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => 'User'])//relation

            ->addColumn(['data' => 'status', 'name' => 'document_verifications.status', 'title' => 'Status'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters($this->getBuilderParameters());
    }
}
