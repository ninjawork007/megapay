<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Language;
use Yajra\DataTables\Services\DataTable;

class LanguagesDataTable extends DataTable
{
    /**
     * [$exportColumns description]
     * @var [type]
     */
    protected $exportColumns = ['name', 'value', 'status'];

    /**
     * Build DataTable class.
     *
     * @return \Yajra\Datatables\Engines\BaseEngine
     */
    public function ajax() //don't use default dataTable() method
    {
        $language = $this->query();

        return datatables()
            ->of($language)
            ->addColumn('action', function ($language)
            {
                $edit = $delete = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_language')) ?
                '<a href="' . url('admin/settings/edit_language/' . $language->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                if ($language->deletable == 'No')
                {
                    $delete = '';
                }
                else
                {
                    $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_language')) ? '<a href="' . url('admin/settings/delete_language/' . $language->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>' : '';
                }
                return $edit . $delete;

            })
            ->editColumn('status', function ($language)
            {
                if ($language->status == 'Active')
                {
                    $status = '<span class="label label-success">Active</span>';
                }
                else
                {
                    $status = '<span class="label label-danger">Inactive</span>';
                }
                return $status;
            })
            ->editColumn('flag', function ($language)
            {
                return isset($language->flag) ? '<img src="' . url('public/uploads/languages-flags/' . $language->flag) . '" width="40" height="40" class="img-responsive">' :
                '<img src="' . url('public/uploads/userPic/default-image.png').'" width="40" height="40" class="img-responsive">';
            })
            ->rawColumns(['flag', 'status','action'])
            ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function query()
    {
        // $language = Language::select()->orderBy('id', 'desc');
        $language = Language::select();
        return $this->applyScopes($language);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\Datatables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'languages.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'name', 'name' => 'languages.name', 'title' => 'Name'])

            ->addColumn(['data' => 'short_name', 'name' => 'languages.short_name', 'title' => 'Short Name'])

            ->addColumn(['data' => 'flag', 'name' => 'languages.flag', 'title' => 'Flag'])

            ->addColumn(['data' => 'status', 'name' => 'languages.status', 'title' => 'Status'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }

    /**
     * Get columns.
     *
     * @return array
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
     *
     * @return string
     */
    protected function filename()
    {
        return 'languagesdatatable_' . time();
    }
}
