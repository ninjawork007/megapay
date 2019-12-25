<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\EmailConfig;
use App\Models\FeesLimit;
use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\Preference;
use App\Models\Setting;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Session;
use Validator;

class SettingController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function general(Request $request)
    {

        if (!$_POST)
        {
            $data['menu']   = 'settings';
            $general        = Setting::where('type', 'general')->get()->toArray();
            $data['result'] = $result = $this->helper->key_value('name', 'value', $general);

            $nexmo         = Setting::where('type', 'Nexmo')->get()->toArray();
            $data['nexmo'] = $nexmo = $this->helper->key_value('name', 'value', $nexmo);
            // dd(($nexmo));

            $data['language'] = $language = $this->helper->key_value('id', 'name', Language::where(['status' => 'Active'])->get()->toArray());

            $data['currency'] = $currency = $this->helper->key_value('id', 'name', Currency::where(['status' => 'Active'])->get()->toArray());

            return view('admin.settings.general', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());

            $rules = array(
                'name' => 'required',
                // 'head_code' => 'required',
            );

            $fieldNames = array(
                'name' => 'Name',
                // 'head_code' => 'Head Code',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {

                //Setting
                Setting::where(['name' => 'name'])->update(['value' => $request->name]);
                foreach ($_FILES["photos"]["error"] as $key => $error)
                {
                    $tmp_name = $_FILES["photos"]["tmp_name"][$key];

                    $name = str_replace(' ', '_', $_FILES["photos"]["name"][$key]);

                    $ext = pathinfo($name, PATHINFO_EXTENSION);

                    $name = time() . '_' . $key . '.' . $ext;

                    if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp' || $ext == 'ico')
                    {
                        if (move_uploaded_file($tmp_name, "public/images/logos/" . $name))
                        {
                            Setting::where(['name' => $key])->update(['value' => $name]);
                        }
                    }
                }
                Setting::where(['name' => 'head_code'])->update(['value' => is_null($request->head_code) ? '' : trim($request->head_code)]);
                Setting::where(['name' => 'default_currency'])->update(['value' => $request->default_currency]);
                Setting::where(['name' => 'default_language'])->update(['value' => $request->default_language]);

                //recaptcha
                Setting::where(['name' => 'has_captcha'])->update(['value' => $request->has_captcha]);

                //login_via
                Setting::where(['name' => 'login_via'])->update(['value' => $request->login_via]);

                //Currency
                Currency::where('default', '=', '1')->update(['default' => '0']);
                Currency::where('id', $request->default_currency)->update(['default' => '1']);
                //

                //updation or creation of fees limit entries on default currency change
                $paymentMethodArray = PaymentMethod::where(['status' => 'Active'])->pluck('id')->toArray();
                $transaction_types  = [Deposit, Withdrawal, Transferred, Exchange_From, Request_To];
                foreach ($transaction_types as $transaction_type)
                {
                    $feeslimit = FeesLimit::where(['has_transaction' => 'No', 'currency_id' => $request->default_currency])->get();
                    if ($feeslimit->count() > 0)
                    {
                        //update existing has transaciton - no to yes
                        foreach ($feeslimit as $fLimit)
                        {
                            $feesLimit                  = FeesLimit::find($fLimit->id);
                            $feesLimit->has_transaction = 'Yes';
                            $feesLimit->save();
                        }
                    }
                    else
                    {
                        if ($transaction_type == 1 || $transaction_type == 2)
                        {
                            foreach ($paymentMethodArray as $key => $value)
                            {
                                //insert new records of feeslimit on change of default currency with pm id
                                $feesLimit                      = new FeesLimit();
                                $feesLimit->currency_id         = $request->default_currency;
                                $feesLimit->transaction_type_id = $transaction_type;
                                $feesLimit->payment_method_id   = $value;
                                $feesLimit->has_transaction     = 'Yes';
                                $feesLimit->save();
                            }
                        }
                        else
                        {
                            //insert new records of feeslimit on change of default currency with no payment method
                            $feesLimit                      = new FeesLimit();
                            $feesLimit->currency_id         = $request->default_currency;
                            $feesLimit->transaction_type_id = $transaction_type;
                            $feesLimit->has_transaction     = 'Yes';
                            $feesLimit->save();
                        }
                    }
                }
                //

                //Language
                Language::where('default', '=', '1')->update(['default' => '0']);
                Language::where('id', $request->default_language)->update(['default' => '1']);

                $lang = Language::find($request->default_language, ['id', 'short_name']);
                Preference::where(['field' => 'dflt_lang', 'category' => 'company'])->update(['value' => $lang->short_name]);

                $this->helper->one_time_message('success', 'General Settings Updated Successfully');
                return redirect('admin/settings');
            }
        }
    }

    public function checkSmsGatewaySettings(Request $request)
    {
        $nexmo         = Setting::where('type', 'Nexmo')->get()->toArray();
        $data['nexmo'] = $nexmo = $this->helper->key_value('name', 'value', $nexmo);

        if (empty($nexmo['default_nexmo_phone_number']) || empty($nexmo['Key']) || empty($nexmo['Secret']))
        {
            return response()->json([
                'status'  => false,
                'message' => 'Sms gateway not configured correctly!',
            ]);
        }
        elseif ($nexmo['is_nexmo_default'] !== 'Yes' || $nexmo['nexmo_status'] !== 'Active')
        {
            return response()->json([
                'status'  => false,
                'message' => 'Sms gateway is either not default or active',
            ]);
        }
        else
        {
            return response()->json([
                'status'  => true,
                'message' => 'Sms gateway configured correctly!',
            ]);
        }
    }

    //deleteSettingLogo
    public function deleteSettingLogo(Request $request)
    {
        $logo = $_POST['logo'];

        if (isset($logo))
        {
            $setting = Setting::where(['name' => 'logo', 'type' => 'general', 'value' => $request->logo])->first();

            if ($setting)
            {
                Setting::where(['name' => 'logo', 'type' => 'general', 'value' => $request->logo])->update(['value' => null]);

                if ($logo != null)
                {
                    $dir = public_path('images/logos/' . $logo);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = 'Logo has been successfully deleted!';
            }
            else
            {
                $data['success'] = 0;
                $data['message'] = "No Record Found!";
            }
        }
        echo json_encode($data);
        exit();
    }

    //deleteSettingFavicon
    public function deleteSettingFavicon(Request $request)
    {
        $favicon = $_POST['favicon'];

        if (isset($favicon))
        {
            $setting = Setting::where(['name' => 'favicon', 'type' => 'general', 'value' => $request->favicon])->first();

            if ($setting)
            {
                Setting::where(['name' => 'favicon', 'type' => 'general', 'value' => $request->favicon])->update(['value' => null]);

                if ($favicon != null)
                {
                    $dir = public_path('images/logos/' . $favicon);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = 'Favicon has been successfully deleted!';
            }
            else
            {
                $data['success'] = 0;
                $data['message'] = "No Record Found!";
            }
        }
        echo json_encode($data);
        exit();
    }

    //email settings
    public function email(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']   = 'email';
            $general        = EmailConfig::find("1")->toArray();
            $data['result'] = $general;
            //dd($general);

            return view('admin.settings.email', $data);
        }
        else if ($_POST)
        {
            $email_config = EmailConfig::find('1');
            if ($email_config)
            {
                $email_config->email_protocol   = $request->driver;
                $email_config->email_encryption = $request->encryption;
                $email_config->smtp_host        = $request->host;
                $email_config->smtp_port        = $request->port;
                $email_config->smtp_email       = $request->from_address;
                $email_config->smtp_username    = $request->username;
                $email_config->smtp_password    = $request->password;
                $email_config->from_address     = $request->from_address;
                $email_config->from_name        = $request->from_name;
                $email_config->save();
            }
            else
            {
                $configIns                   = new EmailConfig();
                $configIns->email_protocol   = $request->driver;
                $configIns->email_encryption = $request->encryption;
                $configIns->smtp_host        = $request->host;
                $configIns->smtp_port        = $request->port;
                $configIns->smtp_email       = $request->from_address;
                $configIns->smtp_username    = $request->username;
                $configIns->smtp_password    = $request->password;
                $configIns->from_address     = $request->from_address;
                $configIns->from_name        = $request->from_name;
                $configIns->save();
            }

            if ($request->driver == "smtp")
            {
                $rules = array(
                    'driver'       => 'required',
                    'host'         => 'required',
                    'port'         => 'required',
                    'from_address' => 'required',
                    'from_name'    => 'required',
                    'encryption'   => 'required',
                    'username'     => 'required',
                    'password'     => 'required',
                );

                $fieldNames = array(
                    'driver'       => 'Driver',
                    'host'         => 'Host',
                    'port'         => 'Port',
                    'from_address' => 'From Address',
                    'from_name'    => 'From Name',
                    'encryption'   => 'Encryption',
                    'username'     => 'Username',
                    'password'     => 'Password',
                );

                $validator = Validator::make($request->all(), $rules);
                $validator->setAttributeNames($fieldNames);

                if ($validator->fails())
                {
                    return back()->withErrors($validator)->withInput();
                }
                else
                {
                    Setting::where(['name' => 'driver'])->update(['value' => $request->driver]);
                    Setting::where(['name' => 'host'])->update(['value' => $request->host]);
                    Setting::where(['name' => 'port'])->update(['value' => $request->port]);
                    Setting::where(['name' => 'from_address'])->update(['value' => $request->from_address]);
                    Setting::where(['name' => 'from_name'])->update(['value' => $request->from_name]);
                    Setting::where(['name' => 'encryption'])->update(['value' => $request->encryption]);
                    Setting::where(['name' => 'username'])->update(['value' => $request->username]);
                    Setting::where(['name' => 'password'])->update(['value' => $request->password]);

                    $data = $request->all();
                    Config::set([
                        'mail.driver'     => isset($data['driver']) ? $data['driver'] : '',

                        'mail.host'       => isset($data['host']) ? $data['host'] : '',

                        'mail.port'       => isset($data['port']) ? $data['port'] : '',

                        'mail.from'       => ['address' => isset($data['from_address']) ? $data['from_address'] : '',

                            'name'                          => isset($data['from_name']) ? $data['from_name'] : ''],

                        'mail.encryption' => isset($data['encryption']) ? $data['encryption'] : '',

                        'mail.username'   => isset($data['username']) ? $data['username'] : '',

                        'mail.password'   => isset($data['password']) ? $data['password'] : '',
                    ]);

                    $fromInfo = \Config::get('mail.from');

                    $user = [];
                    // $user['to']       = 'tuhin.techvill@gmail.com';
                    $user['to']       = 'parvez.techvill@gmail.com';
                    $user['from']     = $fromInfo['address'];
                    $user['fromName'] = $fromInfo['name'];
                    try
                    {
                        $ok = Mail::send('emails.verify', ['user' => $user], function ($m) use ($user)
                        {
                            $m->from($user['from'], $user['fromName']);
                            $m->to($user['to']);
                            $m->subject('verify smtp settings');
                        });
                        $emailConfig         = EmailConfig::find("1");
                        $emailConfig->status = 1;
                        $emailConfig->save();
                        $this->helper->one_time_message('success', 'SMTP settings are verified successfully!');
                    }
                    catch (\Exception $e)
                    {
                        dd($e);
                        $emailConfig         = EmailConfig::find("1");
                        $emailConfig->status = 0;
                        $emailConfig->save();
                        $this->helper->one_time_message('error', 'Email Settings Updated fail');
                        return redirect('admin/settings/email');
                    }

                    $this->helper->one_time_message('success', 'Email Settings Updated Successfully');
                    return redirect('admin/settings/email');
                }
            }
            else
            {
                Setting::where(['name' => 'driver'])->update(['value' => $request->driver]);

                $this->helper->one_time_message('success', 'Email Settings Updated Successfully');
                return redirect('admin/settings/email');
            }
        }
    }

    //sms settings
    public function sms(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'sms';
            $general      = Setting::where(['type' => 'Nexmo'])->get()->toArray();

            $data['result'] = $result = $this->helper->key_value('name', 'value', $general);
            // dd($result);

            return view('admin.settings.sms', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());

            $setting = Setting::where(['type' => 'Nexmo'])->first(['type']);
            // dd($setting);

            if (isset($setting) && $setting->type == "Nexmo")
            {
                $rules = array(
                    'nexmo_key'    => 'required',
                    'nexmo_secret' => 'required',
                );

                $fieldNames = array(
                    'nexmo_key'    => 'Nexmo Key',
                    'nexmo_secret' => 'Nexmo Secret',
                );

                $validator = Validator::make($request->all(), $rules);
                $validator->setAttributeNames($fieldNames);

                if ($validator->fails())
                {
                    return back()->withErrors($validator)->withInput();
                }
                else
                {
                    Setting::where(['name' => 'Key', 'type' => 'Nexmo'])->update(['value' => $request->nexmo_key]);
                    Setting::where(['name' => 'Secret', 'type' => 'Nexmo'])->update(['value' => $request->nexmo_secret]);

                    Setting::where(['name' => 'is_nexmo_default', 'type' => 'Nexmo'])->update(['value' => $request->is_nexmo_default]);

                    Setting::where(['name' => 'nexmo_status', 'type' => 'Nexmo'])->update(['value' => $request->nexmo_status]);

                    Setting::where(['name' => 'default_nexmo_phone_number', 'type' => 'Nexmo'])->update(['value' => $request->default_nexmo_phone_number]);

                    $data = $request->all();
                    // dd($data);
                    Config::set([
                        'services.nexmo.key'    => isset($data['nexmo_key']) ? $data['nexmo_key'] : '',
                        'services.nexmo.secret' => isset($data['nexmo_secret']) ? $data['nexmo_secret'] : '',
                    ]);

                    // changeEnvironmentVariable('NEXMO_KEY', isset($data['nexmo_key']) ? $data['nexmo_key'] : '');
                    // changeEnvironmentVariable('NEXMO_SECRET', isset($data['nexmo_secret']) ? $data['nexmo_secret'] : '');

                    $this->helper->one_time_message('success', 'SMS Settings Updated Successfully');
                    return redirect('admin/settings/sms');
                }
            }
            else
            {
                dd('N/A');
                $this->helper->one_time_message('success', 'SMS Settings Updated Successfully');
                return redirect('admin/settings/sms');
            }
        }
    }

    // social_links
    public function social_links(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'social_links';
            $general      = DB::table('socials')->get();

            $data['result'] = $general;
            return view('admin.settings.social', $data);
        }
        else if ($_POST)
        {
            // $rules = array(
            //     'facebook'    => 'required',
            //     'google_plus' => 'required',
            //     'twitter'     => 'required',
            //     'linkedin'    => 'required',
            //     'pinterest'   => 'required',
            //     'youtube'     => 'required',
            //     'instagram'   => 'required',
            // );

            // $fieldNames = array(
            //     'facebook'    => 'Facebook',
            //     'google_plus' => 'Google Plus',
            //     'twitter'     => 'Twitter',
            //     'linkedin'    => 'Linkedin',
            //     'pinterest'   => 'Pinterest',
            //     'youtube'     => 'Youtube',
            //     'instagram'   => 'Instagram',

            // );
            // $validator = Validator::make($request->all(), $rules);
            // $validator->setAttributeNames($fieldNames);

            // if ($validator->fails())
            // {
            //     return back()->withErrors($validator)->withInput();
            // }
            // else
            // {
            //     $links = $request->all();
            //     unset($links['_token']);

            //     foreach ($links as $key => $link)
            //     {
            //         $social = DB::table('socials')->where('name', $key)->first();
            //         if (!$social)
            //         {
            //             $key2 = str_replace('_', ' ', $key);

            //             $data['name'] = $key;
            //             $data['icon'] = "<i class=\"ti-$key2\" aria-hidden=\"true\"></i>";
            //             $data['url']  = $link;
            //             DB::table('socials')->insert($data);
            //         }
            //         else
            //         {
            //             DB::table('socials')->where('name', $key)->update(['url' => $link]);
            //         }
            //     }

            //     $this->helper->one_time_message('success', 'Social Links Settings Updated Successfully');
            //     return redirect('admin/settings/social_links');
            // }

            $links = $request->all();
            unset($links['_token']);

            foreach ($links as $key => $link)
            {
                $social = DB::table('socials')->where('name', $key)->first();
                if (!$social)
                {
                    $key2 = str_replace('_', ' ', $key);

                    $data['name'] = $key;
                    $data['icon'] = "<i class=\"ti-$key2\" aria-hidden=\"true\"></i>";
                    $data['url']  = $link;
                    DB::table('socials')->insert($data);
                }
                else
                {
                    DB::table('socials')->where('name', $key)->update(['url' => $link]);
                }
            }

            $this->helper->one_time_message('success', 'Social Links Settings Updated Successfully');
            return redirect('admin/settings/social_links');
        }
    }

    // api_informations
    public function api_informations(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'api_informations';

            $data['recaptcha'] = $recaptcha = Setting::where('type', 'recaptcha')->pluck('value', 'name')->toArray();
            // dd($recaptcha);
            return view('admin.settings.api_credentials', $data);
        }
        else if ($_POST)
        {
            $rules = array(
                'captcha_secret_key' => 'required',
                'captcha_site_key'   => 'required',
            );

            $fieldNames = array(
                'captcha_secret_key' => 'Captcha Secret Key',
                'captcha_site_key'   => 'Captcha Site Key',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                Setting::where(['name' => 'secret_key', 'type' => 'recaptcha'])->update(['value' => $request->captcha_secret_key]);
                Setting::where(['name' => 'site_key', 'type' => 'recaptcha'])->update(['value' => $request->captcha_site_key]);

                $data = $request->all();
                // dd($data);
                Config::set([
                    'captcha.secret'  => isset($data['captcha_secret_key']) ? $data['captcha_secret_key'] : '',
                    'captcha.sitekey' => isset($data['captcha_site_key']) ? $data['captcha_site_key'] : '',
                ]);

                // changeEnvironmentVariable('CAPTCHA_SECRET', isset($data['captcha_secret_key']) ? $data['captcha_secret_key'] : '');
                // changeEnvironmentVariable('CAPTCHA_SITEKEY', isset($data['captcha_site_key']) ? $data['captcha_site_key'] : '');

                $this->helper->one_time_message('success', 'Api informations Settings Updated Successfully');
                return redirect('admin/settings/api_informations');
            }
        }
        else
        {
            return redirect('admin/settings/api_informations');
        }
    }

    // payment_methods
    public function payment_methods(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'payment_methods';

            $data['paypal']      = $paypal      = Setting::where('type', 'PayPal')->pluck('value', 'name', 'id')->toArray();
            $data['stripe']      = $stripe      = Setting::where('type', 'Stripe')->pluck('value', 'name', 'id')->toArray();
            $data['twoCheckout'] = $twoCheckout = Setting::where('type', '2Checkout')->pluck('value', 'name', 'id')->toArray();

            $data['payUmoney'] = $payUmoney = Setting::where('type', 'PayUmoney')->pluck('value', 'name', 'id')->toArray();

            $data['coinPayments'] = $coinPayments = Setting::where('type', 'Coinpayments')->pluck('value', 'name', 'id')->toArray();

            return view('admin.settings.payment', $data);
        }
        else if ($_POST['gateway'] == 'paypal')
        {

            $rules = array(
                'client_id'     => 'required',
                'client_secret' => 'required',
            );

            $fieldNames = array(
                'client_id'     => 'PayPal Client ID',
                'client_secret' => 'PayPal Client Secret',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                // $data['success'] = 0;
                // $data['errors']  = $validator->messages();
                return back()->withErrors($validator)->withInput();
                // echo json_encode($data);
            }
            else
            {
                // dd($request->all());
                Setting::where(['name' => 'client_id', 'type' => 'PayPal'])->update(['value' => $request->client_id]);

                Setting::where(['name' => 'client_secret', 'type' => 'PayPal'])->update(['value' => $request->client_secret]);

                Setting::where(['name' => 'mode', 'type' => 'PayPal'])->update(['value' => $request->mode]);

                $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully');
                return redirect('admin/settings/payment_methods');
            }
        }
        else if ($_POST['gateway'] == 'stripe')
        {
            // dd('ss');
            $rules = array(
                'secret_key'      => 'required',
                'publishable_key' => 'required',
            );

            $fieldNames = array(
                'secret_key'      => 'Secret Key',
                'publishable_key' => 'Publishable Key',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
                // $data['success'] = 0;
                // $data['errors']  = $validator->messages();
                // echo json_encode($data);
            }
            else
            {
                Setting::where(['name' => 'secret', 'type' => 'Stripe'])->update(['value' => $request->secret_key]);
                Setting::where(['name' => 'publishable', 'type' => 'Stripe'])->update(['value' => $request->publishable_key]);
                $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully');
                return redirect('admin/settings/payment_methods');
                // $data['message'] = 'Updated Successfully';
                // $data['success'] = 1;
                // echo json_encode($data);
            }
        }
        else if ($_POST['gateway'] == 'twoCheckout')
        {
            $rules = array(
                'seller_id' => 'required',
            );

            $fieldNames = array(
                'seller_id' => 'Seller Id',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                Setting::where(['name' => 'seller_id', 'type' => '2Checkout'])->update(['value' => $request->seller_id]);
                Setting::where(['name' => 'mode', 'type' => '2Checkout'])->update(['value' => $request->mode]);

                $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully');
                return redirect('admin/settings/payment_methods');
            }
        }
        else if ($_POST['gateway'] == 'payUMoney')
        {
            $rules = array(
                'key'  => 'required',
                'salt' => 'required',
            );

            $fieldNames = array(
                'key'  => 'Key',
                'salt' => 'Salt',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                Setting::where(['name' => 'key', 'type' => 'PayUmoney'])->update(['value' => $request->key]);
                Setting::where(['name' => 'salt', 'type' => 'PayUmoney'])->update(['value' => $request->salt]);
                Setting::where(['name' => 'mode', 'type' => 'PayUmoney'])->update(['value' => $request->mode]);
                $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully');
                return redirect('admin/settings/payment_methods');
            }
        }
        else if ($_POST['gateway'] == 'coinPayments')
        {
            $rules = array(
                'merchant_id' => 'required',
                'private_key' => 'required',
                'public_key'  => 'required',
            );

            $fieldNames = array(
                'merchant_id' => 'Merchant Key',
                'private_key' => 'Private Key',
                'public_key'  => 'Public Key',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                // changeEnvironmentVariable('COIN_PAYMENT_MARCHANT_ID', $request->merchant_id);
                // changeEnvironmentVariable('COIN_PAYMENT_PRIVATE_KEY', $request->private_key);
                // changeEnvironmentVariable('COIN_PAYMENT_PUBLIC_KEY', $request->public_key);

                Setting::where(['name' => 'merchant_id', 'type' => 'Coinpayments'])->update(['value' => $request->merchant_id]);
                Setting::where(['name' => 'private_key', 'type' => 'Coinpayments'])->update(['value' => $request->private_key]);
                Setting::where(['name' => 'public_key', 'type' => 'Coinpayments'])->update(['value' => $request->public_key]);

                $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully');
                return redirect('admin/settings/payment_methods');
            }
        }
    }

    // preference - form
    public function preference()
    {
        $data['menu'] = 'preference';

        // $data['timezones'] = TimeZone::all();
        $data['timezones'] = $timezones = phpDefaultTimeZones();

        $pref     = Preference::where('category', 'preference')->get();
        $data_arr = [];
        foreach ($pref as $row)
        {
            $data_arr[$row->category][$row->field] = $row->value;
        }
        $data['prefData'] = $data_arr;

        return view('admin.settings.preference', $data);
    }

    // preference - save
    public function savePreference(Request $request)
    {
        $post = $request->all();
        // dd($post);

        unset($post['_token']);

        if ($post['date_format'] == 0)
        {
            $post['date_format_type'] = 'yyyy' . $post['date_sepa'] . 'mm' . $post['date_sepa'] . 'dd';
        }
        elseif ($post['date_format'] == 1)
        {
            $post['date_format_type'] = 'dd' . $post['date_sepa'] . 'mm' . $post['date_sepa'] . 'yyyy';
        }
        elseif ($post['date_format'] == 2)
        {
            $post['date_format_type'] = 'mm' . $post['date_sepa'] . 'dd' . $post['date_sepa'] . 'yyyy';
        }
        elseif ($post['date_format'] == 3)
        {
            $post['date_format_type'] = 'dd' . $post['date_sepa'] . 'M' . $post['date_sepa'] . 'yyyy';
        }
        elseif ($post['date_format'] == 4)
        {
            $post['date_format_type'] = 'yyyy' . $post['date_sepa'] . 'M' . $post['date_sepa'] . 'dd';
        }

        $i = 0;
        foreach ($post as $key => $value)
        {
            $data[$i]['category'] = "preference";
            $data[$i]['field']    = $key;
            $data[$i]['value']    = $value;
            $i++;
        }
        foreach ($data as $key => $value)
        {
            $category = $value['category'];
            $field    = $value['field'];
            $val      = $value['value'];
            $res      = Preference::where(['field' => $field])->first();
            // dd($res);
            // if (count($res) == 0)k
            if (empty($res))
            {
                DB::insert(DB::raw("INSERT INTO preferences(category,field,value) VALUES ('$category','$field','$val')"));
            }
            else
            {
                Preference::where(['category' => 'preference', 'field' => $field])->update(array('field' => $field, 'value' => $val));
            }
        }

        $pref = Preference::where('category', 'preference')->get();
        if (!empty($pref))
        {
            foreach ($pref as $value)
            {
                $prefer[$value->field] = $value->value;
            }
            Session::put($prefer);
        }
        // dd($prefer);
        $this->helper->one_time_message('success', 'Preferences Updated Successfully');
        return redirect('admin/settings/preference');
    }

    // Enable woocommerce - form
    public function enableWoocommerce(Request $request)
    {
        $wooCommerce = Setting::where(['type' => 'envato'])->get(['value', 'name'])->toArray();
        $wooCommerce = $this->helper->key_value('name', 'value', $wooCommerce);
        // dd($wooCommerce);

        if ($request->method() != 'POST')
        {
            $data['menu']              = 'enablewoocommerce';
            $data['code_status']       = isset($wooCommerce['code_status']) ? $wooCommerce['code_status'] : '';
            $data['publicationStatus'] = isset($wooCommerce['publication_status']) ? $wooCommerce['publication_status'] : '';
            $data['plugin_name']       = isset($wooCommerce['plugin_name']) ? $wooCommerce['plugin_name'] : '';
            return view('admin.settings.enablewoocommerce', $data);
        }
        else
        {
            if ($request->key == 'purchasecodeverification')
            {
                $this->validate($request, [
                    'envatopurchasecode' => 'nullable|required',
                ], [
                    'envatopurchasecode.required' => 'The Purchase code field is required.',
                ]);

                $domainName     = request()->getHost();
                $domainIp       = request()->ip();
                $purchaseStatus = $this->getPurchaseStatus($domainName, $domainIp, $request->envatopurchasecode);
                $match          = ['type' => 'envato', 'name' => 'purchasecodeverificationstatus'];
                if ($purchaseStatus == 1)
                {
                    try
                    {
                        \DB::beginTransaction();

                        //Insert data for purchase code verification status to settings table
                        $Settings        = Setting::firstOrNew($match);
                        $Settings->name  = 'purchasecodeverificationstatus';
                        $Settings->value = 1;
                        $Settings->type  = 'envato';
                        $Settings->save();

                        //Insert data for code status to settings table
                        $matchs         = ['type' => 'envato', 'name' => 'code_status'];
                        $Setting        = Setting::firstOrNew($matchs);
                        $Setting->name  = 'code_status';
                        $Setting->value = 1;
                        $Setting->type  = 'envato';
                        $Setting->save();
                        \DB::commit();

                        $this->helper->one_time_message('success', 'Your purchase code is verified.You can upload plugin zip file now.');
                        return redirect('admin/settings/enable-woocommerce');
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        $this->helper->one_time_message('error', $e->getMessage());
                        return redirect('admin/settings/enable-woocommerce');
                    }
                }
                else
                {
                    //Insert data for purchase code verification status to settings table
                    $Settings        = Setting::firstOrNew($match);
                    $Settings->name  = 'purchasecodeverificationstatus';
                    $Settings->value = 0;
                    $Settings->type  = 'envato';
                    $Settings->save();
                    return back()->withErrors(['envatopurchasecode' => 'Invalid purchase code'])->withInput();
                }
            }
            else
            {
                // dd($request->all());
                // dd($request->publication_status);

                if ((empty($request->plugin) || !empty($request->plugin)) && $request->publication_status != "Active")
                {
                    $this->validate($request, [
                        'publication_status' => 'required',
                    ]);
                    $statusUpdateSetting        = Setting::firstOrNew(['name' => 'publication_status', 'type' => 'envato']);
                    $statusUpdateSetting->name  = 'publication_status';
                    $statusUpdateSetting->value = $request->publication_status;
                    $statusUpdateSetting->type  = 'envato';
                    $statusUpdateSetting->save();
                    $this->helper->one_time_message('success', 'Plugin Uploaded Successfully');
                    return redirect('admin/settings/enable-woocommerce');
                }
                else
                {
                    $this->validate($request, [
                        'plugin'             => 'mimes:zip|max:2048',
                        'publication_status' => 'required',
                    ], [
                        'publication_status.required' => 'The Publication Status field is required.',
                        'plugin.required'             => 'The plugin field is required.',
                        'plugin.mimes'                => 'The plugin must be a zip file.',
                        'plugin.max'                  => 'The plugin file size must be less than 2 MB.',
                    ]);

                    try
                    {
                        \DB::beginTransaction();

                        $statusUpdateSetting        = Setting::firstOrNew(['name' => 'publication_status', 'type' => 'envato']);
                        $statusUpdateSetting->name  = 'publication_status';
                        $statusUpdateSetting->value = $request->publication_status;
                        $statusUpdateSetting->type  = 'envato';
                        $statusUpdateSetting->save();

                        if ($_FILES["plugin"]["error"] == 0)
                        {
                            $tmp_name = $_FILES["plugin"]["tmp_name"];
                            $name     = str_replace(' ', '_', $_FILES["plugin"]["name"]);
                            //
                            $location = public_path('uploads/woocommerce/' . $name);
                            if (file_exists($location))
                            {
                                unlink($location);
                            }
                            //
                            $ext = pathinfo($name, PATHINFO_EXTENSION);
                            if ($ext == 'zip')
                            {
                                if (move_uploaded_file($tmp_name, $location))
                                {
                                    $fileSetting        = Setting::firstOrNew(['name' => 'plugin_name', 'type' => 'envato']);
                                    $fileSetting->name  = 'plugin_name';
                                    $fileSetting->value = $name;
                                    $fileSetting->type  = 'envato';
                                    $fileSetting->save();
                                    \DB::commit();
                                    $this->helper->one_time_message('success', 'Plugin Uploaded Successfully');
                                    return redirect('admin/settings/enable-woocommerce');
                                }
                                else
                                {
                                    \DB::rollBack();
                                    return back()->withErrors(['plugin' => 'Error in plugin upload'])->withInput();
                                }
                            }
                        }
                        else
                        {
                            $fileSetting        = Setting::firstOrNew(['name' => 'plugin_name', 'type' => 'envato']);
                            $fileSetting->name  = 'plugin_name';
                            $fileSetting->value = $request->pluginUploaded;
                            $fileSetting->type  = 'envato';
                            $fileSetting->save();
                            \DB::commit();
                            $this->helper->one_time_message('success', 'Plugin Uploaded Successfully');
                            return redirect('admin/settings/enable-woocommerce');
                        }
                    }
                    catch (\Exception $e)
                    {
                        \DB::rollBack();
                        $this->helper->one_time_message('error', $e->getMessage());
                        return redirect('admin/settings/enable-woocommerce');
                    }
                }
            }
        }
    }

    public function getPurchaseStatus($domainName, $domainIp, $envatopurchasecode)
    {
        $data = array(
            'domain_name'        => $domainName,
            'domain_ip'          => $domainIp,
            'envatopurchasecode' => $envatopurchasecode,
        );
        $url  = "http://envatoapi.techvill.net/";
        // $url = "http://aminul-pc/checkenvatoapi";
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        $output = curl_exec($ch);
        if ($output == 1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * FOR CACHE - BELOW
     */

    // public function savePreference(Request $request)
    // {
    //     $post = $request->all();
    //     unset($post['_token']);

    //     if($post['date_format'] == 0) {
    //         $post['date_format_type'] = 'yyyy'.$post['date_sepa'].'mm'.$post['date_sepa'].'dd';
    //     } elseif ($post['date_format'] == 1) {
    //         $post['date_format_type'] = 'dd'.$post['date_sepa'].'mm'.$post['date_sepa'].'yyyy';
    //     } elseif ($post['date_format'] == 2) {
    //         $post['date_format_type'] = 'mm'.$post['date_sepa'].'dd'.$post['date_sepa'].'yyyy';
    //     } elseif ($post['date_format'] == 3) {
    //         $post['date_format_type'] = 'dd'.$post['date_sepa'].'M'.$post['date_sepa'].'yyyy';
    //     } elseif ($post['date_format'] == 4) {
    //         $post['date_format_type'] = 'yyyy'.$post['date_sepa'].'M'.$post['date_sepa'].'dd';
    //     }

    //     $i=0;
    //     foreach ($post as $key => $value) {
    //         $data[$i]['category'] = "preference";
    //         $data[$i]['field'] = $key;
    //         $data[$i]['value'] = $value;
    //         $i++;
    //     }
    //     foreach($data as $key => $value) {
    //         $category = $value['category'];
    //         $field    = $value['field'];
    //         $val      = $value['value'];
    //         $res      = Preference::getAll()->where('field', $field)->count();
    //         if($res == 0) {
    //             $newPreference= new Preference();
    //             $newPreference->category = $category;
    //             $newPreference->field    = $field;
    //             $newPreference->value    = $val;
    //             $newPreference->save();
    //         } else {
    //             $preferenceToUpdate = Preference::where('category', 'preference')
    //                                             ->where('field', $field)
    //                                             ->update(['value' => $val]);
    //         }
    //     }
    //     Cache::forget('preferences');
    //     $prefer = Preference::getAll()->pluck('value', 'field')->toArray();
    //     if(!empty($prefer)) {
    //         Session::put($prefer);
    //     }
    //     Session::flash('success',trans('message.success.save_success'));
    //     return redirect()->intended('setting-preference');
    // }
}
