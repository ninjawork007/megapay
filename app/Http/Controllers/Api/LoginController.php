<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use App\Models\EmailTemplate;
use App\Models\Preference;
use App\Models\Setting;
use App\Models\User;
use App\Models\VerifyUser;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $unverifiedUser     = 201;
    protected $helper;
    public $email;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function checkLoginVia()
    {
        $loginVia = Setting::where('name', 'login_via')->first(['value'])->value;
        return response()->json([
            'status'   => $this->successStatus,
            'loginVia' => $loginVia,
        ]);
    }

    public function getPreferenceSettings()
    {
        $preference            = Preference::where(['category' => 'preference'])->whereIn('field', ['thousand_separator', 'decimal_format_amount', 'money_format'])->get(['field', 'value'])->toArray();
        $preference            = Common::key_value('field', 'value', $preference);
        $thousand_separator    = $preference['thousand_separator'];
        $decimal_format_amount = $preference['decimal_format_amount'];
        $money_format          = $preference['money_format'];
        return response()->json([
            'status'                => $this->successStatus,
            'thousand_separator'    => $thousand_separator,
            'decimal_format_amount' => $decimal_format_amount,
            'money_format'          => $money_format,
        ]);
    }

    public function login(Request $request)
    {
        //Login Vaia - starts
        $loginVia = Setting::where('name', 'login_via')->first(['value'])->value;
        if ((isset($loginVia) && $loginVia == 'phone_only'))
        {
            //phone only
            //to remove leading '0' (zero) - bangladeshi number
            $formattedRequest = ltrim($request->email, '0');
            $phnUser          = User::where(['phone' => $formattedRequest])->orWhere(['formattedPhone' => $formattedRequest])->first(['email']);
            if (!$phnUser)
            {
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = "Invalid email & credentials";
                return response()->json(['success' => $success], $this->unauthorisedStatus);
            }
            $request->email = $phnUser->email;
        }
        else if (isset($loginVia) && $loginVia == 'email_or_phone')
        {
            //phone or email
            if (strpos($request->email, '@') !== false)
            {
                $user = User::where(['email' => $request->email])->first(['email']);
                if (!$user)
                {
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = "Invalid email & credentials";
                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                }
                $request->email = $user->email;
            }
            else
            {
                $formattedRequest = ltrim($request->email, '0'); //to remove leading '0' (zero) - bangladeshi number
                $phoneOrEmailUser = User::where(['phone' => $formattedRequest])->orWhere(['formattedPhone' => $formattedRequest])->first(['email']);
                if (!$phoneOrEmailUser)
                {
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = "Invalid email & credentials";
                    return response()->json(['success' => $success], $this->unauthorisedStatus);
                }
                $request->email = $phoneOrEmailUser->email;
            }
        }
        else
        {
            //email only
            $user = User::where(['email' => $request->email])->first(['email']);
            if (!$user)
            {
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = "Invalid email & credentials";
                return response()->json(['success' => $success], $this->unauthorisedStatus);
            }
            $request->email = $user->email;
        }
        //Login Vaia - ends

        $checkUserVerificationStatus = $this->checkUserVerificationStatusApi($request->email);
        if ($checkUserVerificationStatus == true)
        {
            \DB::commit();
            $success['status']  = $this->unverifiedUser;
            $success['message'] = 'We sent you an activation code. Check your email and click on the link to verify.';
            return response()->json(['response' => $success], $this->unverifiedUser);
        }
        else
        {
            //Auth attempt - starts
            if (Auth::attempt(['email' => $request->email, 'password' => request('password')]))
            {
                $user             = Auth::user();
                $default_currency = Setting::where('name', 'default_currency')->first(['value']);
                $chkWallet        = Wallet::where(['user_id' => $user->id, 'currency_id' => $default_currency->value])->first();
                try
                {
                    \DB::beginTransaction();

                    if (empty($chkWallet))
                    {
                        $wallet              = new Wallet();
                        $wallet->user_id     = $user->id;
                        $wallet->currency_id = $default_currency->value;
                        $wallet->balance     = 0.00;
                        $wallet->is_default  = 'No';
                        $wallet->save();
                    }

                    $log                  = [];
                    $log['user_id']       = Auth::check() ? $user->id : null;
                    $log['type']          = 'User';
                    $log['ip_address']    = $request->ip();
                    $log['browser_agent'] = $request->header('user-agent');
                    $log['created_at']    = \DB::raw('CURRENT_TIMESTAMP');
                    ActivityLog::create($log);

                    //user_detail - adding last_login_at and last_login_ip
                    $user->user_detail()->update([
                        'last_login_at' => Carbon::now()->toDateTimeString(),
                        'last_login_ip' => $request->getClientIp(),
                    ]);
                    \DB::commit();

                    $success['user_id']        = $user->id;
                    $success['first_name']     = $user->first_name;
                    $success['last_name']      = $user->last_name;
                    $success['email']          = $user->email;
                    $success['formattedPhone'] = $user->formattedPhone;
                    $success['picture']        = $user->picture;

                    $fullName         = $user->first_name . ' ' . $user->last_name;
                    $success['token'] = $user->createToken($fullName)->accessToken;

                    //Get Money Format from Preferences Table
                    // $success['thousand_separator']    = Preference::where(['category' => 'preference', 'field' => 'thousand_separator'])->first(['value'])->value;
                    // $success['decimal_format_amount'] = Preference::where(['category' => 'preference', 'field' => 'decimal_format_amount'])->first(['value'])->value;
                    // $success['money_format']          = Preference::where(['category' => 'preference', 'field' => 'money_format'])->first(['value'])->value;

                    $success['status'] = $this->successStatus;
                    return response()->json(['response' => $success], $this->successStatus);
                }
                catch (Exception $e)
                {
                    \DB::rollBack();
                    $success['status']  = $this->unauthorisedStatus;
                    $success['message'] = $e->getMessage();
                    return response()->json(['response' => $success], $this->unauthorisedStatus);
                }
            }
            else
            {
                //d($request->all(),1);
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = "Invalid email & credentials";
                return response()->json(['response' => $success], $this->unauthorisedStatus);
            }
            //Auth attempt - ends
        }
    }

    //Check User Verification Status
    protected function checkUserVerificationStatusApi($userEmail)
    {
        $checkLoginDataOfUser = User::where(['email' => $userEmail])->first(['id', 'first_name', 'last_name', 'email']);
        if (checkVerificationMailStatus() == 'Enabled' && $checkLoginDataOfUser->user_detail->email_verification == 0)
        {
            $verifyUser = VerifyUser::where(['user_id' => $checkLoginDataOfUser->id])->first(['id']);
            try
            {
                \DB::beginTransaction();
                if (empty($verifyUser))
                {
                    $verifyUserNewRecord          = new VerifyUser();
                    $verifyUserNewRecord->user_id = $checkLoginDataOfUser->id;
                    $verifyUserNewRecord->token   = str_random(40);
                    $verifyUserNewRecord->save();
                }
                $englishUserVerificationEmailTempInfo = EmailTemplate::where(['temp_id' => 17, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                $userVerificationEmailTempInfo        = EmailTemplate::where([
                    'temp_id'     => 17,
                    'language_id' => getDefaultLanguage(),
                    'type'        => 'email',
                ])->select('subject', 'body')->first();

                if (!empty($userVerificationEmailTempInfo->subject) && !empty($userVerificationEmailTempInfo->body))
                {
                    $userVerificationEmailTempInfo_sub = $userVerificationEmailTempInfo->subject;
                    $userVerificationEmailTempInfo_msg = str_replace('{user}', $checkLoginDataOfUser->first_name . ' ' . $checkLoginDataOfUser->last_name, $userVerificationEmailTempInfo->body);
                }
                else
                {
                    $userVerificationEmailTempInfo_sub = $englishUserVerificationEmailTempInfo->subject;
                    $userVerificationEmailTempInfo_msg = str_replace('{user}', $checkLoginDataOfUser->first_name . ' ' . $checkLoginDataOfUser->last_name, $englishUserVerificationEmailTempInfo->body);
                }
                $userVerificationEmailTempInfo_msg = str_replace('{email}', $checkLoginDataOfUser->email, $userVerificationEmailTempInfo_msg);
                $userVerificationEmailTempInfo_msg = str_replace('{verification_url}', url('user/verify', $checkLoginDataOfUser->verifyUser->token), $userVerificationEmailTempInfo_msg);
                $userVerificationEmailTempInfo_msg = str_replace('{soft_name}', getCompanyName(), $userVerificationEmailTempInfo_msg);

                if (checkAppMailEnvironment())
                {
                    try
                    {
                        $this->email->sendEmail($checkLoginDataOfUser->email, $userVerificationEmailTempInfo_sub, $userVerificationEmailTempInfo_msg);
                        return true;
                    }
                    catch (Exception $e)
                    {
                        \DB::rollBack();
                        $success['status']  = $this->unauthorisedStatus;
                        $success['message'] = $e->getMessage();
                        return response()->json(['success' => $success], $this->unauthorisedStatus);
                    }
                }
            }
            catch (Exception $e)
            {
                \DB::rollBack();
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = $e->getMessage();
                return response()->json(['response' => $success], $this->unauthorisedStatus);
            }
        }
    }

    public function logout()
    {
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('view:clear');
        \Auth::logout();
        $response['status']  = $this->successStatus;
        $response['message'] = "You have successfully logged out!";
        return response()->json(['response' => $response], $this->successStatus);
    }
}
