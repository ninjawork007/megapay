<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Dispute;
use App\Models\DisputeDiscussion;
use App\Models\Reason;
use App\Models\Transaction;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

class DisputeController extends Controller
{
    protected $helper;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function index()
    {
        $data['menu']     = 'dispute';
        $data['sub_menu'] = 'dispute';
        $data['list']     = Dispute::where('claimant_id', Auth::user()->id)
            ->orWhere('defendant_id', Auth::user()->id)
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('user_dashboard.dispute.list', $data);
    }

    public function add($id)
    {
        $data['menu']        = 'dispute';
        $data['sub_menu']    = 'dispute';
        $defendant           = [];
        $data['transaction'] = $transaction = Transaction::find($id);
        $data['reasons']     = Reason::all();

        return view('user_dashboard.dispute.add', $data);
    }

    public function store(Request $request)
    {
        $rules = array(
            'title'          => 'required',
            'reason_id'      => 'required',
            'description'    => 'required',
            'claimant_id'    => 'required',
            'defendant_id'   => 'required',
            'transaction_id' => 'required',
        );

        $fieldNames = array(
            'title'          => 'Title',
            'reason_id'      => 'Reason',
            'description'    => 'Description',
            'claimant_id'    => 'Claimant',
            'defendant_id'   => 'Defendant',
            'transaction_id' => 'Transaction Id',
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
            return redirect('disputes');
        }
    }

    public function discussion($id)
    {
        $data['menu']          = 'dispute';
        $data['sub_menu']      = 'dispute';
        $data['content_title'] = 'Dispute';
        $data['icon']          = 'user';
        $data['dispute']       = $dispute       = Dispute::find($id);
        // dd($dispute);

        return view('user_dashboard.dispute.discussion', $data);
    }

    public function storeReply(Request $request)
    {

        $rules = array(
            'description' => 'required',
            'file'        => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,gif,bmp|max:10000',
        );

        $fieldNames = array(
            'description' => 'Message',
            'file'        => 'The file must be an image (docx, rtf, doc, pdf, png, jpg, jpeg, gif,bmp)',
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
                $ext = $file->getClientOriginalExtension();
                // dd($ext);

                if ($ext == 'docx' || $ext == 'rtf' || $ext == 'doc' || $ext == 'pdf' || $ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp')
                {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $destinationPath = public_path('uploads/files');
                    $file->move($destinationPath, $fileName);
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid Image Format!');
                }
            }
            $discussion             = new DisputeDiscussion();
            $discussion->user_id    = Auth::user()->id;
            $discussion->message    = $request->description;
            $discussion->dispute_id = $request->dispute_id;
            $discussion->file       = isset($fileName) ? $fileName : null;
            $discussion->type       = 'User';
            $discussion->save();

            $this->helper->one_time_message('success', 'Reply Created Successfully!');
            return redirect('dispute/discussion/' . $request->dispute_id);
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
