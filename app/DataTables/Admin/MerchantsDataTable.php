<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Merchant;
use Yajra\DataTables\Services\DataTable;

class MerchantsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($merchant)
            {
                return dateFormat($merchant->created_at);
            })
            ->editColumn('business_name', function ($merchant)
            {
                return (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_merchant')) ? '<a href="' . url('admin/merchant/edit/' . $merchant->id) . '">'.$merchant->business_name.'</a>' : $merchant->business_name;
            })
            ->addColumn('user', function ($merchant)
            {
                $user = isset($merchant->user) ? $merchant->user->first_name.' '.$merchant->user->last_name :"-";

                $userWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $merchant->user->id) . '">'.$user.'</a>' : $user;
                return $userWithLink;
            })
            ->editColumn('merchant_group_id', function ($merchant)
            {
                return isset($merchant->merchant_group) ? $merchant->merchant_group->name: "-";
            })
            ->editColumn('logo', function ($merchant)
            {
                if (!empty($merchant->logo))
                {
                    $logo = '<td><img src="'. url('public/user_dashboard/merchant/' . $merchant->logo).'" width="80" height="80" class="img-responsive"></td>';
                }
                else
                {
                    $logo = '<td><img src="'. url('public/uploads/userPic/default-image.png').'" width="80" height="80" class="img-responsive"></td>';
                }
                return $logo;
            })
            ->editColumn('status', function ($merchant)
            {
                if ($merchant->status == 'Approved')
                {
                    $status = '<span class="label label-success">Approved</span>';
                }
                elseif ($merchant->status == 'Moderation')
                {
                    $status = '<span class="label label-primary">Moderation</span>';
                }
                elseif ($merchant->status == 'Disapproved')
                {
                    $status = '<span class="label label-danger">Disapproved</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($merchant)
            {
                $edit = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_merchant')) ?
                '<a href="' . url('admin/merchant/edit/' . $merchant->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
                return $edit;
            })
            ->rawColumns(['business_name','user','logo', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status   = $_GET['status'];
            $user     = $_GET['user_id'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Merchant())->getMerchantsList($from, $to, $status, $user);
            }
            else
            {
                $from         = setDateForDb($_GET['from']);
                $to           = setDateForDb($_GET['to']);
                $query = (new Merchant())->getMerchantsList($from, $to, $status, $user);
            }
        }
        else
        {
            $from     = null;
            $to       = null;
            $status   = 'all';
            $user     = null;
            $query = (new Merchant())->getMerchantsList($from, $to, $status, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'merchants.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'user', 'name' => 'user.last_name', 'title' => 'User','visible' => false]) //relation

            ->addColumn(['data' => 'created_at', 'name' => 'merchants.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'merchant_uuid', 'name' => 'merchants.merchant_uuid', 'title' => 'ID'])

            ->addColumn(['data' => 'type', 'name' => 'merchants.type', 'title' => 'Type'])

            ->addColumn(['data' => 'business_name', 'name' => 'merchants.business_name', 'title' => 'Name'])

            ->addColumn(['data' => 'user', 'name' => 'user.first_name', 'title' => 'User']) //relation

            ->addColumn(['data' => 'site_url', 'name' => 'merchants.site_url', 'title' => 'Url'])

            ->addColumn(['data' => 'merchant_group_id', 'name' => 'merchant_group.name', 'title' => 'Group']) //relation

            ->addColumn(['data' => 'logo', 'name' => 'merchants.logo', 'title' => 'Logo'])

            ->addColumn(['data' => 'status', 'name' => 'merchants.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
