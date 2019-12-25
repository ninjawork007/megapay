<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Meta;
use Yajra\DataTables\Services\DataTable;

class MetasDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     */
    public function ajax()
    {
        return datatables()
        ->eloquent($this->query())
        ->editColumn('keywords', function ($seo_metas)
        {
            return isset($seo_metas->keywords) ? $seo_metas->keywords : '-';
        })
        ->addColumn('action', function ($seo_metas)
        {
            $edit = '';

            $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_meta')) ? '<a href="'.url('admin/settings/edit_meta/'.$seo_metas->id).'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';
            return $edit;
        })
        ->make(true);
    }

    /**
     * Get the query object to be processed by dataTables.
     */
    public function query()
    {
        $query = Meta::select();
        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'metas.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])
        ->addColumn(['data' => 'url', 'name' => 'metas.url', 'title' => 'Url'])
        ->addColumn(['data' => 'title', 'name' => 'metas.title', 'title' => 'Title'])
        ->addColumn(['data' => 'description', 'name' => 'metas.description', 'title' => 'Description'])
        ->addColumn(['data' => 'keywords', 'name' => 'metas.keywords', 'title' => 'Keywords'])
        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters($this->getBuilderParameters());
    }
}
