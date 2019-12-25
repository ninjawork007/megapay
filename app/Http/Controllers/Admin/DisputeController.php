<?php

namespace App\Http\Controllers\Admin;

use App;
use App\DataTables\Admin\DisputesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Dispute;
use App\Models\DisputeDiscussion;
use App\Models\EmailTemplate;
use App\Models\Reason;
use App\Models\Transaction;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use Validator;

class DisputeController extends Controller
{
    protected $helper;
    protected $email;
    protected $dispute;

    public function __construct()
    {
        $this->helper  = new Common();
        $this->email   = new EmailController();
        $this->dispute = new Dispute();
    }

    public function index(DisputesDataTable $dataTable)
    {
        $data['menu']     = 'dispute';
        $data['sub_menu'] = 'dispute';

        $data['summary']        = $summary        = Dispute::select('id', 'status')->addSelect(\DB::raw('COUNT(status) as total_status'))->groupBy('status')->get();
        $data['dispute_status'] = $dispute_status = $this->dispute->select('status')->groupBy('status')->get();
        $data['dispute_list']   = $dispute_list   = Dispute::orderBy('id', 'desc')->get();

        if (isset($_GET['btn']))
        {
            $data['user']   = $user   = $_GET['user_id'];
            $data['status'] = $_GET['status'];

            $data['getName'] = $getName = $this->dispute->getDisputesUsersName($user);

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
        return $dataTable->render('admin.dispute.list', $data);
    }

    public function disputesUserSearch(Request $request)
    {
        $search = $request->search;
        $users  = $this->dispute->getDisputesUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($users) > 0)
        {
            $i = 0;

            foreach ($users as $key => $value)
            {
                $array[$i]['id']         = $value->id;
                $array[$i]['first_name'] = $value->first_name;
                $array[$i]['last_name']  = $value->last_name;
                $i++;
            }
            // dd($value);

            $res = [
                'status' => 'success',
                'data'   => $array,
            ];
        }
        return json_encode($res);
    }

    public function add($id)
    {
        $data['menu']        = 'dispute';
        $data['sub_menu']    = 'dispute';
        $defendant           = [];
        $data['transaction'] = $transaction = Transaction::find($id);
        // dd($transaction->dispute->id);

        $data['reasons'] = Reason::all();

        return view('admin.dispute.add', $data);
    }

    public function store(Request $request)
    {
        $rules = array(
            'title'       => 'required',
            'description' => 'required',
        );

        $fieldNames = array(
            'title'       => 'Title',
            'description' => 'Description',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $dispute                 = new Dispute();
            $dispute->claimant_id    = $request->claimant_id;
            $dispute->defendant_id   = $request->defendant_id;
            $dispute->transaction_id = $request->transaction_id;
            $dispute->reason_id      = $request->reason_id;
            $dispute->title          = $request->title;
            $dispute->description    = $request->description;
            $dispute->code           = 'DIS-' . strtoupper(str_random(6));
            // dd($dispute);

            $dispute->save();
            $this->helper->one_time_message('success', 'Dispute Created Successfully!');
            return redirect('admin/disputes');
        }
    }

    public function discussion($id)
    {
        $data['menu']     = 'dispute';
        $data['sub_menu'] = 'dispute';
        $data['dispute']  = $dispute  = Dispute::find($id);

        return view('admin.dispute.discussion', $data);
    }

