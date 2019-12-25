<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Currency;
use Yajra\DataTables\Services\DataTable;

class CurrenciesDataTable extends DataTable
{
    public function ajax() //don't use default dataTable() method
    {
        return datatables()
            ->eloquent($this->query())

            ->editColumn('rate', function ($currency)
            {
                return $currency->rate != 0  ? formatNumber($currency->rate) : '-';
            })
            ->editColumn('logo', function ($currency)
            {
                if ($currency->logo)
                {
                    $logo = '<td><img src="'. url('public/uploads/currency_logos/' . $currency->logo).'" width="64" height="64" class="img-responsive"></td>';
                }
                else
                {
                    $logo = '<td><img src="'. url('public/user_dashboard/images/favicon.png').'" width="64" height="64" class="img-responsive"></td>';
                }
                return $logo;
            })
            ->editColumn('status', function ($currency)
            {
                if ($currency->default == 1)
                {
                    $status = '<span class="label label-warning">Default Currency</span>';
                }
                else
                {
                    if ($currency->status == 'Active')
                    {
                        $status = '<span class="label label-success">Active</span>';
                    }
                    elseif ($currency->status == 'Inactive')
                    {
                        $status = '<span class="label label-danger">Inactive</span>';
                    }
                }
                return $status;
            })
            ->addColumn('action', function ($currency)
            {
                $edit = $delete = $feesLimit = $pm = '';

                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_currency')) ? '<a href="' . url('admin/settings/edit_currency/' . $currency->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_currency')) ? '<a href="' . url('admin/settings/delete_currency/' . $currency->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>&nbsp;' : '';

                $feesLimit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_currency') && $currency->status == 'Active') ? '<a href="' . url('admin/settings/feeslimit/deposit/' . $currency->id) . '" class="btn btn-xs btn-success">
                <i class="glyphicon glyphicon-view">Fees</i></a>&nbsp;' : '';

                $pm = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_currency')) && $currency->status == 'Active'? '<a href="' . url('admin/settings/payment-methods/stripe/' . $currency->id) . '" class="btn btn-xs btn-primary">
                <i class="glyphicon glyphicon-view">Payment-Methods</i></a>&nbsp;' : '';

                return $edit . $delete . $feesLimit . $pm;
            })
            ->rawColumns(['logo', 'status', 'action'])
            ->make(true);
    }

    public function query()
    {
        $query = Currency::select('id','name','code','symbol','rate','logo','status','default')->orderBy('id', 'desc');
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'currencies.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])
        ->addColumn(['data' => 'name', 'name' => 'currencies.name', 'title' => 'Name'])
        ->addColumn(['data' => 'code', 'name' => 'currencies.code', 'title' => 'Code'])
        ->addColumn(['data' => 'symbol', 'name' => 'currencies.symbol', 'title' => 'Symbol'])
        ->addColumn(['data' => 'rate', 'name' => 'currencies.rate', 'title' => 'Rate'])
        ->addColumn(['data' => 'logo', 'name' => 'currencies.logo', 'title' => 'Logo'])
        ->addColumn(['data' => 'status', 'name' => 'currencies.status', 'title' => 'Status'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters($this->getBuilderParameters());
    }
}
