<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Role;
use Yajra\DataTables\Services\DataTable;

class RolesDataTable extends DataTable
{

    public function ajax() //don't use default dataTable() method
    {
        $role = $this->query();

        return datatables()
            ->of($role)
            ->addColumn('action', function ($role)
            {
                $edit = $delete = '';

                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_role')) ? '<a href="' . url('admin/settings/edit_role/' . $role->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_role')) ? '<a href="' . url('admin/settings/delete_role/' . $role->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>' : '';

                return $edit . $delete;
            })
            ->addColumn('name', function ($role)
            {
                $name = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_role')) ? '<a href="' . url('admin/settings/edit_role/' . $role->id) . '">' . ucfirst($role->name) . '</a>' : ucfirst($role->name);
                return $name;
            })
            ->editColumn('display_name', function ($role)
            {
                $display_name = ucfirst($role->display_name);
                return $display_name;
            })
            ->editColumn('description', function ($role)
            {
                $description = ucfirst($role->description);
                return $description;
            })
            ->rawColumns(['name', 'action'])
            ->make(true);
    }

    public function query()
    {
        $role = Role::where(['user_type' => 'Admin'])->select();
        return $this->applyScopes($role);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'roles.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'name', 'name' => 'roles.name', 'title' => 'Name'])

            ->addColumn(['data' => 'display_name', 'name' => 'roles.display_name', 'title' => 'Display Name'])

            ->addColumn(['data' => 'description', 'name' => 'roles.description', 'title' => 'Description'])

            ->addColumn([
                'data'  => 'action',
                'name'  => 'action',
                'title' => 'Action', 'orderable' => false, 'searchable' => false,
            ])
            ->parameters($this->getBuilderParameters());
    }
}