    public function storeReply(Request $request)
    {
        // dd($request->all());

        $rules = array(
            'description' => 'required',
            'file'        => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,gif,bmp|max:10000',
        );

        $fieldNames = array(
            'description' => 'Message',
            'file'        => 'File',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $file = $request->file('file');

            if (isset($file))
            {
                $fileName        = time() . '_' . $file->getClientOriginalName();
                $file_extn       = strtolower($file->getClientOriginalExtension());

                if ($file_extn == 'docx' || $file_extn == 'rtf' || $file_extn == 'doc' || $file_extn == 'pdf' || $file_extn == 'png' || $file_extn == 'jpg'
                || $file_extn == 'jpeg' || $file_extn == 'gif' || $file_extn == 'bmp')
                {
                    $path            = 'uploads\files';
                    $destinationPath = public_path($path);

                    $file->move($destinationPath, $fileName);
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid File Format!');
                }

            }

            $discussion             = new DisputeDiscussion();
            $discussion->user_id    = Auth::guard('admin')->user()->id;
            $discussion->message    = $request->description;
            $discussion->dispute_id = $request->dispute_id;
            $discussion->file       = isset($fileName) ? $fileName : null;
            $discussion->type       = 'Admin';
            $discussion->save();

            /*
            Mail to both claimant and defandant when admin replying a dispute
             */

            //if other language's subject and body not set, get en sub and body for mail
            $englishSenderLanginfo = EmailTemplate::where(['temp_id' => 13, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

            $dispute_reply_info = EmailTemplate::where([
                'temp_id'     => 13,
                'language_id' => Session::get('default_language'),
                'type'        => 'email',
            ])->select('subject', 'body')->first();

            /**
             * Sms to both claimant and defandant when admin replying a dispute
             */
            if (isset($discussion->dispute->claimant_id))
            {
                // Mail to claimant
                if (!empty($dispute_reply_info->subject) && !empty($dispute_reply_info->body))
                {
                    $dispute_reply_sub = $dispute_reply_info->subject;
                    $dispute_reply_msg = str_replace('{user}', $discussion->dispute->claimant->first_name . ' ' . $discussion->dispute->claimant->last_name, $dispute_reply_info->body); //
                }
                else
                {
                    $dispute_reply_sub = $englishSenderLanginfo->subject;
                    $dispute_reply_msg = str_replace('{user}', $discussion->dispute->claimant->first_name . ' ' . $discussion->dispute->claimant->last_name, $englishSenderLanginfo->body); //
                }
                $dispute_reply_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{Claimant/Defendant:}', 'Defendant:', $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{claimant/defendant}', $discussion->dispute->defendant->first_name . ' ' . $discussion->dispute->defendant->last_name, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{transaction_id}', isset($discussion->dispute->transaction) ? $discussion->dispute->transaction->uuid : "Not Available", $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{subject}', $discussion->dispute->title, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{message}', $discussion->message, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{status}', $discussion->dispute->status, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{soft_name}', Session::get('name'), $dispute_reply_msg);

                if (isset($file))
                {
                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmailWithAttachment($discussion->dispute->claimant->email, $dispute_reply_sub, $dispute_reply_msg, $path, $discussion->file);
                    }
                }
                else
                {
                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($discussion->dispute->claimant->email, $dispute_reply_sub, $dispute_reply_msg);
                    }
                }

            }

            if (isset($discussion->dispute->defendant_id))
            {
                // Mail to defendant
                if (!empty($dispute_reply_info->subject) && !empty($dispute_reply_info->body))
                {
                    $dispute_reply_sub = $dispute_reply_info->subject;
                    $dispute_reply_msg = str_replace('{user}', $discussion->dispute->defendant->first_name . ' ' . $discussion->dispute->defendant->last_name, $dispute_reply_info->body); //
                }
                else
                {
                    $dispute_reply_sub = $englishSenderLanginfo->subject;
                    $dispute_reply_msg = str_replace('{user}', $discussion->dispute->defendant->first_name . ' ' . $discussion->dispute->defendant->last_name, $englishSenderLanginfo->body); //
                }
                $dispute_reply_msg = str_replace('{created_at}', Carbon::now()->toDateString(), $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{Claimant/Defendant:}', 'Claimant:', $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{claimant/defendant}', $discussion->dispute->claimant->first_name . ' ' . $discussion->dispute->claimant->last_name, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{transaction_id}', isset($discussion->dispute->transaction) ? $discussion->dispute->transaction->uuid : "Not Available", $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{subject}', $discussion->dispute->title, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{message}', $discussion->message, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{status}', $discussion->dispute->status, $dispute_reply_msg);
                $dispute_reply_msg = str_replace('{soft_name}', Session::get('name'), $dispute_reply_msg);

                if (isset($file))
                {
                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmailWithAttachment($discussion->dispute->defendant->email, $dispute_reply_sub, $dispute_reply_msg, $path, $discussion->file);
                    }
                }
                else
                {
                    if (checkAppMailEnvironment())
                    {
                        $this->email->sendEmail($discussion->dispute->defendant->email, $dispute_reply_sub, $dispute_reply_msg);
                    }
                }
            }
            $this->helper->one_time_message('success', 'Dispute Reply Saved Successfully!');
            // return redirect('admin/disputes');
            return redirect('admin/dispute/discussion/'. $discussion->dispute->id);
        }
    }

    public function changeReplyStatus(Request $request)
    {
        $dispute         = Dispute::find($request->id);
        $dispute->status = $request->status;
        $dispute->save();

        $data['status'] = 1;

        return json_encode($data);
    }
}
