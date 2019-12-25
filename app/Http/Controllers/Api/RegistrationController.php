<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use App\Models\Country;
use App\Models\EmailTemplate;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\RequestPayment;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\VerifyUser;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;
    public $email;

    public function __construct()
    {
        $this->email = new EmailController();
    }

    public function getMerchantUserRoleExistence()
    {
        $checkMerchantRole = Role::where(['customer_type' => 'merchant'])->first(['id']);
        $checkUserRole     = Role::where(['customer_type' => 'user', 'is_default' => 'Yes'])->first(['id']);
        // dd($checkMerchantRole, $checkUserRole);

        return response()->json([
            'status'            => $this->successStatus,
            'checkMerchantRole' => $checkMerchantRole,
            'checkUserRole'     => $checkUserRole,
        ]);
    }

    public function duplicateEmailCheckApi(Request $request)
    {
        $email = User::where(['email' => $request->email])->exists();
        if ($email)
        {
            $data['status'] = true;
            $data['fail']   = 'The email has already been taken!';
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "Email Available!";
        }
        return json_encode($data);
    }

    public function duplicatePhoneNumberCheckApi(Request $request)
    {
        $req_id = $request->id;
        if (isset($req_id))
        {
            // dd('with id');
            $phone = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone)])->where(function ($query) use ($req_id)
            {
                $query->where('id', '!=', $req_id);
            })->exists();
        }
        else
        {
            // dd('no id');
            $phone = User::where(['phone' => preg_replace("/[\s-]+/", "", $request->phone)])->exists();
        }
        // dd($phone);

        if ($phone)
        {
            $data['status'] = true;
            $data['fail']   = "The phone number has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "The phone number is Available!";
        }
        return json_encode($data);
    }

    public function registration(Request $request)
    {
        // dd($request->all());

        $rules = array(
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required',
        );

        $fieldNames = array(
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email',
            'password'   => 'Password',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);
        if ($validator->fails())
        {
            $response['message'] = "Email/Phone already exist.";
            $response['status']  = $this->unauthorisedStatus;
            return response()->json(['success' => $response], $this->successStatus);
        }
        else
        {
            //default_timezone
            $timezone = Setting::where('name', 'default_timezone')->first(['value']);
            //default_currency
            $default_currency = Setting::where('name', 'default_currency')->first(['value']);
            //Random Country
            $randomCountry = Country::first(['id']);
            try
            {
                \DB::beginTransaction();

                //Create New user - starts
                $user             = new User();
                $user->type       = $request->type; //new
                $user->first_name = $request->first_name;
                $user->last_name  = $request->last_name;
                $user->email      = $request->email;
                $formattedPhone   = str_replace('+' . $request->carrierCode, "", $request->formattedPhone);
                if (!empty($request->phone))
                {
                    $user->phone          = preg_replace("/[\s-]+/", "", $formattedPhone);
                    $user->defaultCountry = $request->defaultCountry;
                    $user->carrierCode    = $request->carrierCode;
                    $user->formattedPhone = $request->formattedPhone;
                }
                else
                {
                    $user->phone          = null;
                    $user->defaultCountry = null;
                    $user->carrierCode    = null;
                }
                $user->password = \Hash::make($request->password);
                if ($request->type == 'user')
                {
                    $role = Role::select('id')->where(['customer_type' => 'user', 'user_type' => 'User', 'is_default' => 'Yes'])->first();
                }
                else
                {
                    $role = Role::select('id')->where(['customer_type' => 'merchant', 'user_type' => 'User'])->first();
                    if (!empty($role))
                    {
                        $checkPermission = Permission::where(['user_type' => 'User'])->get(['id']); //checkPermission
                        if (!empty($checkPermission))
                        {
                            foreach ($checkPermission as $cp)
                            {
                                $checkPermissionRole = PermissionRole::where(['permission_id' => $cp->id, 'role_id' => $role->id]); //checkPermissionRole
                                if (!empty($checkPermissionRole))
                                {
                                    PermissionRole::firstOrCreate(['permission_id' => $cp->id, 'role_id' => $role->id]);
                                }
                            }
                        }
                    }
                }
                $user->role_id = $role->id;
                $user->save();
                //Create New user - ends

                // Assigning user type and role to new user - starts
                RoleUser::insert(['user_id' => $user->id, 'role_id' => $role->id, 'user_type' => 'User']);
                // Assigning user type and role to new user - ends

                //UserDetail - starts
                $UserDetail          = new UserDetail();
                $UserDetail->user_id = $user->id;

                if (!empty($randomCountry))
                {
                    $UserDetail->country_id = $randomCountry->id;
                }
                $UserDetail->timezone = $timezone->value;
                $UserDetail->save();
                //UserDetail - ends

                //Wallet creation - starts
                $wallet              = new Wallet();
                $wallet->user_id     = $user->id;
                $wallet->currency_id = $default_currency->value;
                $wallet->balance     = 0.00;
                $wallet->is_default  = 'Yes';
                $wallet->save();
                //Wallet creation - ends

                /**
                * Entry for unknown transfer /request payments - starts
                */
                $userEmail          = $user->email;
                $userFormattedPhone = $user->formattedPhone;

                /**
                 * Entry for unknown transfer - starts
                 */
                if (!empty($user->email) || !empty($user->formattedPhone))
                {
                    $unknownTransferTransaction = Transaction::where(function ($q) use ($userEmail)
                    {
                        $q->where(['user_type' => 'unregistered']);
                        $q->where(['email' => $userEmail]);
                        $q->whereIn('transaction_type_id', [Transferred]);
                    })
                    ->orWhere(function ($q) use ($userFormattedPhone)
                    {
                        $q->where(['user_type' => 'unregistered']);
                        $q->where(['phone' => $userFormattedPhone]);
                        $q->whereIn('transaction_type_id', [Transferred]);
                    })
                    ->get(['transaction_reference_id', 'uuid']);
                    // dd($unknownTransferTransaction);
                }

                if (isset($unknownTransferTransaction))
                {
                    foreach ($unknownTransferTransaction as $key => $value)
                    {
                        $transfer = Transfer::where(['uuid' => $value->uuid])->first(['id','uuid', 'amount', 'currency_id', 'receiver_id', 'status']);
                        // dd($transfer);

                        if ($transfer->uuid == $value->uuid)
                        {
                            $transfer->receiver_id = $user->id;
                            $transfer->status      = 'Success';
                            $transfer->save();

                            Transaction::where([
                                'transaction_reference_id' => $value->transaction_reference_id,
                                'transaction_type_id'      => Transferred,
                            ])->update([
                                'end_user_id' => $user->id,
                                'user_type'   => 'registered',
                                'status'      => 'Success',
                            ]);

                            Transaction::where([
                                'transaction_reference_id' => $value->transaction_reference_id,
                                'transaction_type_id'      => Received,
                            ])->update([
                                'user_id'   => $user->id,
                                'user_type' => 'registered',
                                'status'    => 'Success',
                            ]);

                            $unknownTransferWallet = Wallet::where(['user_id' => $user->id, 'currency_id' => $transfer->currency_id])->first(['id','balance']);
                            if (empty($unknownTransferWallet))
                            {
                                $wallet              = new Wallet();
                                $wallet->user_id     = $user->id;
                                $wallet->currency_id = $transfer->currency_id;
                                if ($wallet->currency_id == $default_currency->value)
                                {
                                    $wallet->is_default = 'Yes';
                                }
                                else
                                {
                                    $wallet->is_default = 'No';
                                }
                                $wallet->balance = $transfer->amount;
                                $wallet->save();
                            }
                            else
                            {
                                $unknownTransferWallet->balance = ($unknownTransferWallet->balance + $transfer->amount);
                                $unknownTransferWallet->save();
                            }
                        }
                    }
                }
                /**
                 * Entry for unknown transfer - ends
                 */
                // dd($userEmail);

                /**
                 * Entry for unknown request payment - starts
                 */
                if (!empty($user->email) || !empty($user->formattedPhone))
                {
                    $unknownRequestTransaction = Transaction::where(function ($q) use ($userEmail)
                    {
                        $q->where(['user_type' => 'unregistered']);
                        $q->where(['email' => $userEmail]);
                        $q->whereIn('transaction_type_id', [Request_From]);
                    })
                    ->orWhere(function ($q) use ($userFormattedPhone)
                    {
                        $q->where(['user_type' => 'unregistered']);
                        $q->where(['phone' => $userFormattedPhone]);
                        $q->whereIn('transaction_type_id', [Request_From]);
                    })
                    ->get(['transaction_reference_id', 'uuid']);
                    // dd($unknownRequestTransaction);
                }
                if (isset($unknownRequestTransaction))
                {
                    foreach ($unknownRequestTransaction as $key => $value)
                    {
                        $request_payment = RequestPayment::where(['uuid' => $value->uuid])->first(['id','uuid', 'currency_id', 'receiver_id']);
                        // dd($request_payment);
                        if ($request_payment->uuid == $value->uuid)
                        {
                            $request_payment->receiver_id = $user->id;
                            $request_payment->save();

                            Transaction::where([
                                'transaction_reference_id' => $value->transaction_reference_id,
                                'transaction_type_id'      => Request_From,
                            ])->update([
                                'end_user_id' => $user->id,
                                'user_type'   => 'registered',
                            ]);

                            Transaction::where([
                                'transaction_reference_id' => $value->transaction_reference_id,
                                'transaction_type_id'      => Request_To,
                            ])->update([
                                'user_id'   => $user->id,
                                'user_type' => 'registered',
                            ]);

                            $unknownRequestWallet = Wallet::where(['user_id' => $user->id, 'currency_id' => $request_payment->currency_id])->first(['id']);
                            if (empty($unknownRequestWallet))
                            {
                                $wallet              = new Wallet();
                                $wallet->user_id     = $user->id;
                                $wallet->currency_id = $request_payment->currency_id;
                                if ($wallet->currency_id == $default_currency->value)
                                {
                                    $wallet->is_default = 'Yes';
                                }
                                else
                                {
                                    $wallet->is_default = 'No';
                                }
                                $wallet->balance = 0.00;
                                $wallet->save();
                            }
                        }
                    }
                }
                /**
                 * Entry for unknown request payment - ends
                 */

                /**
                * Entry for unknown transfer /request payments - ends
                */

                //email_verification
                if (!$user->user_detail->email_verification)
                {
                    if (checkVerificationMailStatus() == "Enabled")
                    {
                        $verifyUser = VerifyUser::where(['user_id' => $user->id])->first(['id']);
                        if (empty($verifyUser))
                        {
                            $verifyUserNewRecord          = new VerifyUser();
                            $verifyUserNewRecord->user_id = $user->id;
                            $verifyUserNewRecord->token   = str_random(40);
                            $verifyUserNewRecord->save();
                        }

                        //mail - temp -17
                        $englishUserVerificationEmailTempInfo = EmailTemplate::where(['temp_id' => 17, 'lang' => 'en', 'type' => 'email'])->select('subject', 'body')->first();
                        $userVerificationEmailTempInfo        = EmailTemplate::where([
                            'temp_id'     => 17,
                            'language_id' => getDefaultLanguage(),
                            'type'        => 'email',
                        ])->select('subject', 'body')->first();

                        if (!empty($userVerificationEmailTempInfo->subject) && !empty($userVerificationEmailTempInfo->body))
                        {
                            // subject
                            $userVerificationEmailTempInfo_sub = $userVerificationEmailTempInfo->subject;
                            $userVerificationEmailTempInfo_msg = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $userVerificationEmailTempInfo->body); //p-1 - $user->first_name . ' ' . $user->last_name
                        }
                        else
                        {
                            $userVerificationEmailTempInfo_sub = $englishUserVerificationEmailTempInfo->subject;
                            $userVerificationEmailTempInfo_msg = str_replace('{user}', $user->first_name . ' ' . $user->last_name, $englishUserVerificationEmailTempInfo->body);
                        }
                        $userVerificationEmailTempInfo_msg = str_replace('{email}', $user->email, $userVerificationEmailTempInfo_msg);                                            //p-2 - $user->email
                        $userVerificationEmailTempInfo_msg = str_replace('{verification_url}', url('user/verify', $user->verifyUser->token), $userVerificationEmailTempInfo_msg); //p-3 - $user->verifyUser->token
                        $userVerificationEmailTempInfo_msg = str_replace('{soft_name}', getCompanyName(), $userVerificationEmailTempInfo_msg);

                        if (checkAppMailEnvironment())
                        {
                            try
                            {
                                $this->email->sendEmail($user->email, $userVerificationEmailTempInfo_sub, $userVerificationEmailTempInfo_msg);
                            }
                            catch (\Exception $e)
                            {
                                \DB::rollBack();
                                $success['status']  = $this->unauthorisedStatus;
                                $success['message'] = $e->getMessage();
                                return response()->json(['success' => $success], $this->unauthorisedStatus);
                            }
                        }
                    }
                }
                \DB::commit();
                $success['status']  = $this->successStatus;
                $success['message'] = "Registration Successfull!";
                return response()->json(['success' => $success], $this->successStatus);
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $success['status']  = $this->unauthorisedStatus;
                $success['message'] = $e->getMessage();
                return response()->json(['success' => $success], $this->unauthorisedStatus);
            }
        }
    }
}
