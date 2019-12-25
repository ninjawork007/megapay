<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Backup;
use Yajra\DataTables\Services\DataTable;

class BackupsDataTable extends DataTable
{
    public function ajax() //don't use default dataTable() method
    {
        $backup = $this->query();
        return datatables()
            ->of($backup)
            ->addColumn('action', function ($backup)
            {
                $edit = '';

                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_database_backup')) ? '<a href="' . url('admin/backup/download/' . $backup->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>' : '';
                return $edit;

            })
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     */
    public function query()
    {
        // $backup = Backup::orderBy('created_at', 'desc');
        $backup = Backup::select();
        return $this->applyScopes($backup);
    }

    /**
     * html builder.
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'backups.id', 'title' => 'Id'])
            ->addColumn(['data' => 'name', 'name' => 'backups.name', 'title' => 'Name'])
            ->addColumn(['data' => 'created_at', 'name' => 'backups.created_at', 'title' => 'Date'])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
            ->parameters($this->getBuilderParameters());
    }

    /**
     * Get columns.
     */
    protected function getColumns()
    {
        return [
            'id',
            'add your columns',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename()
    {
        return 'backupsdatatable_' . time();
    }
}
