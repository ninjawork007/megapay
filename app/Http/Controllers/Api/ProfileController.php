<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\RequestPayment;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    //Get User Updated Balance
    public function getUserDefaultWalletBalance()
    {
        // dd(request()->all());

        $wallet = Wallet::with(['user:id,formattedPhone', 'currency:id,code'])->where(['user_id' => request('user_id'), 'is_default' => 'Yes'])->first(['currency_id', 'user_id', 'balance', 'is_default']);
        if (!empty($wallet->user->formattedPhone))
        {
            $success['formattedPhone'] = $wallet->user->formattedPhone;
        }

        // dd($wallet);
        $success['userDefaultWalletCode'] = $wallet->currency->code;
        $success['current_balance']       = number_format((float) $wallet->balance, 2, '.', '');

        $success['status'] = $this->successStatus;
        return response()->json(['success' => $success], $this->successStatus);
    }

    public function getUserAvailableWalletsBalances()
    {
        // dd(request()->all());
        $user_id = request('user_id');
        if ($user_id)
        {
            //get user formatted phone
            $wallet = Wallet::with(['user:id,formattedPhone'])->where(['user_id' => $user_id, 'is_default' => 'Yes'])->first(['user_id']);
            if (!empty($wallet->user->formattedPhone))
            {
                $success['formattedPhone'] = $wallet->user->formattedPhone;
            }
            //
            $wallet            = new Wallet();
            $wallets           = $wallet->getAvailableBalance($user_id);
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success, 'wallets' => $wallets], $this->successStatus);
        }
        else
        {
            echo "In else block";
            exit();
            return false;
        }
    }

    public function details()
    {
        // dd(request()->all());
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    //Grab specific user profile details based on email address.
    public function getUserSpecificProfile()
    {
        // dd(request()->all());
        try
        {
            if (request('type') == 'sendMoney')
            {
                $transfer = Transfer::where(['id' => request('tr_ref_id')])->first(['receiver_id', 'email', 'phone']);
                if (!empty($transfer->receiver))
                {
                    $success['receiver']['first_name'] = $transfer->receiver->first_name;
                    $success['receiver']['last_name']  = $transfer->receiver->last_name;
                    $success['receiver']['email']      = $transfer->receiver->email;
                    $success['receiver']['picture']    = $transfer->receiver->picture;
                    $success['status']                 = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    $success['receiver']['first_name'] = null;
                    $success['receiver']['last_name']  = null;
                    $success['receiver']['email']      = $transfer->email;
                    $success['receiver']['phone']      = $transfer->phone;
                    $success['receiver']['picture']    = null;
                    $success['status']                 = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
            }
            elseif (request('type') == 'requestMoneyCreate')
            {
                $requestPaymentCreate = RequestPayment::where(['id' => request('tr_ref_id')])->first(['receiver_id', 'email', 'phone']);
                if (!empty($requestPaymentCreate->receiver))
                {
                    $success['receiver']['first_name'] = $requestPaymentCreate->receiver->first_name;
                    $success['receiver']['last_name']  = $requestPaymentCreate->receiver->last_name;
                    $success['receiver']['email']      = $requestPaymentCreate->receiver->email;
                    $success['receiver']['picture']    = $requestPaymentCreate->receiver->picture;
                    $success['status']                 = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
                else
                {
                    $success['receiver']['first_name'] = null;
                    $success['receiver']['last_name']  = null;
                    $success['receiver']['email']      = $requestPaymentCreate->email;
                    $success['receiver']['phone']      = $requestPaymentCreate->phone;
                    $success['receiver']['picture']    = null;
                    $success['status']                 = $this->successStatus;
                    return response()->json(['success' => $success], $this->successStatus);
                }
            }
            elseif (request('type') == 'requestMoneyAccept')
            {
                $requestPaymentAccept = RequestPayment::where(['id' => request('tr_ref_id')])->first(['user_id', 'email', 'phone']);
                if (!empty($requestPaymentAccept->user))
                {
                    $success['user']['first_name'] = $requestPaymentAccept->user->first_name;
                    $success['user']['last_name']  = $requestPaymentAccept->user->last_name;
                    $success['user']['email']      = $requestPaymentAccept->user->email;
                    $success['user']['picture']    = $requestPaymentAccept->user->picture;
                }
                else
                {
                    $success['user']['first_name'] = null;
                    $success['user']['last_name']  = null;
                    $success['user']['email']      = $requestPaymentAccept->email;
                    $success['user']['phone']      = $requestPaymentAccept->phone;
                    $success['user']['picture']    = null;
                    $success['status']             = $this->successStatus;
                }
                $success['status'] = $this->successStatus;
                return response()->json(['success' => $success], $this->successStatus);
            }
            else
            {
                $user              = User::where(['email' => request('email')])->first(['email']);
                $success['user']   = $user->email;
                $success['status'] = $this->successStatus;
                return response()->json(['success' => $success], $this->successStatus);
            }
        }
        catch (\Exception $e)
        {
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }

    //Fetch Specific User Profile Details
    public function getUserProfile()
    {
        // dd(request()->all());

        //id is needed for user_detail relation
        $user              = User::with('user_detail', 'user_detail.country:id')->where(['id' => request('user_id')])->first(['id', 'first_name', 'last_name', 'email', 'phone', 'formattedPhone', 'carrierCode', 'defaultCountry']);
        $success['status'] = $this->successStatus;

        //users data
        $success['user']['first_name']     = $user->first_name;
        $success['user']['last_name']      = $user->last_name;
        $success['user']['email']          = $user->email;
        $success['user']['phone']          = $user->phone;
        $success['user']['formattedPhone'] = $user->formattedPhone;
        $success['user']['carrierCode']    = $user->carrierCode;
        $success['user']['defaultCountry'] = $user->defaultCountry;

        //user details deta
        $success['user']['address_1'] = !empty($user->user_detail->address_1) ? $user->user_detail->address_1 : '';
        $success['user']['city']      = !empty($user->user_detail->city) ? $user->user_detail->city : '';
        $success['user']['state']     = !empty($user->user_detail->state) ? $user->user_detail->state : '';

        //countries and country_id
        $success['countries']          = Country::get(['id', 'name']);
        $success['user']['country_id'] = !empty($user->user_detail->country) ? $user->user_detail->country->id : '';

        //timezones and timezone
        $success['timezones']        = phpDefaultTimeZones();
        $success['user']['timezone'] = !empty($user->user_detail->timezone) ? $user->user_detail->timezone : '';

        $wallets            = Wallet::with(['currency:id,name'])->where(['user_id' => request('user_id')])->get(['id', 'currency_id', 'is_default']);
        $success['wallets'] = $wallets->map(function ($wallet)
        {
            $arr['id']           = $wallet->id;
            $arr['currencyName'] = $wallet->currency->name;
            $arr['is_default']   = $wallet->is_default;
            return $arr;
        });

        return response()->json(['success' => $success], $this->successStatus);
    }

    public function userProfileDuplicateEmailCheckApi(Request $request)
    {
        // dd(request()->all());

        $req_id = $request->user_id;
        $email  = User::where(['email' => $request->email])->where(function ($query) use ($req_id)
        {
            $query->where('id', '!=', $req_id);
        })->exists();

        if ($email)
        {
            $data['status'] = true;
            $data['fail']   = "The email has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "Email Available!";
        }
        return json_encode($data);
    }

    //Update Specific User Profile Details
    public function updateUserProfile()
    {
        // dd(request()->all());

        try
        {
            \DB::beginTransaction();

            //Update User
            $user             = User::find(request('user_id'), ['id', 'first_name', 'last_name', 'email', 'phone']);
            $user->first_name = request('first_name');
            $user->last_name  = request('last_name');
            $user->email      = request('email');
            $user->phone      = request('phone');
            $formattedPhone   = ltrim(request('phone'), '0');
            if (!empty(request('phone')))
            {
                $user->phone          = preg_replace("/[\s-]+/", "", $formattedPhone);
                $user->defaultCountry = request('user_defaultCountry');
                $user->carrierCode    = request('user_carrierCode');
                $user->formattedPhone = request('formattedPhone');
            }
            else
            {
                $user->phone          = null;
                $user->defaultCountry = null;
                $user->carrierCode    = null;
                $user->formattedPhone = null;
            }
            $user->save();

            //Update User Details
            $userDetail             = UserDetail::where(['user_id' => request('user_id')])->first(['id', 'country_id', 'address_1', 'city', 'state', 'timezone']);
            $userDetail->country_id = request('country');
            $userDetail->address_1  = request('address');
            $userDetail->city       = request('city');
            $userDetail->state      = request('state');
            $userDetail->timezone   = request('timezone');
            $userDetail->save();

            //Default wallet change - starts
            $defaultWallet = Wallet::where('user_id', request('user_id'))->where('is_default', 'Yes')->first(['id', 'is_default']);
            if ($defaultWallet->id != request('defaultWallet'))
            {
                //making existing default wallet to 'No'
                $defaultWallet->is_default = 'No';
                $defaultWallet->save();

                //Change to default wallet
                $walletToDefault             = Wallet::find(request('defaultWallet'), ['id', 'is_default']);
                $walletToDefault->is_default = 'Yes';
                $walletToDefault->save();
            }
            //Default wallet change - ends

            \DB::commit();
            $success['status'] = $this->successStatus;
            return response()->json(['success' => $success], $this->successStatus);
        }
        catch (\Exception $e)
        {
            \DB::rollBack();
            $success['status']  = $this->unauthorisedStatus;
            $success['message'] = $e->getMessage(); // echo print_r($e->getMessage());
            return response()->json(['success' => $success], $this->unauthorisedStatus);
        }
    }
}
