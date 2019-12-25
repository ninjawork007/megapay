<?php

use App\Models\CurrencyPaymentMethod;
use App\Models\Meta;
use App\Models\Pages;
use App\Models\Preference;
use Illuminate\Support\Facades\Session;

function setDateForDb($value)
{
    $separator   = \Session::get('date_sepa');
    $date_format = \Session::get('date_format_type');
    // dd($date);

    if (str_replace($separator, '', $date_format) == "mmddyyyy")
    {
        $value = str_replace($separator, '/', $value);
        $date  = date('Y-m-d', strtotime($value));
    }
    else
    {
        $date = date('Y-m-d', strtotime(strtr($value, $separator, '-')));
    }
    return $date;
}

function array2string($data)
{
    $log_a = "";
    foreach ($data as $key => $value)
    {
        if (is_array($value))
        {
            $log_a .= "\r\n'" . $key . "' => [\r\n" . array2string($value) . "\r\n],";
        }
        else
        {
            $log_a .= "'" . $key . "'" . " => " . "'" . str_replace("'", "\\'", $value) . "',\r\n";
        }

    }
    return $log_a;
}

function d($var, $a = false)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    if ($a)
    {
        exit;
    }
}

/**
 * [unique code
 * @return [void] [unique code for each transaction]
 */
function unique_code()
{
    return strtoupper(str_random(13));
}

/**
 * [current_balance description]
 * @return [void] [displaying default wallet balance on page header]
 */
function current_balance() //TODO: remove it
{
    $wallet            = App\Models\Wallet::with('currency:id,code')->where(['user_id' => \Auth::user()->id, 'is_default' => 'Yes'])->first();
    $balance_with_code = moneyFormat($wallet->currency->code, '+'.formatNumber($wallet->balance));
    return $balance_with_code;
}

/**
 * [userWallets description]
 * @return [void] [dropdown of wallets on page header]
 */
function userWallets()
{
    $wallet = App\Models\Wallet::where(['user_id' => \Auth::user()->id])->get();
    return $wallet;
}

/**
 * [from gobilling]
 */
function AssColumn($a = array(), $column = 'id')
{
    $two_level = func_num_args() > 2 ? true : false;
    if ($two_level)
    {
        $scolumn = func_get_arg(2);
    }

    $ret = array();
    settype($a, 'array');
    if (false == $two_level)
    {
        foreach ($a as $one)
        {
            if (is_array($one))
            {
                $ret[@$one[$column]] = $one;
            }
            else
            {
                $ret[@$one->$column] = $one;
            }

        }
    }
    else
    {
        foreach ($a as $one)
        {
            if (is_array($one))
            {
                if (false == isset($ret[@$one[$column]]))
                {
                    $ret[@$one[$column]] = array();
                }
                $ret[@$one[$column]][@$one[$scolumn]] = $one;
            }
            else
            {
                if (false == isset($ret[@$one->$column]))
                {
                    $ret[@$one->$column] = array();
                }

                $ret[@$one->$column][@$one->$scolumn] = $one;
            }
        }
    }
    return $ret;
}

/**
 * [dateFormat description]
 * @param  [type] $value    [any number]
 * @return [type] [formates date according to preferences setting in Admin Panel]
 */
