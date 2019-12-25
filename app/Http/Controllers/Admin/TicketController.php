<?php

namespace App\Http\Controllers\Admin;

use App;
use App\DataTables\Admin\TicketsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Admin;
use App\Models\EmailTemplate;
use App\Models\File;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\TicketStatus;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Session;

class TicketController extends Controller
{
    public function __construct(Ticket $ticket, EmailController $email, Common $helper)
    {
        $this->ticket = $ticket;
        $this->email  = $email;
        $this->helper = $helper;
    }

    public function index(TicketsDataTable $dataTable)
    {
        $data['menu'] = 'ticket';

        $data['ticket_status'] = $ticket_status = $this->ticket->select('ticket_status_id')->groupBy('ticket_status_id')->get();
        // dd($ticket_status);

        $data['summary'] = $summary = \DB::select(\DB::raw("SELECT COUNT(tickets.ticket_status_id)
                                              as total_status, ticket_statuses.name,ticket_statuses.id, ticket_statuses.color as color
                                              FROM tickets
                                              RIGHT JOIN
                                              ticket_statuses
                                              ON ticket_statuses.id = tickets.ticket_status_id
                                              GROUP BY ticket_statuses.id"));
        // dd($summary);

        if (isset($_GET['ticket_status_id']))
        {
            $ticket_status_id = $_GET['ticket_status_id'];

            $data['tickets'] = $tickets = Ticket::where(['ticket_status_id' => $ticket_status_id])->orderBy('id', 'desc')->get();
        }
        else
        {
            $data['tickets'] = $tickets = Ticket::orderBy('id', 'desc')->get();
        }

        if (isset($_GET['btn']))
        {
            $data['status'] = $_GET['status'];
            $data['user']   = $user   = $_GET['user_id'];

            $data['getName'] = $getName = $this->ticket->getTicketsUserName($user);

            if (empty($_GET['from']))
            {
                $data['from'] = null;
                $data['to']   = null;
            }
            else
            {
                $data['from'] = $_GET['from'];
                $data['to']   = $_GET['to'];
            }
        }
        else
        {
            $data['from']   = null;
            $data['to']     = null;
            $data['status'] = 'all';
            $data['user']   = null;
        }
        return $dataTable->render('admin.tickets.list', $data);
    }

    public function create()
    {
        $data['menu'] = 'ticket';

        $data['admins'] = Admin::where(['status' => 'Active'])->get();

        $data['ticket_statuses'] = TicketStatus::get();

        $data['users'] = User::where(['status' => 'Active'])->get();

        return view('admin.tickets.add', $data);
    }

    public function ticketUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->ticket->getTicketUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $this->validate($request, [
            'subject' => 'required',
            'message' => 'required',
        ]);

        $ticket                   = new Ticket();
        $ticket->admin_id         = $request->assignee;
        $ticket->user_id          = $request->user_id;
        $ticket->ticket_status_id = $request->status;
        $ticket->subject          = $request->subject;
        $ticket->message          = $request->message;
        $ticket->code             = 'TIC-' . strtoupper(str_random(6));
        $ticket->priority         = $request->priority;
        $ticket->save();

        /**
         *  Mail to assignee
         */
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 11, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

        $ticket_info = EmailTemplate::where([
            'temp_id'     => 11,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        /**
         * SMS
         */
        // $ticketCreationEnglishSmsTempInfo = EmailTemplate::where(['temp_id' => 11, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();

        // $ticketCreationSmsTempInfo = EmailTemplate::where(['temp_id' => 11, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        if (isset($ticket->admin_id))
        {
            //Email
            if (!empty($ticket_info->subject) && !empty($ticket_info->body))
            {
                $ticket_sub = $ticket_info->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->admin->username, $ticket_info->body); //
            }
            else
            {
                $ticket_sub = $englishSenderLanginfo->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->admin->username, $englishSenderLanginfo->body); //
            }
            $ticket_msg = str_replace('{ticket_code}', $ticket->code, $ticket_msg);
            $ticket_msg = str_replace('{assigned/created}', 'assigned', $ticket_msg);
            $ticket_msg = str_replace('{to/for}', 'to', $ticket_msg);
            $ticket_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $ticket_msg);
            $ticket_msg = str_replace('{Assignee:}', '', $ticket_msg);
            $ticket_msg = str_replace('{assignee}', '', $ticket_msg);
            $ticket_msg = str_replace('{User:}', 'User:', $ticket_msg);
            $ticket_msg = str_replace('{user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticket_msg);
            $ticket_msg = str_replace('{subject}', $ticket->subject, $ticket_msg);
            $ticket_msg = str_replace('{message}', $ticket->message, $ticket_msg);
            $ticket_msg = str_replace('{status}', $ticket->ticket_status->name, $ticket_msg);
            $ticket_msg = str_replace('{priority}', $request->priority, $ticket_msg);
            $ticket_msg = str_replace('{soft_name}', Session::get('name'), $ticket_msg);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($ticket->admin->email, $ticket_sub, $ticket_msg);
            }

            //Sms
            // if (!empty($ticket->admin->phone))
            // {
            //     if (!empty($ticketCreationSmsTempInfo->subject) && !empty($ticketCreationSmsTempInfo->body))
            //     {
            //         $ticketCreationSmsTempInfo_sub = $ticketCreationSmsTempInfo->subject;
            //         $ticketCreationSmsTempInfo_msg = str_replace('{assignee/user}', $ticket->admin->username, $ticketCreationSmsTempInfo->body);
            //     }
            //     else
            //     {
            //         $ticketCreationSmsTempInfo_sub = $ticketCreationEnglishSmsTempInfo->subject;
            //         $ticketCreationSmsTempInfo_msg = str_replace('{assignee/user}', $ticket->admin->username, $ticketCreationEnglishSmsTempInfo->body);
            //     }
            //     $ticketCreationSmsTempInfo_msg = str_replace('{ticket_code}', $ticket->code, $ticketCreationSmsTempInfo_msg);
            //     $ticketCreationSmsTempInfo_msg = str_replace('{assigned/created}', 'assigned', $ticketCreationSmsTempInfo_msg);
            //     $ticketCreationSmsTempInfo_msg = str_replace('{to/for}', 'to', $ticketCreationSmsTempInfo_msg);

            //     if (checkAppSmsEnvironment())
            //     {
            //         if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
            //         {
            //             sendSMS(getNexmoDetails()->default_nexmo_phone_number, $ticket->admin->phone, $ticketCreationSmsTempInfo_msg);
            //         }
            //     }
            // }
        }


        if (isset($ticket->user_id))
        {
            /**
            *  Mail to user
            */
            if (!empty($ticket_info->subject) && !empty($ticket_info->body))
            {
                $ticket_sub = $ticket_info->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticket_info->body); //
            }
            else
            {
                $ticket_sub = $englishSenderLanginfo->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $englishSenderLanginfo->body); //
            }
            $ticket_msg = str_replace('{ticket_code}', $ticket->code, $ticket_msg);
            $ticket_msg = str_replace('{assigned/created}', 'created', $ticket_msg);
            $ticket_msg = str_replace('{to/for}', 'for', $ticket_msg);
            $ticket_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $ticket_msg);
            $ticket_msg = str_replace('{Assignee:}', 'Assignee:', $ticket_msg);
            $ticket_msg = str_replace('{assignee}', $ticket->admin->username, $ticket_msg);
            $ticket_msg = str_replace('{User:}', '', $ticket_msg);
            $ticket_msg = str_replace('{user}', '', $ticket_msg);
            $ticket_msg = str_replace('{subject}', $ticket->subject, $ticket_msg);
            $ticket_msg = str_replace('{message}', $ticket->message, $ticket_msg);
            $ticket_msg = str_replace('{status}', $ticket->ticket_status->name, $ticket_msg);
            $ticket_msg = str_replace('{priority}', $request->priority, $ticket_msg);
            $ticket_msg = str_replace('{soft_name}', Session::get('name'), $ticket_msg);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($ticket->user->email, $ticket_sub, $ticket_msg);
            }

            /**
            *  Sms to user
            */
            // if (!empty($ticket->user->phone))
            // {
            //     if (!empty($ticketCreationSmsTempInfo->subject) && !empty($ticketCreationSmsTempInfo->body))
            //     {
            //         $ticketCreationSmsTempInfo_sub = $ticketCreationSmsTempInfo->subject;
            //         $ticketCreationSmsTempInfo_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticketCreationSmsTempInfo->body);
            //     }
            //     else
            //     {
            //         $ticketCreationSmsTempInfo_sub = $ticketCreationEnglishSmsTempInfo->subject;
            //         $ticketCreationSmsTempInfo_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticketCreationEnglishSmsTempInfo->body);
            //     }
            //     $ticketCreationSmsTempInfo_msg = str_replace('{ticket_code}', $ticket->code, $ticketCreationSmsTempInfo_msg);
            //     $ticketCreationSmsTempInfo_msg = str_replace('{assigned/created}', 'created', $ticketCreationSmsTempInfo_msg);
            //     $ticketCreationSmsTempInfo_msg = str_replace('{to/for}', 'for', $ticketCreationSmsTempInfo_msg);

            //     if (checkAppSmsEnvironment())
            //     {
            //         if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
            //         {
            //             sendSMS(getNexmoDetails()->default_nexmo_phone_number, $ticket->user->phone, $ticketCreationSmsTempInfo_msg);
            //         }
            //     }
            // }
        }

        $this->helper->one_time_message('success', 'Ticket Created Successfully!');
        return redirect()->intended('admin/tickets/list');
    }

    public function reply($id)
    {
        // dd($id);

        $data['menu'] = 'ticket';

        $data['ticket'] = $ticket = Ticket::find($id);

        $data['ticket_status'] = $ticket_status = TicketStatus::get();

        $data['ticket_replies'] = $ticket_replies = TicketReply::where(['ticket_id' => $id])->orderBy('id', 'desc')->get();

        return view('admin.tickets.reply', $data);
    }

    public function change_ticket_status(Request $request)
    {
        if ($request->status_id && $request->ticket_id)
        {
            $update = Ticket::where(['id' => $request->ticket_id])->update(['ticket_status_id' => $request->status_id]);

            if ($update)
            {
                $status          = TicketStatus::select('name')->where(['id' => $request->status_id])->first();
                $data['message'] = $status->name;
                $data['status']  = '1';
            }
            else
            {
                $data['status'] = '0';
            }
            return $data;
        }
    }

    public function adminTicketReply(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'file'    => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,gif,bmp|max:10000',
        ]);

        if (!empty($request->status_id))
        {
            Ticket::where(['id' => $request->ticket_id])
                ->update([
                    'ticket_status_id' => $request->status_id,
                    'last_reply'       => date('Y-m-d H:i:s'),
                ]);
        }

        // Store in Ticket Replies Table
        $ticket_reply            = new TicketReply();
        $ticket_reply->admin_id  = Auth::guard('admin')->user()->id;
        $ticket_reply->user_id   = $request->user_id;
        $ticket_reply->ticket_id = $request->ticket_id;
        $ticket_reply->message   = $request->message;
        $ticket_reply->save();

        $path = '';

        // Store in Files Table
        if ($request->hasFile('file'))
        {
            $fileName     = $request->file('file');
            $originalName = $fileName->getClientOriginalName();
            $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
            $file_extn    = strtolower($fileName->getClientOriginalExtension());

            if ($file_extn == 'docx' || $file_extn == 'rtf' || $file_extn == 'doc' || $file_extn == 'pdf' || $file_extn == 'png'
            || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'gif' || $file_extn == 'bmp')
            {
                $path = 'uploads/ticketFile';

                $uploadPath = public_path($path); //problem
                $fileName->move($uploadPath, $uniqueName);

                $file                  = new File();
                $file->admin_id        = Auth::guard('admin')->user()->id;
                $file->user_id         = $request->user_id;
                $file->ticket_id       = $request->ticket_id;
                $file->ticket_reply_id = $ticket_reply->id;
                $file->filename        = $uniqueName;
                $file->originalname    = $originalName;
                $file->type            = $file_extn;
                $file->save();
            }
            else
            {
                $this->helper->one_time_message('error', 'Invalid File Format!');
            }
        }

        /**
         * Reply Mail for user
         */
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 12, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

        $ticket_reply_temp = EmailTemplate::where([
            'temp_id'     => 12,
            'language_id' => Session::get('default_language'),
            'type'        => 'email',
        ])->select('subject', 'body')->first();

        if (!empty($ticket_reply_temp->subject) && !empty($ticket_reply_temp->body))
        {
            $ticket_reply_sub = $ticket_reply_temp->subject;
            $ticket_reply_msg = str_replace('{user}', $ticket_reply->user->first_name . ' ' . $ticket_reply->user->last_name, $ticket_reply_temp->body); //
        }
        else
        {
            $ticket_reply_sub = $englishSenderLanginfo->subject;
            $ticket_reply_msg = str_replace('{user}', $ticket_reply->user->first_name . ' ' . $ticket_reply->user->last_name, $englishSenderLanginfo->body); //
        }
        $ticket_reply_msg = str_replace('{ticket_code}', $ticket_reply->ticket->code, $ticket_reply_msg);
        $ticket_reply_msg = str_replace('{assignee}', $ticket_reply->admin->username, $ticket_reply_msg);
        $ticket_reply_msg = str_replace('{message}', $ticket_reply->message, $ticket_reply_msg);
        $ticket_reply_msg = str_replace('{status}', $ticket_reply->ticket->ticket_status->name, $ticket_reply_msg);
        $ticket_reply_msg = str_replace('{priority}', $ticket_reply->ticket->priority, $ticket_reply_msg);
        $ticket_reply_msg = str_replace('{soft_name}', Session::get('name'), $ticket_reply_msg);

        if ($request->file('file'))
        {
            if (checkAppMailEnvironment())
            {
                $this->email->sendEmailWithAttachment($ticket_reply->user->email, $ticket_reply_sub, $ticket_reply_msg, $path, $file->filename);
            }
        }
        else
        {
            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($ticket_reply->user->email, $ticket_reply_sub, $ticket_reply_msg);
            }
        }

        /**
         * Reply Sms for user
         */
        // $adminTicketReplyEnglishSmsTempInfo = EmailTemplate::where(['temp_id' => 12, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
        // $adminTicketReplySmsTempInfo        = EmailTemplate::where(['temp_id' => 12, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

        // if (!empty($ticket_reply->user->phone))
        // {
        //     if (!empty($adminTicketReplySmsTempInfo->subject) && !empty($adminTicketReplySmsTempInfo->body))
        //     {
        //         $adminTicketReplySmsTempInfo_sub = $adminTicketReplySmsTempInfo->subject;
        //         $adminTicketReplySmsTempInfo_msg = str_replace('{user}', $ticket_reply->user->first_name . ' ' . $ticket_reply->user->last_name, $adminTicketReplySmsTempInfo->body);
        //     }
        //     else
        //     {
        //         $adminTicketReplySmsTempInfo_sub = $adminTicketReplyEnglishSmsTempInfo->subject;
        //         $adminTicketReplySmsTempInfo_msg = str_replace('{user}', $ticket_reply->user->first_name . ' ' . $ticket_reply->user->last_name, $adminTicketReplyEnglishSmsTempInfo->body);
        //     }
        //     $adminTicketReplySmsTempInfo_msg = str_replace('{ticket_code}', $ticket_reply->ticket->code, $adminTicketReplySmsTempInfo_msg);

        //     if (checkAppSmsEnvironment())
        //     {
        //         if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
        //         {
        //             sendSMS(getNexmoDetails()->default_nexmo_phone_number, $ticket_reply->user->phone, $adminTicketReplySmsTempInfo_msg);
        //         }
        //     }
        // }

        $this->helper->one_time_message('success', 'Ticket Reply Saved Successfully!');
        return redirect()->back();
    }

    public function replyUpdate(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
        ]);
        if (isset($request->id))
        {
            $ticket_reply          = TicketReply::find($request->id);
            $ticket_reply->message = $request->message;
            $ticket_reply->save();

            // TicketReply::where(['id' => $request->id])->update(['message' => $request->message]);

            $this->helper->one_time_message('success', 'Ticket Reply Updated Successfully!');
            return redirect()->back();
        }
    }

    public function replyDelete(Request $request)
    {
        // dd($request->all());

        if (isset($request->id) && isset($request->ticket_id))
        {
            //If file exists then delete
            $file = File::where(['ticket_reply_id' => $request->id, 'ticket_id' => $request->ticket_id])->first();

            if (!empty($file))
            {
                @unlink(public_path() . '/uploads/ticketFile/' . $file->filename);

                File::where(['ticket_reply_id' => $request->id, 'ticket_id' => $request->ticket_id])->delete();
            }
            //Delete Ticket Reply
            $data = TicketReply::where(['id' => $request->id, 'ticket_id' => $request->ticket_id])->first();
            if (!empty($data))
            {
                TicketReply::where(['id' => $request->id, 'ticket_id' => $request->ticket_id])->delete();

                $this->helper->one_time_message('success', 'Ticket Reply Deleted Successfully');
                return redirect()->back();
            }
        }
    }

    public function edit($id)
    {
        $data['menu'] = 'ticket';

        $data['ticket'] = $ticket = Ticket::find($id);
        // dd($ticket);

        $data['user'] = $user = User::where(['id' => $ticket->user_id, 'status' => 'Active'])->first();
        // dd($user);

        $data['admins'] = Admin::where(['status' => 'Active'])->get();

        $data['ticket_statuses'] = TicketStatus::get();

        return view('admin.tickets.edit', $data);
    }

    public function update(Request $request)
    {
        // return $request->all();

        $this->validate($request, [
            'subject' => 'required',
            'message' => 'required',
        ]);

        $ticket                   = Ticket::find($request->id);
        $ticket->admin_id         = $request->assignee;
        $ticket->user_id          = $request->user_id;
        $ticket->ticket_status_id = $request->status;
        $ticket->subject          = $request->subject;
        $ticket->message          = $request->message;
        $ticket->priority         = $request->priority;
        $ticket->save();

        /**
         * Mail to assignee and user on update
         */

        //if other language's subject and body not set, get en sub and body for mail
        $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 11, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

        $ticket_info = EmailTemplate::where([
            'temp_id'     => 11,
            'language_id' => Session::get('default_language'),
            'type' => 'email',
        ])->select('subject', 'body')->first();

        // Mail to assignee
        if (isset($ticket->admin_id))
        {
            if (!empty($ticket_info->subject) && !empty($ticket_info->body))
            {
                $ticket_sub = $ticket_info->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->admin->username, $ticket_info->body); //
            }
            else
            {
                $ticket_sub = $englishSenderLanginfo->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->admin->username, $englishSenderLanginfo->body); //
            }
            $ticket_msg = str_replace('{ticket_code}', $ticket->code, $ticket_msg);
            $ticket_msg = str_replace('{assigned/created}', 'assigned', $ticket_msg);
            $ticket_msg = str_replace('{to/for}', 'to', $ticket_msg);
            $ticket_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $ticket_msg);
            $ticket_msg = str_replace('{Assignee:}', '', $ticket_msg);
            $ticket_msg = str_replace('{assignee}', '', $ticket_msg);
            $ticket_msg = str_replace('{User:}', 'User:', $ticket_msg);
            $ticket_msg = str_replace('{user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticket_msg);
            $ticket_msg = str_replace('{subject}', $ticket->subject, $ticket_msg);
            $ticket_msg = str_replace('{message}', $ticket->message, $ticket_msg);
            $ticket_msg = str_replace('{status}', $ticket->ticket_status->name, $ticket_msg);
            $ticket_msg = str_replace('{priority}', $request->priority, $ticket_msg);
            $ticket_msg = str_replace('{soft_name}', Session::get('name'), $ticket_msg);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($ticket->admin->email, $ticket_sub, $ticket_msg);
            }
        }

        // Mail to user
        if (isset($ticket->user_id))
        {
            if (!empty($ticket_info->subject) && !empty($ticket_info->body))
            {
                $ticket_sub = $ticket_info->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $ticket_info->body); //
            }
            else
            {
                $ticket_sub = $englishSenderLanginfo->subject;
                $ticket_msg = str_replace('{assignee/user}', $ticket->user->first_name . ' ' . $ticket->user->last_name, $englishSenderLanginfo->body); //
            }
            $ticket_msg = str_replace('{ticket_code}', $ticket->code, $ticket_msg);
            $ticket_msg = str_replace('{assigned/created}', 'created', $ticket_msg);
            $ticket_msg = str_replace('{to/for}', 'for', $ticket_msg);
            $ticket_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $ticket_msg);
            $ticket_msg = str_replace('{Assignee:}', 'Assignee:', $ticket_msg);
            $ticket_msg = str_replace('{assignee}', $ticket->admin->username, $ticket_msg);
            $ticket_msg = str_replace('{User:}', '', $ticket_msg);
            $ticket_msg = str_replace('{user}', '', $ticket_msg);
            $ticket_msg = str_replace('{subject}', $ticket->subject, $ticket_msg);
            $ticket_msg = str_replace('{message}', $ticket->message, $ticket_msg);
            $ticket_msg = str_replace('{status}', $ticket->ticket_status->name, $ticket_msg);
            $ticket_msg = str_replace('{priority}', $request->priority, $ticket_msg);
            $ticket_msg = str_replace('{soft_name}', Session::get('name'), $ticket_msg);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($ticket->user->email, $ticket_sub, $ticket_msg);
            }
        }
        /*
         */

        $this->helper->one_time_message('success', 'Ticket Updated Successfully!');
        return redirect()->intended('admin/tickets/list');
    }

    public function delete($id)
    {
        $ticket = Ticket::find($id);
        if ($ticket)
        {
            $ticket->delete();
            $this->helper->one_time_message('success', 'Ticket Deleted Successfully!');
            return redirect()->intended('admin/tickets/list');
        }
    }
}
