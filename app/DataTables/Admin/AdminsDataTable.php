<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Admin;
use Yajra\DataTables\Services\DataTable;

class AdminsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())

            ->editColumn('first_name', function ($admin)
            {
                return (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_admin')) ?
                '<a href="' . url('admin/admin-user/edit/' . $admin->id) . '">'.$admin->first_name.'</a>' : $admin->first_name;
            })
            ->editColumn('last_name', function ($admin)
            {
                return (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_admin')) ?
                '<a href="' . url('admin/admin-user/edit/' . $admin->id) . '">'.$admin->last_name.'</a>' : $admin->last_name;
            })
            ->addColumn('role', function ($admin)
            {
                return $admin->role->display_name;
            })
            ->editColumn('status', function ($admin)
            {
                if ($admin->status == 'Active')
                {
                    $status = '<span class="label label-success">Active</span>';
                }
                elseif ($admin->status == 'Inactive')
                {
                    $status = '<span class="label label-danger">Inactive</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($admin)
            {
                $edit = $delete = '';

                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_admin')) ? '<a href="' . url('admin/admin-user/edit/' . $admin->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_admin')) ? '<a href="' . url('admin/admin-user/delete/' . $admin->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>' : '';
                return $edit . $delete;
            })
            ->rawColumns(['first_name','last_name','status', 'action'])
            ->make(true);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Admin::with('role:id,display_name')->select('admins.id','admins.first_name','admins.last_name','admins.email','admins.role_id','admins.status');
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'admins.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'first_name', 'name' => 'admins.first_name', 'title' => 'First Name'])

            ->addColumn(['data' => 'last_name', 'name' => 'admins.last_name', 'title' => 'Last Name'])

            ->addColumn(['data' => 'email', 'name' => 'admins.email', 'title' => 'Email'])

            ->addColumn(['data' => 'role', 'name' => 'role', 'title' => 'Group'])

            ->addColumn(['data' => 'status', 'name' => 'admins.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }
}