function dateFormat($value)
{
    $prefix = str_replace('/', '', request()->route()->getPrefix());
    if ($prefix == 'admin')
    {
        // $timezone = session('dflt_timezone');
        $timezone = Preference::where(['category' => 'preference', 'field' => 'dflt_timezone'])->first(['value'])->value;
        // dd($timezone);
    }
    else
    {
        // $timezone = session('dflt_timezone_user');
        $user     = App\Models\User::with('user_detail:user_id,timezone')->where(['id' => auth()->user()->id])->first(['id']);
        $timezone = $user->user_detail->timezone;
    }
    $today = new DateTime($value, new DateTimeZone(config('app.timezone')));
    $today->setTimezone(new DateTimeZone($timezone));
    $value = $today->format('Y-m-d h:m:s');

    $preferenceData = Preference::where(['category' => 'preference'])->whereIn('field', ['date_format_type', 'date_sepa'])->get(['field', 'value'])->toArray();
    $preferenceData = App\Http\Helpers\Common::key_value('field', 'value', $preferenceData);
    $preference     = $preferenceData['date_format_type'];
    $separator      = $preferenceData['date_sepa'];

    $data   = str_replace(['/', '.', ' ', '-'], $separator, $preference);
    $data   = explode($separator, $data);
    $first  = $data[0];
    $second = $data[1];
    $third  = $data[2];

    $dateInfo = str_replace(['/', '.', ' ', '-'], $separator, $value);
    $datas    = explode($separator, $dateInfo);
    $year     = $datas[0];
    $month    = $datas[1];
    $day      = $datas[2];

    $dateObj   = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');

    if ($first == 'yyyy' && $second == 'mm' && $third == 'dd')
    {
        $value = $year . $separator . $month . $separator . $day;
    }
    elseif ($first == 'dd' && $second == 'mm' && $third == 'yyyy')
    {

        $value = $day . $separator . $month . $separator . $year;
    }
    elseif ($first == 'mm' && $second == 'dd' && $third == 'yyyy')
    {

        $value = $month . $separator . $day . $separator . $year;
    }
    elseif ($first == 'dd' && $second == 'M' && $third == 'yyyy')
    {
        $value = $day . $separator . $monthName . $separator . $year;
    }
    elseif ($first == 'yyyy' && $second == 'M' && $third == 'dd')
    {
        $value = $year . $separator . $monthName . $separator . $day;
    }
    return $value;
}

/**
 * [roundFormat description]
 * @param  [type] $value   [any number]
 * @return [type] [formats to 2 decimal places]
 */

function decimalFormat($value) //modified on may 21,2018
{
    $pref_amount = \Session::get('decimal_format_amount');
    // dd($pref_amount);

    if ($pref_amount == "1")
    {
        $condition = 1;
    }

    if ($pref_amount == "2")
    {
        $condition = 2;
    }

    if ($pref_amount == "3")
    {
        $condition = 3;
    }

    if ($pref_amount == "4")
    {
        $condition = 4;
    }

    if ($pref_amount == "5")
    {
        $condition = 5;
    }

    if ($pref_amount == "6")
    {
        $condition = 6;
    }

    if ($pref_amount == "7")
    {
        $condition = 7;
    }

    if ($pref_amount == "8")
    {
        $condition = 8;
    }

    if ($pref_amount == "9")
    {
        $condition = 9;
    }

    if ($pref_amount == "10")
    {
        $condition = 10;
    }

    if (!empty($pref_amount))
    {
        // dd($condition);
        $value = number_format((float) ($value), $condition, '.', '');
        return $value;
    }
}

/**
 * [roundFormat description]
 * @param  [type] $value     [any number]
 * @return [type] [placement of money symbol according to preferences setting in Admin Panel]
 */
function moneyFormat($symbol, $value)
{
    // $symbol_position = \Session::get('money_format');
    $symbol_position = Preference::where(['category' => 'preference', 'field' => 'money_format'])->first(['value'])->value;
    if (!empty($symbol_position))
    {
        if ($symbol_position == "before")
        {
            $value = $symbol . ' ' . $value;
        }
        elseif ($symbol_position == "after")
        {
            $value = $value . ' ' . $symbol;
        }
        return $value;
    }
}

function moneyFormatForDashboardProgressBars($symbol, $value)
{
    $symbol_position = \Session::get('money_format');
    if (!empty($symbol_position))
    {
        if ($symbol_position == "before")
        {
            $value = $symbol . '' . $value;
        }
        elseif ($symbol_position == "after")
        {
            $value = $value . '' . $symbol;
        }
        return $value;
    }
}

/**
 * [roundFormat description]
 * @param  [type] $value     [any number]
 * @return [type] [placement of money symbol according to preferences setting in Admin Panel]
 */
function thousandsCurrencyFormat($num)
{
    if ($num < 1000)
    {
        return $num;
    }
    $x               = round($num);
    $x_number_format = number_format($x);
    $x_array         = explode(',', $x_number_format);
    $x_parts         = array('k', 'm', 'b', 't');
    $x_count_parts   = count($x_array) - 1;
    $x_display       = $x;
    $x_display       = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
    $x_display .= $x_parts[$x_count_parts - 1];
    return $x_display;
}

