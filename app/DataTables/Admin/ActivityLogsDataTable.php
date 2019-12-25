<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use Yajra\DataTables\Services\DataTable;

class ActivityLogsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($activityLog)
            {
                return dateFormat($activityLog->created_at);
            })
            ->editColumn('user_id', function ($activityLog)
            {
                if ($activityLog->type == 'Admin')
                {
                    $admin = $activityLog->user_id;
                    $withLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_admin')) ? '<a href="' . url('admin/admin-user/edit/' . $admin) . '">'.$admin.'</a>' : $admin;
                }
                else
                {
                    $user = $activityLog->user_id;
                    $withLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $user) . '">'.$user.'</a>' : $user;
                }
                return $withLink;
            })
            ->addColumn('username', function ($activityLog)
            {
                if ($activityLog->type == 'Admin')
                {
                    $admin = $activityLog->admin->first_name.' '. $activityLog->admin->last_name;
                    $withLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_admin')) ? '<a href="' . url('admin/admin-user/edit/' . $activityLog->admin->id) . '">'.$admin.'</a>' : $admin;
                }
                else
                {
                    $user = $activityLog->user->first_name.' '. $activityLog->user->last_name;
                    $withLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ? '<a href="' . url('admin/users/edit/' . $activityLog->user->id) . '">'.$user.'</a>' : $user;
                }
                return $withLink;
            })
            ->editColumn('browser_agent', function ($activityLog)
            {
                $getBrowser = getBrowser($activityLog->browser_agent);
                $browser_agent = $getBrowser['name'] .' '. substr($getBrowser['version'], 0, 4) .' | '. ucfirst($getBrowser['platform']);
                return $browser_agent;
            })
            ->rawColumns(['user_id','username'])
            ->make(true);
    }

    public function query()
    {
        $query = ActivityLog::with([
            'user'   => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            },
            'admin' => function ($query)
            {
                $query->select('id', 'first_name', 'last_name');
            },
        ])
        ->select('activity_logs.*');
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
        ->addColumn(['data' => 'id', 'name' => 'activity_logs.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

        ->addColumn(['data' => 'created_at', 'name' => 'activity_logs.created_at', 'title' => 'Date'])

        ->addColumn(['data' => 'user_id', 'name' => 'activity_logs.user_id', 'title' => 'User ID'])

        ->addColumn(['data' => 'type', 'name' => 'activity_logs.type', 'title' => 'User Type'])

        //username
        ->addColumn(['data' => 'username', 'name' => 'user.last_name', 'title' => 'User', 'visible' => false])//relation
        ->addColumn(['data' => 'username', 'name' => 'user.first_name', 'title' => 'User', 'visible' => false])//relation
        ->addColumn(['data' => 'username', 'name' => 'admin.last_name', 'title' => 'User', 'visible' => false])//relation
        ->addColumn(['data' => 'username', 'name' => 'admin.first_name', 'title' => 'User', 'visible' => false])//relation
        ->addColumn(['data' => 'username', 'name' => 'username', 'title' => 'Username'])

        ->addColumn(['data' => 'ip_address', 'name' => 'activity_logs.ip_address', 'title' => 'IP Address'])

        ->addColumn(['data' => 'browser_agent', 'name' => 'activity_logs.browser_agent', 'title' => 'Browser | Platform'])

        ->parameters($this->getBuilderParameters());
    }
}
