<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\ActivityLogsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // public function activities_list()
    // {
    //     $data['menu']     = 'activity_logs';
    //     $data['activity_logs'] = $activity_logs = ActivityLog::latest()->get();
    //     return view('admin.activity_logs.list', $data);
    // }

    public function activities_list(ActivityLogsDataTable $dataTable)
    {
        $data['menu']     = 'activity_logs';
        return $dataTable->render('admin.activity_logs.list', $data);
    }
}