//function to set pages position on frontend
function getMenuContent($position)
{
    $data = Pages::where('position', 'like', "%$position%")->where('status', 1)->get(['url','name']);
    return $data;
}

function getSocialLink()
{
    $data = collect(DB::table('socials')->get(['url', 'icon']))->toArray();
    // dd($data);
    return $data;
}

function meta($url, $field)
{
    $meta = Meta::where('url', $url)->first(['title']);
    if ($meta)
    {
        return $meta->$field;
    }
    elseif ($field == 'title' || $field == 'description' || $field == 'keyword')
    {
        return "Page Not Found";
    }
    else
    {
        return "";
    }
}

function available_balance()
{
    $wallet = App\Models\Wallet::where(['user_id' => \Auth::user()->id, 'is_default' => 'Yes'])->first(['balance']);
    return $wallet->balance;
}

function getTime($date)
{
    $time = date("H:i A", strtotime($date));
    return $time;
}

function changeEnvironmentVariable($key, $value)
{
    $path = base_path('.env');

    if (is_bool(env($key)))
    {
        $old = env($key) ? 'true' : 'false';
    }
    elseif (env($key) === null)
    {
        $old = 'null';
    }
    else
    {
        $old = env($key);
    }

    if (file_exists($path))
    {
        if ($old == 'null')
        {

            file_put_contents($path, "$key=" . $value, FILE_APPEND);
        }
        else
        {
            file_put_contents($path, str_replace(
                "$key=" . $old, "$key=" . $value, file_get_contents($path)
            ));
        }
    }
}

function getCompanyName()
{
    $setting = App\Models\Setting::where(['name' => 'name'])->first(['value']);
    return $setting->value;
}

function getDefaultLanguage()
{
    $setting = App\Models\Setting::where('name', 'default_language')->first(['value']);
    return $setting->value;
}

function thirtyDaysNameList()
{
    $data = array();
    for ($j = 30; $j > -1; $j--)
    {
        $data[30 - $j] = date("d M", strtotime("-$j day"));
    }
    return $data;
}
function getLastOneMonthDates()
{
    $data = array();
    for ($j = 30; $j > -1; $j--)
    {
        $data[30 - $j] = date("d-m", strtotime(" -$j day"));
    }
    return $data;
}

function encryptIt($value)
{
    $encoded = base64_encode(\Illuminate\Support\Facades\Hash::make($value));
    return ($encoded);
}

function formatNumber($num = 0)
{
    // $seperator      = Session::get('thousand_separator');
    // $decimal_format = Session::get('decimal_format_amount');

    $preference     = Preference::where(['category' => 'preference'])->whereIn('field', ['thousand_separator', 'decimal_format_amount'])->get(['field', 'value'])->toArray();
    $preference     = App\Http\Helpers\Common::key_value('field', 'value', $preference);
    $seperator      = $preference['thousand_separator'];
    $decimal_format = $preference['decimal_format_amount'];

    if ($seperator == '.')
    {
        $num = number_format($num, $decimal_format, ",", ".");
    }
    else if ($seperator == ',')
    {
        $num = number_format($num, $decimal_format, ".", ",");
    }
    return $num;
}

function getLanguagesListAtFooterFrontEnd()
{
    $languages = App\Models\Language::where(['status' => 'Active'])->get(['short_name','name']);
    return $languages;
}
function getAppStoreLinkFrontEnd()
{
    $app = App\Models\AppStoreCredentials::where(['has_app_credentials' => 'Yes'])->get(['logo','link']);
    return $app;
}

function getCurrencyRate($from, $to)
{
    // dd($from,$to);

    //fixed -  api key should be generated from - https://free.currencyconverterapi.com
    $url = "https://free.currencyconverterapi.com/api/v6/convert?q=$from" . "_" . "$to&compact=ultra&apiKey=fa6655ff5c936204ceb0";
    // example - https://free.currencyconverterapi.com/api/v6/convert?q=USD_EUR&compact=ultra&apiKey=fa6655ff5c936204ceb0

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
    ));
    $result = curl_exec($ch);
    curl_close($ch);
    $variable = $from . "_" . $to;
    return json_decode($result)->$variable;
}

function getfavicon()
{
    $session = session('favicon');
    if (!$session)
    {
        $session = \App\Models\Setting::where(['name' => 'favicon', 'type' => 'general'])->first(['value']);
        $session = $session->value;
        session(['favicon' => $session]);
    }
    return $session;
}

