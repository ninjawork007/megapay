<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\Country;
use App\Models\DeviceLog;
use App\Models\DocumentVerification;
use App\Models\EmailTemplate;
use App\Models\File;
use App\Models\Preference;
use App\Models\TimeZone;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Wallet;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Image;

class CustomerController extends Controller
{

    protected $helper;
    protected $twoFa;
    protected $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->twoFa  = new LoginController();
        $this->email  = new EmailController();
    }

    public function view2fa()
    {
        return view('user_dashboard.auth.2sa.verify');
    }

    public function verify2fa(Request $request)
    {
        // dd($request->all());

        $validation = Validator::make($request->all(), [
            'two_step_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            \Session::put('2fa', '2fa');
            if ($request->two_step_verification_code == auth()->user()->user_detail->two_step_verification_code)
            {
                $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first();

                if ($request->remember_me == "true")
                {
                    $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
                    if (empty($checkDeviceLog))
                    {
                        $deviceLog                      = new DeviceLog();
                        $deviceLog->user_id             = auth()->user()->id;
                        $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                        $deviceLog->browser_agent       = $request->header('user-agent');
                        $deviceLog->ip                  = $request->ip();
                        $deviceLog->save();
                    }
                }

                if ($userDetail->two_step_verification == 0)
                {
                    $userDetail->two_step_verification = 1;
                }
                $userDetail->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('User Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Google2fa after login- start
    public function viewGoogle2fa()
    {
        return view('user_dashboard.auth.google2fa.verify');
    }

    public function verifyGoogle2fa(Request $request)
    {
        $user                   = User::find(auth()->user()->id);
        $user->google2fa_secret = $request->google2fa_secret;
        $user->save();

        return response()->json([
            'status' => true,
        ]);
    }

    public function verifyGoogle2faOtp(Request $request)
    {
        // dd($request->all());

        \Session::put('2fa', '2fa');
        if ($request->remember_otp == "true")
        {
            $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
            // dd($checkDeviceLog);
            if (empty($checkDeviceLog))
            {
                $deviceLog                      = new DeviceLog();
                $deviceLog->user_id             = auth()->user()->id;
                $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                $deviceLog->browser_agent       = $request->header('user-agent');
                $deviceLog->ip                  = $request->ip();
                $deviceLog->save();
            }
        }

        $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
        $userDetail->two_step_verification_type = $request->two_step_verification_type;
        if ($userDetail->two_step_verification == 0)
        {
            $userDetail->two_step_verification = 1;
        }
        $userDetail->save();

        return response()->json([
            'status'  => true,
            'message' => __('User Verified Successfully!'),
        ]);
    }
    //Google2fa after login - end

    public function dashboard()
    {
        // dd(session()->all());

        $data['menu']         = 'dashboard';
        $data['title']        = 'Dashboard';
        $data['wallets']      = $wallets      = Wallet::with('currency:id,name,logo,code')->where(['user_id' => Auth::user()->id])->orderBy('balance', 'ASC')
        ->get(['currency_id','balance','is_default']);
    	// dd($data['wallets']);
        $transaction          = new Transaction();
        $data['transactions'] = $transaction->dashboardTransactionList();
        // dd($data['transactions']);
        return view('user_dashboard.layouts.dashboard', $data);
    }

    public function profile()
    {
        $data['menu']     = 'profile';
        $data['sub_menu'] = 'profile';
        $data['user']     = $user     = User::find(Auth::user()->id);

        $data['timezones'] = phpDefaultTimeZones();

        $data['is_sms_env_enabled'] = $is_sms_env_enabled = checkAppSmsEnvironment();

        $data['checkPhoneVerification'] = $checkPhoneVerification = checkPhoneVerification();

        $data['countries'] = Country::orderBy('name', 'asc')->get();

        $data['two_step_verification'] = $two_step_verification = Preference::where(['category' => 'preference', 'field' => 'two_step_verification'])->first(['value'])->value;
        // dd($two_step_verification);

        $data['wallets'] = $wallets = Wallet::with('currency:id,name')->where(['user_id' => Auth::user()->id])->orderBy('balance', 'ASC')->get(['id', 'currency_id', 'is_default']);

        return view('user_dashboard.users.profile', $data);
    }

    public function profileTwoFa()
    {
        $data['menu']     = 'profile';
        $data['sub_menu'] = 'profile';

        $data['user'] = $user = User::find(Auth::user()->id);

        $data['two_step_verification'] = $two_step_verification = Preference::where(['category' => 'preference', 'field' => 'two_step_verification'])->first(['value'])->value;

        if (!empty(auth()->user()->device_log->browser_fingerprint))
        {
            $data['checkDeviceLog'] = $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => auth()->user()->device_log->browser_fingerprint])->first(['browser_fingerprint']);
            // dd($checkDeviceLog);
        }

        $data['is_demo'] = $is_demo = checkDemoEnvironment();
        // dd($is_demo);

        return view('user_dashboard.users.2fa', $data);
    }

    public function disabledTwoFa(Request $request)
    {
        if ($request->ajax())
        {
            $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
            $userDetail->two_step_verification_type = $request->two_step_verification_type;
            $userDetail->save();

            return response()->json([
                'status' => true,
            ]);
        }
    }

    public function ajaxTwoFa(Request $request)
    {
        // dd($request->all());

        // if (auth()->user()->user_detail->two_step_verification && auth()->user()->user_detail->two_step_verification_type !== $request->two_step_verification_type)
        if (auth()->user()->user_detail->two_step_verification_type !== $request->two_step_verification_type)
        {
            $six_digit_random_number = six_digit_random_number();

            auth()->user()->user_detail()->update([
                'two_step_verification_code' => $six_digit_random_number,
            ]);

            if ($request->two_step_verification_type == 'phone')
            {
                //sms
                $message = $six_digit_random_number . ' is your ' . getCompanyName() . ' 2-factor verification code. ';

                if (!empty(auth()->user()->carrierCode) && !empty(auth()->user()->phone))
                {
                    if (checkAppSmsEnvironment() == true)
                    {
                        if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                        {
                            sendSMS(getNexmoDetails()->default_nexmo_phone_number, auth()->user()->carrierCode . auth()->user()->phone, $message);
                        }
                    }
                }
            }
            elseif ($request->two_step_verification_type == 'email')
            {
                //email
                if (checkAppMailEnvironment())
                {
                    $twoStepVerification = EmailTemplate::where([
                        'temp_id'     => 19,
                        'language_id' => getDefaultLanguage(),
                        'type'        => 'email',
                    ])->select('subject', 'body')->first();
                    // dd($twoStepVerification);

                    $englishtwoStepVerification = EmailTemplate::where(['temp_id' => 19, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();

                    if (!empty($twoStepVerification->subject) && !empty($twoStepVerification->body))
                    {
                        $twoStepVerification_sub = $twoStepVerification->subject;
                        $twoStepVerification_msg = str_replace('{user}', auth()->user()->first_name . ' ' . auth()->user()->last_name, $twoStepVerification->body);
                    }
                    else
                    {
                        $twoStepVerification_sub = $englishtwoStepVerification->subject;
                        $twoStepVerification_msg = str_replace('{user}', auth()->user()->first_name . ' ' . auth()->user()->last_name, $englishtwoStepVerification->body);
                    }
                    $twoStepVerification_msg = str_replace('{code}', $six_digit_random_number, $twoStepVerification_msg);
                    $twoStepVerification_msg = str_replace('{soft_name}', getCompanyName(), $twoStepVerification_msg);
                    $this->email->sendEmail(auth()->user()->email, $twoStepVerification_sub, $twoStepVerification_msg);
                }
            }

            //
            if ($request->two_step_verification_type == 'email')
            {
                return response()->json([
                    'status'                           => true,
                    'twoFaVerificationTypeForResponse' => 'email',
                    'twoFa_type'                       => auth()->user()->email,
                ]);
            }
            else
            {
                return response()->json([
                    'status'                           => true,
                    'twoFaVerificationTypeForResponse' => 'phone',
                    'twoFa_type'                       => str_pad(substr(auth()->user()->phone, -2), strlen(auth()->user()->phone), '*', STR_PAD_LEFT),
                ]);
            }
        }
        else
        {
            // dd('false');
            return response()->json([
                'status'                     => false,
                'two_step_verification_type' => auth()->user()->user_detail->two_step_verification_type,
            ]);
        }
    }

    //Google2fa in user profile- start
    public function google2fa(Request $request)
    {
        // dd($request->all());

        if (auth()->user()->user_detail->two_step_verification_type !== $request->two_step_verification_type)
        {
            $google2fa                             = app('pragmarx.google2fa');
            $registration_data                     = $request->all();
            $registration_data["google2fa_secret"] = $google2fa->generateSecretKey();

            $request->session()->flash('registration_data', $registration_data);

            $QR_Image = $google2fa->getQRCodeInline(
                config('app.name'),
                auth()->user()->email,
                $registration_data['google2fa_secret']
            );
            return response()->json([
                'status'                           => true,
                'secret'                           => $registration_data['google2fa_secret'],
                'QR_Image'                         => $QR_Image,
                'twoFaVerificationTypeForResponse' => 'google_authenticator',
            ]);
        }
        else
        {
            // dd('here');
            return response()->json([
                'status' => false,
            ]);
        }
    }

    public function completeGoogle2faVerification(Request $request)
    {
        // dd($request->all());

        $user                   = User::find(auth()->user()->id);
        $user->google2fa_secret = $request->google2fa_secret;
        $user->save();

        return response()->json([
            'status' => true,
        ]);
    }

    public function google2faOtpVerification(Request $request)
    {
        // dd($request->all());
        if ($request->remember_otp == "true")
        {
            $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
            // dd($checkDeviceLog);
            if (empty($checkDeviceLog))
            {
                $deviceLog                      = new DeviceLog();
                $deviceLog->user_id             = auth()->user()->id;
                $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                $deviceLog->browser_agent       = $request->header('user-agent');
                $deviceLog->ip                  = $request->ip();
                $deviceLog->save();
            }
        }

        $userDetail                             = UserDetail::where(['user_id' => auth()->user()->id])->first();
        $userDetail->two_step_verification_type = $request->two_step_verification_type;
        if ($userDetail->two_step_verification == 0)
        {
            $userDetail->two_step_verification = 1;
        }
        $userDetail->save();

        return response()->json([
            'status'  => true,
            'message' => __('User Verified Successfully!'),
        ]);
    }
    //Google2fa in user profile- end

    public function ajaxTwoFaSettingsVerify(Request $request)
    {
        // dd($request->all());

        $validation = Validator::make($request->all(), [
            'two_step_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            if ($request->two_step_verification_code == auth()->user()->user_detail->two_step_verification_code)
            {

                $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first();

                if ($request->remember_me == "true")
                {
                    $checkDeviceLog = DeviceLog::where(['user_id' => auth()->user()->id, 'browser_fingerprint' => $request->browser_fingerprint])->first();
                    if (empty($checkDeviceLog))
                    {
                        $deviceLog                      = new DeviceLog();
                        $deviceLog->user_id             = auth()->user()->id;
                        $deviceLog->browser_fingerprint = $request->browser_fingerprint;
                        $deviceLog->browser_agent       = $request->header('user-agent');
                        $deviceLog->ip                  = $request->ip();
                        $deviceLog->save();
                    }
                }

                if ($userDetail->two_step_verification == 0)
                {
                    $userDetail->two_step_verification = 1;
                }
                $userDetail->two_step_verification_type = $request->twoFaVerificationType;
                $userDetail->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('User Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    public function checkPhoneFor2fa(Request $request)
    {
        if (!empty(auth()->user()->carrierCode) && !empty(auth()->user()->phone))
        {
            return response()->json([
                'status'  => true,
                'message' => __('Phone number is set.'),
            ]);
        }
        else
        {
            return response()->json([
                'status'  => false,
                'message' => __('Please set your phone number first!'),
            ]);
        }
    }

    public function updateProfilePassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required',
        ]);

        $user = User::where(['id' => Auth::user()->id])->first();

        if (Hash::check($request->old_password, $user->password))
        {
            $user->password = Hash::make($request->password);
            $user->save();

            $this->helper->one_time_message('success', __('Password Updated successfully!'));
            return redirect()->intended("profile");
        }
        else
        {
            $this->helper->one_time_message('error', __('Old Password is Wrong!'));
            return redirect()->intended("profile");
        }
    }

    public function profileImage(Request $request)
    {
        if ($request->isMethod('get'))
        {
            return redirect()->intended("profile");
        }
        else
        {
            $validator = Validator::make($request->all(),
                [
                    'file' => 'image|max:5120',
                ],
                [
                    'file.image' => __('The file must be an image (jpeg, png, bmp, gif, or svg)'),
                    'file.max'   => __('The file size must not be greater than 5MB'),
                ]);
            if ($validator->fails())
            {
                return array(
                    'fail'   => true,
                    'errors' => $validator->errors(),
                );
            }
            $filename = '';
            $user     = User::find(Auth::user()->id);

            $picture = $request->file;
            if (isset($picture))
            {
                $ext      = strtolower($picture->getClientOriginalExtension());
                $filename = time() . '.' . $ext;

                $dir1 = public_path('/user_dashboard/profile/' . $filename);
                $dir2 = public_path('/user_dashboard/profile/thumb/' . $filename);

                if (!empty(Auth::user()->picture))
                {
                    if (file_exists($dir1))
                    {
                        unlink($dir1);
                    }

                    if (file_exists($dir2))
                    {
                        unlink($dir2);
                    }
                }

                if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp')
                {
                    $img = Image::make($picture->getRealPath());

                    $img->fit(100, 100, function ($constraint)
                    {
                        $constraint->aspectRatio();
                    })->save($dir1);

                    $img->fit(70, 70, function ($constraint)
                    {
                        $constraint->aspectRatio();
                    })->save($dir2);

                    $user->picture = $filename;
                }
                else
                {
                    // $this->helper->one_time_message('error', 'Invalid Image Format!');
                    return array(
                        'fail'   => true,
                        'errors' => 'Invalid Image Format!',
                    );
                }
            }
            $user->save();
            return $filename;
        }
    }

    public function generatePhoneVerificationCode(Request $request)
    {
        // dd($request->all());

        $six_digit_random_number = six_digit_random_number();

        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (empty($userDetail->phone_verification_code))
        {
            UserDetail::where([
                'user_id' => auth()->user()->id,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        }
        else
        {
            UserDetail::where([
                'user_id'                 => auth()->user()->id,
                'phone_verification_code' => $userDetail->phone_verification_code,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        }

        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        //SMS
        if (!empty($request->phone))
        {
            if (!empty($request->carrierCode) && !empty($request->phone))
            {
                $message = $six_digit_random_number . ' is your ' . getCompanyName() . ' verification code. ';

                $data = [];
                if (checkAppSmsEnvironment() == true && checkPhoneVerification() == "Enabled")
                {
                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                    {
                        $data['status']  = true;
                        $data['message'] = 'Yes';
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request->carrierCode . $phoneFormatted, $message);
                    }
                    else
                    {
                        $data['status']  = false;
                        $data['message'] = 'No';
                    }
                    return json_encode($data);
                }
            }
        }
    }

    public function completePhoneVerification(Request $request)
    {
        // dd($request->all());
        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone_verification_code' => 'required|numeric',
        ]);

        if ($validation->passes())
        {
            $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

            if ($request->phone_verification_code == $userDetail->phone_verification_code)
            {
                $user                 = User::where(['id' => auth()->user()->id])->first();
                $user->phone          = $phoneFormatted; //
                $user->defaultCountry = $request->defaultCountry;
                $user->carrierCode    = $request->carrierCode;
                $user->formattedPhone = $request->phone;
                $user->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('Phone Number Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Without PhoneVerification - Add
    public function addPhoneNumberViaAjax(Request $request)
    {
        // dd($request->all());

        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
        ]);

        if ($validation->passes())
        {
            $user                 = User::findOrFail(auth()->user()->id);
            $user->phone          = $phoneFormatted;
            $user->defaultCountry = $request->defaultCountry;
            $user->carrierCode    = $request->carrierCode;
            $user->formattedPhone = $request->phone;
            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => __('Phone Number Added Successfully!'),
                'class_name' => 'alert-success',
            ]);
        }
        else
        {
            return response()->json([
                'status'     => false,
                'message'    => $validation->errors()->all(),
                'class_name' => 'alert-danger',
            ]);
        }
    }

    public function editGeneratePhoneVerificationCode(Request $request)
    {
        // dd($request->all());

        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $six_digit_random_number = six_digit_random_number();

        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (!empty($userDetail))
        {
            UserDetail::where([
                'user_id'                 => auth()->user()->id,
                'phone_verification_code' => $userDetail->phone_verification_code,
            ])->update(['phone_verification_code' => $six_digit_random_number]);
        }

        //SMS
        if (!empty($request->phone))
        {
            if (!empty($request->code) && !empty($request->phone))
            {
                $message = $six_digit_random_number . ' is your ' . getCompanyName() . ' verification code. ';

                $data = [];
                if (checkAppSmsEnvironment() == true && checkPhoneVerification() == "Enabled")
                {
                    if (getNexmoDetails()->is_nexmo_default == 'Yes' && getNexmoDetails()->nexmo_status == 'Active')
                    {
                        sendSMS(getNexmoDetails()->default_nexmo_phone_number, $request->code . $phoneFormatted, $message);

                        $data['status']  = true;
                        $data['message'] = 'Yes';
                    }
                    else
                    {
                        $data['status']  = false;
                        $data['message'] = 'No';
                    }
                    return json_encode($data);
                }
            }
        }
    }

    public function editCompletePhoneVerification(Request $request)
    {
        // dd($request->all());

        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $rules = array(
            'edit_phone_verification_code' => 'required|numeric',
        );

        $fieldNames = array(
            'edit_phone_verification_code' => __('phone verification code'),
        );
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->passes())
        {
            $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

            if ($request->edit_phone_verification_code == $userDetail->phone_verification_code)
            {
                $user                 = User::where(['id' => auth()->user()->id])->first();
                $user->phone          = $phoneFormatted;
                $user->defaultCountry = $request->flag;
                $user->carrierCode    = $request->code;
                $user->formattedPhone = $request->phone;
                $user->save();

                return response()->json([
                    'status'  => true,
                    'message' => __('Phone Number Verified Successfully!'),
                    'success' => "alert-success",
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification Code Does Not Match!'),
                    'error'   => "alert-danger",
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 500,
                'message' => $validator->errors()->all(),
                'error'   => "alert-danger",
            ]);
        }
    }

    //Without PhoneVerification - Update
    public function updatePhoneNumberViaAjax(Request $request)
    {
        // dd($request->all());

        $phoneFormatted = str_replace('+' . $request->code, "", $request->phone);

        $validation = Validator::make($request->all(), [
            'phone' => 'unique:users,phone,' . auth()->user()->id,
        ]);

        if ($validation->passes())
        {
            $user = User::findOrFail(auth()->user()->id);

            /*phone*/
            $user->phone          = $phoneFormatted;
            $user->defaultCountry = $request->flag;
            $user->carrierCode    = $request->code;
            $user->formattedPhone = $request->phone;
            /**/

            $user->save();

            return response()->json([
                'status'     => true,
                'message'    => __('Phone Number Updated Successfully!'),
                'class_name' => 'alert-success',
            ]);
        }
        else
        {
            return response()->json([
                'status'     => false,
                'message'    => $validation->errors()->all(),
                'class_name' => 'alert-danger',
            ]);
        }
    }

    public function deletePhoneNumberViaAjax(Request $request)
    {
        // dd($request->all());

        $user       = User::find(auth()->user()->id, ['phone', 'carrierCode', 'defaultCountry']);
        $userDetail = UserDetail::where(['user_id' => auth()->user()->id])->first(['phone_verification_code']);

        if (!empty($user))
        {
            User::where(['id' => auth()->user()->id])->update([
                'phone'          => null,
                'carrierCode'    => null,
                'defaultCountry' => null,
            ]);

            if (!empty($userDetail))
            {
                UserDetail::where([
                    'user_id'                 => auth()->user()->id,
                    'phone_verification_code' => auth()->user()->user_detail->phone_verification_code,
                ])->update(['phone_verification_code' => null]);
            }
            return response()->json([
                'status'  => 'success',
                'message' => __('Phone Deleted Successfully!'),
            ]);
        }
        else
        {
            return response()->json([
                'status'  => 'error',
                'message' => __('Unable To Delete Phone!'),
            ]);
        }
    }

    public function userDuplicatePhoneNumberCheck(Request $request)
    {
        // dd($request->all());
        $req_id = $request->id;

        if (isset($req_id))
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->where(function ($query) use ($req_id)
            {
                $query->where('id', '!=', $req_id);
            })->first(['phone', 'carrierCode']);
            // dd($user);
        }
        else
        {
            $user = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone), 'carrierCode' => $request->carrierCode])->first(['phone', 'carrierCode']);
        }

        if (!empty($user->phone) && !empty($user->carrierCode))
        {
            $data['status'] = true;
            $data['fail']   = __("The phone number has already been taken!");
        }
        else
        {
            $data['status']  = false;
            $data['success'] = __("The phone number is Available!");
        }
        return json_encode($data);
    }

    public function logout()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        // \Session::flush();
        \Auth::logout();
        return redirect('login');
    }

    //Personal Identity Verification - start
    public function personalId()
    {
        $data['menu']     = 'profile';
        $data['sub_menu'] = 'profile';

        $data['user']                  = $user                  = User::find(Auth::user()->id);
        $data['two_step_verification'] = $two_step_verification = Preference::where(['category' => 'preference', 'field' => 'two_step_verification'])->first(['value'])->value;
        $data['documentVerification']  = $documentVerification  = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first();

        return view('user_dashboard.users.personal_id', $data);
    }

    public function updatePersonalId(Request $request)
    {
        // dd($request->all());

        if ($_POST)
        {

            //make identity_verified false every time a user updates
            $user                    = User::find(auth()->user()->id);
            $user->identity_verified = false;
            $user->save();

            $this->validate($request, [
                'identity_type'   => 'required',
                'identity_number' => 'required|numeric',
                'identity_file'   => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,csv,txt,gif,bmp|max:10000',
            ]);

            $fileId = $this->insertUserIdentityInfoToFilesTable($request->identity_file);

            $documentVerification = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first();
            if (empty($documentVerification))
            {
                $createDocumentVerification          = new DocumentVerification();
                $createDocumentVerification->user_id = $request->user_id;
                if (!empty($request->identity_file))
                {
                    $createDocumentVerification->file_id = $fileId;
                }
                $createDocumentVerification->verification_type = 'identity';
                $createDocumentVerification->identity_type     = $request->identity_type;
                $createDocumentVerification->identity_number   = $request->identity_number;
                $createDocumentVerification->status            = 'pending';
                $createDocumentVerification->save();
            }
            else
            {
                $documentVerification->user_id = $request->user_id;
                if (!empty($request->identity_file))
                {
                    $documentVerification->file_id = $fileId;
                }
                $documentVerification->verification_type = 'identity';
                $documentVerification->identity_type     = $request->identity_type;
                $documentVerification->identity_number   = $request->identity_number;
                $documentVerification->status            = 'pending';
                $documentVerification->save();
            }

        }
        $this->helper->one_time_message('success', __('User Identity Updated Successfully'));
        return redirect('profile/personal-id');
    }

    protected function insertUserIdentityInfoToFilesTable($identity_file)
    {
        if (!empty($identity_file))
        {
            $request = app(\Illuminate\Http\Request::class);
            if ($request->hasFile('identity_file'))
            {
                $fileName     = $request->file('identity_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());

                if ($file_extn == 'docx' || $file_extn == 'rtf' || $file_extn == 'doc' || $file_extn == 'pdf' || $file_extn == 'png' || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'csv'
                    || $file_extn == 'txt' || $file_extn == 'gif' || $file_extn == 'bmp')
                {
                    $path       = 'uploads/user-documents/identity-proof-files';
                    $uploadPath = public_path($path); //problem
                    $fileName->move($uploadPath, $uniqueName);

                    if (isset($request->existingIdentityFileID))
                    {
                        // dd($request->existingIdentityFileID);
                        $checkExistingFile               = File::where(['id' => $request->existingIdentityFileID])->first();
                        $checkExistingFile->filename     = $uniqueName;
                        $checkExistingFile->originalname = $originalName;
                        $checkExistingFile->save();
                        return $checkExistingFile->id;
                    }
                    else
                    {
                        $file               = new File();
                        $file->user_id      = $request->user_id;
                        $file->filename     = $uniqueName;
                        $file->originalname = $originalName;
                        $file->type         = $file_extn;
                        $file->save();
                        return $file->id;
                    }
                }
                else
                {
                    $this->helper->one_time_message('error', __('Invalid File Format!'));
                }
            }
        }
    }
    //Personal Identity Verification - end

    //Personal Address Verification - start
    public function personalAddress()
    {
        $data['menu']     = 'profile';
        $data['sub_menu'] = 'profile';

        $data['user'] = $user = User::find(Auth::user()->id);

        $data['two_step_verification'] = $two_step_verification = Preference::where(['category' => 'preference', 'field' => 'two_step_verification'])->first(['value'])->value;

        $data['documentVerification'] = $documentVerification = DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'address'])->first(['file_id']);

        return view('user_dashboard.users.personal_address', $data);
    }

    public function updatePersonalAddress(Request $request)
    {
        // dd($request->all());

        if ($_POST)
        {
            //make identity_verified false every time a user updates
            $user                   = User::find(auth()->user()->id, ['id', 'address_verified']);
            $user->address_verified = false;
            $user->save();

            $this->validate($request, [
                'address_file' => 'mimes:docx,rtf,doc,pdf,png,jpg,jpeg,csv,txt,gif,bmp|max:10000',
            ]);

            $addressFileId = $this->insertUserAddressProofToFilesTable($request->address_file);
            // dd($addressFileId);

            $documentVerification = DocumentVerification::where(['user_id' => $user->id, 'verification_type' => 'address'])->first();
            if (empty($documentVerification))
            {
                $createDocumentVerification          = new DocumentVerification();
                $createDocumentVerification->user_id = $request->user_id;
                if (!empty($request->address_file))
                {
                    $createDocumentVerification->file_id = $addressFileId;
                }
                $createDocumentVerification->verification_type = 'address';
                $createDocumentVerification->status            = 'pending';
                $createDocumentVerification->save();
            }
            else
            {
                $documentVerification->user_id = $request->user_id;
                if (!empty($request->address_file))
                {
                    $documentVerification->file_id = $addressFileId;
                }
                $documentVerification->status = 'pending';
                $documentVerification->save();
            }
        }
        $this->helper->one_time_message('success', __('User Address Poof Updated Successfully'));
        return redirect('profile/personal-address');
    }

    protected function insertUserAddressProofToFilesTable($address_file)
    {
        if (!empty($address_file))
        {
            $request = app(\Illuminate\Http\Request::class);
            if ($request->hasFile('address_file'))
            {
                $fileName     = $request->file('address_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $file_extn    = strtolower($fileName->getClientOriginalExtension());

                if ($file_extn == 'docx' || $file_extn == 'rtf' || $file_extn == 'doc' || $file_extn == 'pdf' || $file_extn == 'png' || $file_extn == 'jpg' || $file_extn == 'jpeg' || $file_extn == 'csv'
                    || $file_extn == 'txt' || $file_extn == 'gif' || $file_extn == 'bmp')
                {
                    $path       = 'uploads/user-documents/address-proof-files';
                    $uploadPath = public_path($path); //problem
                    $fileName->move($uploadPath, $uniqueName);

                    if (isset($request->existingAddressFileID))
                    {
                        // dd($request->existingAddressFileID);
                        $checkExistingFile = File::where(['id' => $request->existingAddressFileID])->first();
                        // dd($checkExistingFile);
                        $checkExistingFile->filename     = $uniqueName;
                        $checkExistingFile->originalname = $originalName;
                        $checkExistingFile->save();
                        return $checkExistingFile->id;
                    }
                    else
                    {
                        $file               = new File();
                        $file->user_id      = $request->user_id;
                        $file->filename     = $uniqueName;
                        $file->originalname = $originalName;
                        $file->type         = $file_extn;
                        $file->save();
                        return $file->id;
                    }
                }
                else
                {
                    $this->helper->one_time_message('error', __('Invalid File Format!'));
                }
            }
        }
    }
    //Personal Address Verification - end

    public function updateProfileInfo(Request $request)
    {
        // dd($request->all());

        if ($_POST)
        {
            $rules = array(
                'first_name' => 'required',
                'last_name'  => 'required',
            );

            $fieldNames = array(
                'first_name' => 'First Name',
                'last_name'  => 'Last Name',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                try
                {
                    \DB::beginTransaction();

                    $user             = User::findOrFail(Auth::user()->id, ['id', 'first_name', 'last_name']); //optimized
                    $user->first_name = $request->first_name;
                    $user->last_name  = $request->last_name;
                    $user->save();

                    $UserDetail             = UserDetail::with(['user:id','country:id'])
                    ->where(['user_id' => Auth::user()->id])->first(['id', 'user_id', 'country_id', 'address_1', 'address_2', 'city', 'state', 'timezone']); //optimized
                    // dd($UserDetail);

                    $UserDetail->user_id    = Auth::user()->id;
                    $UserDetail->country_id = $request->country_id;
                    $UserDetail->address_1  = $request->address_1;
                    $UserDetail->address_2  = $request->address_2;
                    $UserDetail->city       = $request->city;
                    $UserDetail->state      = $request->state;
                    $UserDetail->timezone   = $request->timezone;
                    $UserDetail->save();

                    //Default wallet change - starts
                    $defaultWallet = Wallet::where('user_id', Auth::user()->id)->where('is_default', 'Yes')->first(['id', 'is_default']);
                    if ($defaultWallet->id != $request->default_wallet)
                    {
                        //making existing default wallet to 'No'
                        $defaultWallet->is_default = 'No';
                        $defaultWallet->save();

                        //Change to default wallet
                        $walletToDefault             = Wallet::find($request->default_wallet, ['id', 'is_default']);
                        $walletToDefault->is_default = 'Yes';
                        $walletToDefault->save();
                    }
                    //Default wallet change - ends
                    \DB::commit();
                }
                catch (\Exception $e)
                {
                    \DB::rollBack();
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect('profile');
                }
            }
        }
        $this->helper->one_time_message('success', __('Profile Settings Updated Successfully'));
        return redirect('profile');
    }
}
