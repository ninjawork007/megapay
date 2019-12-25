<?php

namespace App\DataTables\Admin;

use App\Http\Helpers\Common;
use App\Models\Ticket;
use Yajra\DataTables\Services\DataTable;

class TicketsDataTable extends DataTable
{

    public function ajax()
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('created_at', function ($ticket)
            {
                return dateFormat($ticket->created_at);
            })
            ->editColumn('user_id', function ($ticket)
            {
                $user = isset($ticket->user) ? $ticket->user->first_name .' '.$ticket->user->last_name :"-";

                $userWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_user')) ?
                '<a href="' . url('admin/users/edit/' . $ticket->user->id) . '">'.$user.'</a>' : $user;
                return $userWithLink;
            })
            ->addColumn('subject', function ($ticket)
            {
                $subject = $ticket->subject;

                $subjectWithLink = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_ticket')) ?
                '<a href="' . url('admin/tickets/reply/' . $ticket->id) . '">'.$subject.'</a>' : $subject;
                return $subjectWithLink;
            })
            ->editColumn('ticket_status_id', function ($ticket)
            {
                if ($ticket->ticket_status->name == 'Open')
                {
                    $status = '<span class="label label-success">Open</span>';
                }
                elseif ($ticket->ticket_status->name == 'In Progress')
                {
                    $status = '<span class="label label-primary">In Progress</span>';
                }
                elseif ($ticket->ticket_status->name == 'Hold')
                {
                    $status = '<span class="label label-warning">Hold</span>';
                }
                elseif ($ticket->ticket_status->name == 'Closed')
                {
                    $status = '<span class="label label-danger">Closed</span>';
                }
                return $status;
            })
            ->editColumn('last_reply', function ($ticket)
            {
                return $ticket->last_reply ?  dateFormat($ticket->last_reply)  :  'No Reply Yet';
            })
            ->addColumn('action', function ($ticket)
            {
                // if (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_ticket') || Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_ticket'))
                // {
                    $edit = $delete = '';

                    $edit = (Common::has_permission(\Auth::guard('admin')->user()->id, 'edit_ticket')) ? '<a href="' . url('admin/tickets/edit/' . $ticket->id) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i></a>&nbsp;' : '';

                    $delete = (Common::has_permission(\Auth::guard('admin')->user()->id, 'delete_ticket')) ? '<a href="' . url('admin/tickets/delete/' . $ticket->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="glyphicon glyphicon-trash"></i></a>' : '';
                    return $edit . $delete;

                // }
                // else{
                //     return datatables()->eloquent($this->query())->removeColumn('action');
                // }
            })
            ->rawColumns(['user_id', 'subject','ticket_status_id','action'])
            ->make(true);
    }

    public function query()
    {
        if (isset($_GET['btn']))
        {
            $status = $_GET['status'];
            $user   = $_GET['user_id'];

            if (empty($_GET['from']))
            {
                $from  = null;
                $to    = null;
                $query = (new Ticket())->getTicketsList($from, $to, $status, $user);
            }
            else
            {
                $from  = setDateForDb($_GET['from']);
                $to    = setDateForDb($_GET['to']);
                $query = (new Ticket())->getTicketsList($from, $to, $status, $user);
            }
        }
        else
        {
            $from = null;
            $to   = null;

            $status   = 'all';
            $user     = null;
            $query    = (new Ticket())->getTicketsList($from, $to, $status, $user);
        }
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'tickets.id', 'title' => 'ID', 'searchable' => false, 'visible' => false])

            ->addColumn(['data' => 'created_at', 'name' => 'tickets.created_at', 'title' => 'Date'])

            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => 'User', 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => 'User'])

            ->addColumn(['data' => 'subject', 'name' => 'tickets.subject', 'title' => 'Subject'])

            ->addColumn(['data' => 'ticket_status_id', 'name' => 'ticket_status.name', 'title' => 'Status'])

            ->addColumn(['data' => 'priority', 'name' => 'tickets.priority', 'title' => 'Priority'])

            ->addColumn(['data' => 'last_reply', 'name' => 'tickets.last_reply', 'title' => 'Last Reply'])

            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => 'Action', 'orderable' => false, 'searchable' => false])

            ->parameters($this->getBuilderParameters());
    }

}