function getCompanyLogo()
{
    $session = session('company_logo');
    if (!$session)
    {
        $session = \App\Models\Setting::where(['name' => 'logo', 'type' => 'general'])->first(['value']);
        $session = $session->value;
        session(['company_logo' => $session]);
    }
    return $session;
}

function setActionSession()
{
    $key = time();
    session(['action-session' => encrypt($key)]);
    session(['session-key' => $key]);
}
function actionSessionCheck()
{
    if (!\Session::has('action-session'))
    {
        abort(404);
    }
    else
    {
        $key          = session('session-key');
        $encryptedKey = session('action-session');
        if ($key != decrypt($encryptedKey))
        {
            abort(404);
        }
    }
}

function clearActionSession()
{
    session()->forget('action-session');
    session()->forget('session-key');
}

function getCurrencyIdOfTransaction($transactions)
{
    $currencies = [];
    foreach ($transactions as $trans)
    {
        $currencies[] = $trans->currency_id;
    }
    return $currencies;
}

//fixed - for exchange rate - if set to 0 - which is unusual
function generateAmountBasedOnDfltCurrency($data, $currencyWithRate)
{
    // dd($data);
    // dd($currencyWithRate);
    // dd(session('default_currency'));

    $data_map = [];
    foreach ($data as $key => $value)
    {
        foreach ($currencyWithRate as $currencyRate)
        {
            if ($currencyRate->id == $value->currency_id)
            {
                if (!isset($data_map[$value->day][$value->month]))
                {
                    $data_map[$value->day][$value->month] = 0;
                }
                if ($value->currency_id != session('default_currency'))
                {
                    if ($currencyRate->rate != 0)
                    {
                        $data_map[$value->day][$value->month] += abs($value->amount / $currencyRate->rate);
                    }
                    else
                    {
                        $data_map[$value->day][$value->month] = 0;
                    }
                }
                else
                {
                    $data_map[$value->day][$value->month] += abs($value->amount);
                }
            }
        }
    }
    return $data_map;
}

//fixed - for exchange rate - if set to 0 - which is unusual
function generateAmountForTotal($data, $currencyWithRate)
{
    $final = 0;
    foreach ($data as $key => $value)
    {
        foreach ($currencyWithRate as $currencyRate)
        {
            if ($currencyRate->id == $value->currency_id)
            {
                if ($value->currency_id != session('default_currency'))
                {
                    if ($currencyRate->rate != 0)
                    {
                        $final += abs($value->total_charge / $currencyRate->rate);
                    }
                    else
                    {
                        // $data_map[$value->day][$value->month] = 0;
                        $final += 0;
                    }
                }
                else
                {
                    $final += abs($value->total_charge);
                }
            }
        }
    }
    return $final;
}

function checkAppMailEnvironment()
{
    $checkMail = env('APP_MAIL', 'true');
    return $checkMail;
}

function checkAppSmsEnvironment()
{
    $checkSms = env('APP_SMS', 'true');
    return $checkSms;
}

function getCompanyLogoWithoutSession()
{
    $logo = \App\Models\Setting::where(['name' => 'logo', 'type' => 'general'])->first(['value'])->value;
    return $logo;
}

//PHP Default Timezones
function phpDefaultTimeZones()
{
    $zones_array = array();
    $timestamp   = time();
    foreach (timezone_identifiers_list() as $key => $zone)
    {
        date_default_timezone_set($zone);
        $zones_array[$key]['zone']          = $zone;
        $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
    }
    return $zones_array;
    return $timezones;
}

function getNexmoDetails()
{
    $general = App\Models\Setting::where(['type' => 'Nexmo'])->get()->toArray();
    $result  = App\Http\Helpers\Common::key_value('name', 'value', $general);
    return (object) $result;
}

