<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Dispute;
use Yajra\DataTables\Services\DataTable;

class DisputesDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($dispute)
            {
                return dateFormat($dispute->created_at);
            })
            ->addColumn('code', function ($dispute)
            {
                $code = $dispute->code;

                $codeWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_dispute') && $dispute->status != 'Reject') ?
                '<a href="' . url('admin/dispute/discussion/' . $dispute->id) . '">'.$code.'</a>' : $code;
                return $codeWithLink;
            })
            ->addColumn('title', function ($dispute)
            {
                $title = $dispute->title;

                $titleWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_dispute') && $dispute->status != 'Reject') ?
                '<a href="' . url('admin/dispute/discussion/' . $dispute->id) . '">'.$title.'</a>' : $title;
                return $titleWithLink;
            })
            ->editColumn('claimant_id', function ($dispute)
            {
                $claimant = isset($dispute->claimant) ? $dispute->claimant->first_name .' '.$dispute->claimant->last_name :"-";

                $claimantWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ?
                '<a href="' . url('admin/users/edit/' . $dispute->claimant->id) . '">'.$claimant.'</a>' : $claimant;
                return $claimantWithLink;
            })
            ->editColumn('defendant_id', function ($dispute)
            {
                $defendant = isset($dispute->defendant) ? $dispute->defendant->first_name .' '.$dispute->defendant->last_name :"-";

                $defendantWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ?
                '<a href="' . url('admin/users/edit/' . $dispute->defendant->id) . '">'.$defendant.'</a>' : $defendant;
                return $defendantWithLink;
            })
            ->editColumn('transaction_id', function ($dispute)
            {
                $transactionUuid = isset($dispute->transaction) ? $dispute->transaction->uuid :"-";

                $transactionUuidWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_dispute')) ?
                '<a href="' . url('admin/transactions/edit/' . $dispute->transaction->id) . '" target="_blank">'.$transactionUuid.'</a>' : $transactionUuid;
                return $transactionUuidWithLink;
            })
            ->editColumn('status', function ($dispute)
            {
                if ($dispute->status == 'Open')
                {
                    $status = '<span class="label label-primary">Open</span>';
                }
                elseif ($dispute->status == 'Solve')
                {
                    $status = '<span class="label label-success">Solved</span>';
                }
                elseif ($dispute->status == 'Close')
                {
                    $status = '<span class="label label-danger">Closed</span>';
                }
                return $status;
            })
            ->rawColumns(['code','title','claimant_id', 'defendant_id','transaction_id','status'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $user   = $_GET['user_id'];
            $status = $_GET['status'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Dispute())->getDisputesList($from, $to, $status, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new Dispute())->getDisputesList($from, $to, $status, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $user     = null;
            $query    = (new Dispute())->getDisputesList($from, $to, $status, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'disputes.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'disputes.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'code', 'name' => 'disputes.code', 'title' => 'Dispute ID'])

            ->addColumn(['data' => 'title', 'name' => 'disputes.title', 'title' => 'Title'])

            ->addColumn(['data' => 'claimant_id', 'name' => 'claimant.last_name', 'title' => 'Claimant','visible' => false]) //relation
            ->addColumn(['data' => 'claimant_id', 'name' => 'claimant.first_name', 'title' => 'Claimant']) //relation

            ->addColumn(['data' => 'defendant_id', 'name' => 'defendant.last_name', 'title' => 'Defendant','visible' => false]) //relation
            ->addColumn(['data' => 'defendant_id', 'name' => 'defendant.first_name', 'title' => 'Defendant']) //relation

            ->addColumn(['data' => 'transaction_id', 'name' => 'disputes.transaction_id', 'title' => 'Transaction ID'])

            ->addColumn(['data' => 'status', 'name' => 'disputes.status', 'title' => 'Status'])

            ->parameters($this->getBuilderParameters());
    }

}
