<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Http\Helpers\Common;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\EmailTemplate;
use App\Models\Preference;
use App\Models\Setting;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Session;

class AdminController extends Controller
{
    protected $helper, $emailController;

    public function __construct()
    {
        $this->helper          = new Common();
        $this->emailController = new EmailController();
    }

    public function login()
    {
        return redirect()->route('admin');
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request['email'])->first();

        if (@$admin->status != 'Inactive')
        {
            if (Auth::guard('admin')->attempt(['email' => trim($request['email']), 'password' => trim($request['password'])]))
            {
                // $preferences = Preference::get();
                $preferences = Preference::where('field', '!=', 'dflt_lang')->get();
                if (!empty($preferences))
                {
                    foreach ($preferences as $pref)
                    {
                        $pref_arr[$pref->field] = $pref->value;
                    }
                }
                if (!empty($preferences))
                {
                    Session::put($pref_arr);
                }

                $default_currency = Setting::where('name', 'default_currency')->first();
                if (!empty($default_currency))
                {
                    Session::put('default_currency', $default_currency->value);
                }

                // default_language
                $default_language = Setting::where('name', 'default_language')->first();
                if (!empty($default_language))
                {
                    Session::put('default_language', $default_language->value);
                }

                // company_name
                $company_name = Setting::where('name', 'name')->first();
                if (!empty($company_name))
                {
                    Session::put('name', $company_name->value);
                }

                // company_logo
                $company_logo = Setting::where('name', 'logo')->first();
                if (!empty($company_logo))
                {
                    Session::put('company_logo', $company_logo->value);
                }

                $log                  = [];
                $log['user_id']       = Auth::guard('admin')->check() ? Auth::guard('admin')->user()->id : null;
                $log['type']          = 'Admin';
                $log['ip_address']    = $request->ip();
                $log['browser_agent'] = $request->header('user-agent');
                $log['created_at']    = \DB::raw('CURRENT_TIMESTAMP');
                ActivityLog::create($log);

                return redirect()->route('dashboard');
            }
            else
            {
                $this->helper->one_time_message('danger', 'Please Check Your Email/Password');
                return redirect()->route('admin');
            }
        }
        else
        {
            $this->helper->one_time_message('danger', 'You are Blocked.');
            return redirect()->route('admin');
        }
    }

    public function logout()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        // \Session::flush();
        Auth::guard('admin')->logout();
        return redirect()->route('admin');
    }

    /**
     * Show and manage Admin profile
     *
     * @return Admin profile page view
     */
    public function profile()
    {
        $data['menu']          = 'profile';
        $data['admin_id']      = $admin_id      = Auth::guard('admin')->user()->id;
        $data['admin_picture'] = $admin_picture = Auth::guard('admin')->user()->picture;

        return view('admin.profile.editProfile', $data);
    }

    /**
     * Update the specified Admin in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return Admin                    List page view
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());

        $this->validate($request, [
            'first_name' => 'required',
            'last_name'  => 'required',
            'picture'    => 'mimes:png,jpg,jpeg,gif,bmp|max:10000',
        ]);

        $data['first_name'] = $request->first_name;
        $data['last_name']  = $request->last_name;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $pic = $request->file('picture');
        if (isset($pic))
        {
            $upload = 'public/uploads/userPic';

            $pic1 = $request->pic;

            if ($pic1 != null)
            {
                $dir = public_path("uploads/userPic/$pic1");
                if (file_exists($dir))
                {
                    unlink($dir);
                }
            }
            $filename = $pic->getClientOriginalName();

            //extension checking
            $extension = strtolower($pic->getClientOriginalExtension());
            if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
            {
                $pic             = $pic->move($upload, $filename);
                $data['picture'] = $filename;
            }
            else
            {
                $this->helper->one_time_message('error', 'Invalid Image Format!');
            }
        }
        Admin::where(['id' => $id])->update($data);
        $this->helper->one_time_message('success', 'Profile Updated Successfully');
        return redirect('admin/profile');
    }

    /**
     * show admin change password operation
     *
     * @return change password page view
     */
    public function changePassword()
    {
        $data['menu']     = 'profile';
        $data['admin_id'] = $admin_id = Auth::guard('admin')->user()->id;
        return view('admin.profile.change_password', $data);
    }

    public function passwordCheck(Request $request)
    {
        $admin = Admin::where(['id' => $request->id])->first();

        if (!\Hash::check($request->old_pass, $admin->password))
        {
            $data['status'] = true;
            $data['fail']   = "Your old password is incorrect!";
        }
        else
        {
            $data['status'] = false;
        }
        return json_encode($data);
    }

    /**
     * Change admin password operation perform
     *
     * @return change password page view
     */

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
            'old_pass' => 'required',
            'new_pass' => 'required',
        ]);

        $admin = Admin::where(['id' => $request->id])->first(['password']);

        $data['password']   = \Hash::make($request->new_pass);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (\Hash::check($request->old_pass, $admin->password))
        {
            Admin::where(['id' => $request->id])->update($data);

            $this->helper->one_time_message('success', 'Password Updated successfully!');
            return redirect()->intended("admin/profile");
        }
        else
        {
            $this->helper->one_time_message('error', 'Old Password is Wrong!');
            return redirect()->intended("admin/change-password");
        }
    }

    public function forgetPassword(Request $request)
    {
        $methodName = $request->getMethod();
        // dd($methodName);
        if ($methodName == "GET")
        {
            return view('admin.auth.forgetPassword');
        }
        else
        {
            $email = $request->email;
            $admin = Admin::where('email', $email)->first(['id','first_name','last_name']);
            // dd($admin);
            if (!$admin)
            {
                $this->helper->one_time_message('error', 'Email Address doesn\'t match!');
                return back();
            }
            $data['email']      = $request->email;
            $data['token']      = $token      = base64_encode(encryptIt(rand(1000000, 9999999) . '_' . $request->email));
            $data['created_at'] = date('Y-m-d H:i:s');

            DB::table('password_resets')->insert($data);

            $adminFullName = $admin->first_name . ' ' . $admin->last_name;
            $this->sendPasswordResetEmail($request->email, $token, $adminFullName);

            $this->helper->one_time_message('success', 'Password reset link has been sent to your email address.');
            return back();
        }
    }

    public function sendPasswordResetEmail($toEmail, $token, $adminFullName)
    {
        //Mail for Password Reset - start
        $userPasswordResetTempInfo = EmailTemplate::where([
            'temp_id'     => 18,
            'language_id' => getDefaultLanguage(),
        ])->select('subject', 'body')->first();

        $englishUserPasswordResetTempInfo = EmailTemplate::where(['temp_id' => 18, 'lang' => 'en'])->select('subject', 'body')->first();

        if (!empty($userPasswordResetTempInfo->subject) && !empty($userPasswordResetTempInfo->body))
        {
            // subject
            $userPasswordResetTempInfo_sub = $userPasswordResetTempInfo->subject;
            // body
            $userPasswordResetTempInfo_msg = str_replace('{user}', $adminFullName, $userPasswordResetTempInfo->body);
        }
        else
        {
            // subject
            $userPasswordResetTempInfo_sub = $englishUserPasswordResetTempInfo->subject;
            // body
            $userPasswordResetTempInfo_msg = str_replace('{user}', $adminFullName, $englishUserPasswordResetTempInfo->body);
        }
        $userPasswordResetTempInfo_msg = str_replace('{email}', $toEmail, $userPasswordResetTempInfo_msg);
        $userPasswordResetTempInfo_msg = str_replace('{password_reset_url}', url('admin/password/resets', $token), $userPasswordResetTempInfo_msg);
        $userPasswordResetTempInfo_msg = str_replace('{soft_name}', getCompanyName(), $userPasswordResetTempInfo_msg);

        if (checkAppMailEnvironment())
        {
            $this->emailController->sendEmail($toEmail, $userPasswordResetTempInfo_sub, $userPasswordResetTempInfo_msg);
        }
        //Mail for Password Reset - end
    }

    public function verifyToken($token)
    {
        if (!$token)
        {
            $this->helper->one_time_message('error', 'Token not found!');
            return back();
        }
        $reset = DB::table('password_resets')->where('token', $token)->first();
        if ($reset)
        {
            $data['token'] = $token;
            return view('admin.auth.passwordForm', $data);
        }
        else
        {
            $this->helper->one_time_message('error', 'Token session has been destroyed. Please try to reset again.');
            return back();
        }

    }

    public function confirmNewPassword(Request $request)
    {
        $token    = $request->token;
        $password = $request->new_password;
        $confirm  = DB::table('password_resets')->where('token', $token)->first(['email']);

        $admin           = Admin::where('email', $confirm->email)->first();
        $admin->password = Hash::make($password);
        $admin->save();

        DB::table('password_resets')->where('token', $token)->delete();

        $this->helper->one_time_message('success', 'Password changed successfully.');
        return redirect()->to('/admin');
    }

}