function sendSMS($from, $to, $message)
{
    $trimmedMsg = trim(preg_replace('/\s\s+/', ' ', $message));
    $url        = 'https://rest.nexmo.com/sms/json?' . http_build_query([
        'api_key'    => '' . trim(getNexmoDetails()->Key) . '',
        'api_secret' => '' . trim(getNexmoDetails()->Secret) . '',
        'from'       => '' . $from . '',
        'to'         => '' . $to . '',
        'text'       => '' . strip_tags($trimmedMsg) . '',
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
}

function checkVerificationMailStatus()
{
    $verification_mail = App\Models\Preference::where(['category' => 'preference', 'field' => 'verification_mail'])->first(['value'])->value;
    return $verification_mail;
}

function checkPhoneVerification()
{
    $phoneVerification = App\Models\Preference::where(['category' => 'preference', 'field' => 'phone_verification'])->first(['value'])->value;
    return $phoneVerification;
}

function twoStepVerification()
{
    $two_step_verification = Preference::where(['category' => 'preference', 'field' => 'two_step_verification'])->first(['value'])->value;
    return $two_step_verification;
}

function six_digit_random_number()
{
    return mt_rand(100000, 999999);
}

// http://www.php.net/manual/en/function.get-browser.php#101125
function getBrowser($agent)
{
    // $u_agent  = $_SERVER['HTTP_USER_AGENT'];
    $u_agent  = $agent;
    $bname    = 'Unknown';
    $platform = 'Unknown';
    $version  = "";

    // First get the platform?
    if (preg_match('/linux/i', $u_agent))
    {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent))
    {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent))
    {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent))
    {
        $bname = 'Internet Explorer';
        $ub    = "MSIE";
    }
    elseif (preg_match('/Firefox/i', $u_agent))
    {
        $bname = 'Mozilla Firefox';
        $ub    = "Firefox";
    }
    elseif (preg_match('/Chrome/i', $u_agent))
    {
        $bname = 'Google Chrome';
        $ub    = "Chrome";
    }
    elseif (preg_match('/Safari/i', $u_agent))
    {
        $bname = 'Apple Safari';
        $ub    = "Safari";
    }
    elseif (preg_match('/Opera/i', $u_agent))
    {
        $bname = 'Opera';
        $ub    = "Opera";
    }
    elseif (preg_match('/Netscape/i', $u_agent))
    {
        $bname = 'Netscape';
        $ub    = "Netscape";
    }

    // finally get the correct version number
    $known   = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches))
    {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1)
    {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub))
        {
            $version = $matches['version'][0];
        }
        else
        {
            $version = $matches['version'][1];
        }
    }
    else
    {
        $version = $matches['version'][0];
    }

    // check if we have a number
    if ($version == null || $version == "")
    {
        $version = "?";}

    return array(
        'name'     => $bname,
        'version'  => $version,
        'platform' => $platform,
    );
}

function getBrowserFingerprint($user_id, $browser_fingerprint)
{
    $getBrowserFingerprint = App\Models\DeviceLog::where(['user_id' => $user_id, 'browser_fingerprint' => $browser_fingerprint])->first(['browser_fingerprint']);
    return $getBrowserFingerprint;
}

function checkDemoEnvironment()
{
    $checkSms = env('APP_DEMO', 'true');
    return $checkSms;
}

function coinPaymentInfo()
{
    $transInfo = Session::get('transInfo');
    $cpm       = CurrencyPaymentMethod::where(['method_id' => $transInfo['payment_method'], 'currency_id' => $transInfo['currency_id']])->first(['method_data']);
    return json_decode($cpm->method_data);
}

function getCaptchaDetails()
{
    $general = App\Models\Setting::where(['type' => 'recaptcha'])->get()->toArray();
    $result  = App\Http\Helpers\Common::key_value('name', 'value', $general);
    return (object) $result;
}

function getLanguageDefault()
{
    $getDefaultLanguage = \App\Models\Language::where(['default' => '1'])->first(['id', 'short_name']);
    return $getDefaultLanguage;
}

function getAuthUserIdentity()
{
    $getAuthUserIdentity = \App\Models\DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'identity'])->first(['verification_type', 'status']);
    return $getAuthUserIdentity;
}

function getAuthUserAddress()
{
    $getAuthUserAddress = \App\Models\DocumentVerification::where(['user_id' => auth()->user()->id, 'verification_type' => 'address'])->first(['verification_type', 'status']);
    return $getAuthUserAddress;
}

//change in pm_v2.1
function getGoogleAnalyticsTrackingCode()
{
    $setting = App\Models\Setting::where(['name' => 'head_code'])->first(['value']);
    return $setting->value;
}
