<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\IdentityProofsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\DocumentVerification;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class IdentityProofController extends Controller
{
    protected $helper;
    protected $documentVerification;
    protected $email;

    public function __construct()
    {
        $this->helper               = new Common();
        $this->documentVerification = new DocumentVerification();
        $this->email                = new EmailController();
    }

    public function index(IdentityProofsDataTable $dataTable)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'identity-proofs';

        $data['documentVerificationStatus'] = $documentVerificationStatus = $this->documentVerification->where(['verification_type' => 'identity'])->select('status')->groupBy('status')->get();

        if (isset($_GET['btn']))
        {
            $data['status'] = $_GET['status'];

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
        }
        return $dataTable->render('admin.verifications.identity_proofs.list', $data);
    }

    public function identityProofsCsv()
    {
        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $data['identityProofs'] = $identityProofs = $this->documentVerification->getDocumentVerificationsList($from, $to, $status)->orderBy('id', 'desc')->get();
        // dd($identityProofs);

        $datas = [];
        if (!empty($identityProofs))
        {
            foreach ($identityProofs as $key => $value)
            {
                $datas[$key]['Date'] = dateFormat($value->created_at);

                $datas[$key]['User'] = isset($value->user) ? $value->user->first_name . ' ' . $value->user->last_name : "-";

                $datas[$key]['Identity Type'] = str_replace('_', ' ', ucfirst($value->identity_type));

                $datas[$key]['Identity Number'] = $value->identity_number;

                if ($value->status == 'approved')
                {
                    $status = 'Approved';
                }
                elseif ($value->status == 'pending')
                {
                    $status = 'Pending';
                }
                elseif ($value->status == 'rejected')
                {
                    $status = 'Rejected';
                }
                $datas[$key]['Status'] = $status;
            }
        }
        else
        {
            $datas[0]['Date']            = '';
            $datas[0]['User']            = '';
            $datas[0]['Identity Type']   = '';
            $datas[0]['Identity Number'] = '';
            $datas[0]['Status']          = '';
        }
        // dd($datas);

        return Excel::create('identity_proofs_list_' . time() . '', function ($excel) use ($datas)
        {
            $excel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $excel->sheet('mySheet', function ($sheet) use ($datas)
            {
                $sheet->cells('A1:E1', function ($cells)
                {
                    $cells->setFontWeight('bold');
                });
                $sheet->fromArray($datas);
            });
        })->download();
    }

    public function identityProofsPdf()
    {
        $data['company_logo'] = getCompanyLogoWithoutSession();

        $from = !empty($_GET['startfrom']) ? setDateForDb($_GET['startfrom']) : null;
        $to   = !empty($_GET['endto']) ? setDateForDb($_GET['endto']) : null;

        $status = isset($_GET['status']) ? $_GET['status'] : null;

        $data['identityProofs'] = $identityProofs = $this->documentVerification->getDocumentVerificationsList($from, $to, $status)->orderBy('id', 'desc')->get();

        if (isset($from) && isset($to))
        {
            $data['date_range'] = $from . ' To ' . $to;
        }
        else
        {
            $data['date_range'] = 'N/A';
        }

        $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);

        $mpdf = new \Mpdf\Mpdf([
            'mode'        => 'utf-8',
            'format'      => 'A3',
            'orientation' => 'P',
        ]);

        $mpdf->autoScriptToLang         = true;
        $mpdf->autoLangToFont           = true;
        $mpdf->allow_charset_conversion = false;

        $mpdf->WriteHTML(view('admin.verifications.identity_proofs.identity_proofs_report_pdf', $data));

        $mpdf->Output('identity_proofs_report_' . time() . '.pdf', 'D');
    }

    public function identityProofEdit($id)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'identity-proofs';

        $data['documentVerification'] = $documentVerification = DocumentVerification::find($id);
        // dd($documentVerification);

        return view('admin.verifications.identity_proofs.edit', $data);
    }

    public function identityProofUpdate(Request $request)
    {
        // dd($request->all());
        $documentVerification         = DocumentVerification::find($request->id);
        $documentVerification->status = $request->status;
        $documentVerification->save();

        $user = User::find($request->user_id);
        if ($request->verification_type == 'identity')
        {
            if ($request->status == 'approved')
            {
                $user->identity_verified = true;
            }
            else
            {
                $user->identity_verified = false;
            }
        }
        $user->save();

        if (checkDemoEnvironment() != true)
        {
            /**
             * Mail
             */
            $englishIdentityVerificationEmailTemp = EmailTemplate::where(['temp_id' => 21, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
            $identityVerificationEmailTemp        = EmailTemplate::where(['temp_id' => 21, 'language_id' => Session::get('default_language'), 'type' => 'email'])->select('subject', 'body')->first();

            if (!empty($identityVerificationEmailTemp->subject) && !empty($identityVerificationEmailTemp->body))
            {
                $identityVerificationEmailSub  = str_replace('{Identity/Address}', 'Identity', $identityVerificationEmailTemp->subject);
                $identityVerificationEmailBody = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $identityVerificationEmailTemp->body);
            }
            else
            {
                $identityVerificationEmailSub  = str_replace('{Identity/Address}', 'Identity', $englishIdentityVerificationEmailTemp->subject);
                $identityVerificationEmailBody = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $englishIdentityVerificationEmailTemp->body);
            }
            $identityVerificationEmailBody = str_replace('{Identity/Address}', 'Identity', $identityVerificationEmailBody);
            $identityVerificationEmailBody = str_replace('{approved/pending/rejected}', ucfirst($request->status), $identityVerificationEmailBody);
            $identityVerificationEmailBody = str_replace('{soft_name}', Session::get('name'), $identityVerificationEmailBody);

            if (checkAppMailEnvironment())
            {
                $this->email->sendEmail($user->email, $identityVerificationEmailSub, $identityVerificationEmailBody);
            }

            /**
             * SMS
             */
            $englishIdentityVerificationSmsTemp = EmailTemplate::where(['temp_id' => 21, 'lang' => 'en', 'type' => 'sms'])->select('subject', 'body')->first();
            $identityVerificationSmsTemp        = EmailTemplate::where(['temp_id' => 21, 'language_id' => Session::get('default_language'), 'type' => 'sms'])->select('subject', 'body')->first();

            if (!empty($identityVerificationSmsTemp->subject) && !empty($identityVerificationSmsTemp->body))
            {
                $identityVerificationSmsSub  = str_replace('{Identity/Address}', 'Identity', $identityVerificationSmsTemp->subject);
                $identityVerificationSmsBody = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $identityVerificationSmsTemp->body);
            }
            else
            {
                $identityVerificationSmsSub  = str_replace('{Identity/Address}', 'Identity', $englishIdentityVerificationSmsTemp->subject);
                $identityVerificationSmsBody = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $englishIdentityVerificationSmsTemp->body);
            }
            $identityVerificationSmsBody = str_replace('{Identity/Address}', 'Identity', $identityVerificationSmsBody);
            $identityVerificationSmsBody = str_replace('{approved/pending/rejected}', ucfirst($request->status), $identityVerificationSmsBody);

            if (!empty($user->carrierCode) && !empty($user->phone))
            {
                if (checkAppSmsEnvironment())
                {
                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                    {
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $user->carrierCode . $user->phone, $identityVerificationSmsBody);
                    }
                }
            }

        }

        $this->helper->one_time_message('success', 'Identity Verified Successfully!');
        return redirect('admin/identity-proofs');
    }
}
