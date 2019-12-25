<?php
namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Pages;
use Yajra\DataTables\Services\DataTable;

class PagesDataTable extends DataTable
{
    public function ajax()
    {
        $page = $this->query();

        return datatables()
            ->of($page)
            ->addColumn('action', function ($page)
            {
                $edit = $delete = '';
                $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_page')) ? '<a href="' . url('admin/settings/page/edit/' . $page->id) . '"  class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_page')) ? '<a href="' . url('admin/settings/page/delete/' . $page->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>' : '';

                return $edit . $delete;
            })

            ->addColumn('name', function ($page)
            {
                $name = '<a href="' . url('admin/settings/page/edit/' . $page->id) . '">' . $page->name . '</a>';
                return $name;
            })
            ->addColumn('url', function ($page)
            {
                $name = '<a target="_blank" href="' . url($page->url) . '">' . $page->url . '</a>';
                return $name;
            })
            ->addColumn('position', function ($page)
            {
                return $page->position;
            })
            ->editColumn('status', function ($page)
            {
                return ucfirst($page->status);
            })
            ->rawColumns(['url','name', 'action'])
            ->make(true);
    }

    public function query()
    {
        // $page = Pages::select()->orderBy('id','desc');
        $page = Pages::select();
        return $this->applyScopes($page);
    }

    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'pages.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

        ->addColumn(['data' => 'name', 'name' => 'pages.name', 'title' => 'Name'])

        ->addColumn(['data' => 'url', 'name' => 'pages.url', 'title' => 'Url'])

        ->addColumn(['data' => 'position', 'name' => 'pages.position', 'title' => 'Position'])

        ->addColumn(['data' => 'status', 'name' => 'pages.status', 'title' => 'Status'])

        ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])
        ->parameters($this->getBuilderParameters());
    }
}
