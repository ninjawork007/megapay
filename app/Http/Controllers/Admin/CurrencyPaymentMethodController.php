<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Bank;
use App\Models\Country;
use App\Models\Currency;
use App\Models\CurrencyPaymentMethod;
use App\Models\File;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Image;

class CurrencyPaymentMethodController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function paymentMethodList($tab, $id)
    {
        // dd($id);
        $data['menu'] = 'currency';

        $data['list_menu'] = $tab;

        if ($tab == 'stripe')
        {
            $tab = 'Stripe';
        }
        elseif ($tab == 'paypal')
        {
            $tab = 'Paypal';
        }
        elseif ($tab == 'twoCheckout')
        {
            $tab = '2Checkout';
        }
        elseif ($tab == 'payUMoney')
        {
            $tab = 'PayUMoney';
        }
        elseif ($tab == 'coinPayments')
        {
            $tab = 'CoinPayments';
        }
        elseif ($tab == 'mts')
        {
            $tab = 'Mts';
        }
        // elseif ($tab == 'perfectMoney')
        // {
        //     $tab = 'perfectMoney';
        // }
        elseif ($tab == 'payeer')
        {
            $tab = 'Payeer';
        }
        elseif ($tab == 'bank')
        {
            $tab = 'bank';
        }
        // elseif ($tab == 'cash')
        // {
        //     $tab = 'CashPayment';
        // }
        // elseif ($tab == 'mobile')
        // {
        //     $tab = 'MobilePayment';
        // }
        $paymentMethod         = PaymentMethod::where(['name' => ucfirst($tab), 'status' => 'Active'])->first();
        // dd($paymentMethod);
        $data['paymentMethod'] = $paymentMethod->id;

        $data['currencyPaymentMethod'] = $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $paymentMethod->id, 'currency_id' => $id])->first();

        $data['currencyList'] = Currency::where(['status' => 'Active'])->get();

        $data['banks'] = $banks = Bank::where(['currency_id' => $id])->get();

        $data['countries'] = Country::get(['id', 'name']);

        $data['currency'] = $currency = Currency::where(['id' => $id])->select('id', 'name')->first();

        return view('admin.currencyPaymentMethod.list', $data);
    }

    public function updatePaymentMethodCredentials(Request $request)
    {
        //dd($request->all());
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();
        //dd($currencyPaymentMethod);

        if (empty($currencyPaymentMethod))
        {
            $currencyPaymentMethod              = new CurrencyPaymentMethod();
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;

            if (!empty($request->stripe))
            {
                $currencyPaymentMethod->method_data = json_encode($request->stripe);

                if (isset($request->stripe_status) && $request->stripe_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->paypal))
            {
                $currencyPaymentMethod->method_data = json_encode($request->paypal);
                if (isset($request->paypal_status) && $request->paypal_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->twoCheckout))
            {
                $currencyPaymentMethod->method_data = json_encode($request->twoCheckout);
                if (isset($request->twoCheckout_status) && $request->twoCheckout_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->payUMoney))
            {
                $currencyPaymentMethod->method_data = json_encode($request->payUMoney);
                if (isset($request->payUMoney_status) && $request->payUMoney_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->coinPayments))
            {
                $currencyPaymentMethod->method_data = json_encode($request->coinPayments);
                if (isset($request->coinPayments_status) && $request->coinPayments_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->payeer))
            {
                $currencyPaymentMethod->method_data = json_encode($request->payeer);
                if (isset($request->payeer_status) && $request->payeer_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            // elseif (!empty($request->perfectMoney))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->perfectMoney);
            //     if (isset($request->perfectMoney_status) && $request->perfectMoney_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }

            // elseif (!empty($request->cash))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->cash);
            //     if (isset($request->cash_status) && $request->cash_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }
            // elseif (!empty($request->mobile))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->mobile);
            //     if (isset($request->mobile_status) && $request->mobile_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }
            $currencyPaymentMethod->processing_time = $request->processing_time;

            // dd($currencyPaymentMethod);
            $currencyPaymentMethod->save();
        }
        else
        {
            $currencyPaymentMethod              = CurrencyPaymentMethod::find($request->id);
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;

            if (!empty($request->stripe))
            {
                $currencyPaymentMethod->method_data = json_encode($request->stripe);
                if (isset($request->stripe_status) && $request->stripe_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->paypal))
            {
                $currencyPaymentMethod->method_data = json_encode($request->paypal);
                if (isset($request->paypal_status) && $request->paypal_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->twoCheckout))
            {
                $currencyPaymentMethod->method_data = json_encode($request->twoCheckout);
                if (isset($request->twoCheckout_status) && $request->twoCheckout_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->payUMoney))
            {
                $currencyPaymentMethod->method_data = json_encode($request->payUMoney);
                if (isset($request->payUMoney_status) && $request->payUMoney_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->coinPayments))
            {
                $currencyPaymentMethod->method_data = json_encode($request->coinPayments);
                if (isset($request->coinPayments_status) && $request->coinPayments_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            elseif (!empty($request->payeer))
            {
                $currencyPaymentMethod->method_data = json_encode($request->payeer);
                if (isset($request->payeer_status) && $request->payeer_status == 'Active')
                {
                    $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
                }
                else
                {
                    $currencyPaymentMethod->activated_for = json_encode(['' => '']);
                }
            }
            // elseif (!empty($request->perfectMoney))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->perfectMoney);
            //     if (isset($request->perfectMoney_status) && $request->perfectMoney_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }
            // elseif (!empty($request->cash))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->cash);
            //     if (isset($request->cash_status) && $request->cash_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }
            // elseif (!empty($request->mobile))
            // {
            //     $currencyPaymentMethod->method_data = json_encode($request->mobile);
            //     if (isset($request->mobile_status) && $request->mobile_status == 'Active')
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            //     }
            //     else
            //     {
            //         $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            //     }
            // }

            $currencyPaymentMethod->processing_time = $request->processing_time;

            // dd($currencyPaymentMethod);
            $currencyPaymentMethod->save();
        }
        $this->helper->one_time_message('success', 'Payment Method Settings Updated Successfully!');
        return redirect('admin/settings/payment-methods/' . $request->tabText . '/' . $request->currency_id);
    }

    public function getPaymentMethodsSpecificCurrencyDetails(Request $request)
    {
        $data = [];

        $currency              = Currency::where(['id' => $request->currency_id])->first();
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();
        if ($currency && $currencyPaymentMethod)
        {
            $data['status']                = 200;
            $data['currency']              = $currency;
            $data['currencyPaymentMethod'] = $currencyPaymentMethod;
        }
        else
        {
            $data['status']   = 401;
            $data['currency'] = $currency;
        }

        $banks                  = Bank::where(['currency_id' => $request->currency_id])->get();
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%bank_id%")->get();
        $bankList               = $this->bankList($banks, $currencyPaymentMethods);
        if ($bankList)
        {
            $data['flag']  = true;
            $data['banks'] = $bankList;
        }
        else
        {
            $data['flag']  = false;
            $data['banks'] = "Banks Not FOund!";
        }

        return $data;
        exit();
    }

    public function getPaymentMethodsDetails(Request $request)
    {
        // dd($request->all());

        $data                  = [];
        $currencyPaymentMethod = CurrencyPaymentMethod::where(['method_id' => $request->paymentMethod, 'currency_id' => $request->currency_id])->first();

        if (empty($currencyPaymentMethod))
        {
            $data['status'] = 401;
        }
        else
        {
            $data['status']                = 200;
            $data['currencyPaymentMethod'] = $currencyPaymentMethod;
        }

        $banks                  = Bank::where(['currency_id' => $request->currency_id])->get();
        $currencyPaymentMethods = CurrencyPaymentMethod::where('currency_id', $request->currency_id)->where('method_data', 'like', "%bank_id%")->get();
        $bankList               = $this->bankList($banks, $currencyPaymentMethods);
        if ($bankList)
        {
            $data['flag']  = true;
            $data['banks'] = $bankList;
        }
        else
        {
            $data['flag']  = false;
            $data['banks'] = "Banks Not FOund!";
        }

        return $data;
        exit();
    }

    public function bankList($banks, $currencyPaymentMethods)
    {
        $selectedBanks = [];
        $i             = 0;
        foreach ($banks as $bank)
        {
            foreach ($currencyPaymentMethods as $cpm)
            {
                if ($bank->id == json_decode($cpm->method_data)->bank_id)
                {
                    $selectedBanks[$i]['id']             = $bank->id;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['account_name']   = $bank->account_name;
                    $selectedBanks[$i]['account_number'] = $bank->account_number;
                    $selectedBanks[$i]['bank_name']      = $bank->bank_name;
                    $selectedBanks[$i]['is_default']     = $bank->is_default;

                    $selectedBanks[$i]['activated_for'] = $cpm->activated_for;
                    $i++;
                }
            }
        }
        return $selectedBanks;
    }

    //Add Bank
    public function addBank(Request $request)
    {
        // dd($request->file('bank_logo'));

        $validation = Validator::make($request->all(), [
            'account_name'   => 'required',
            'account_number' => 'required',
            'swift_code'     => 'required',
            'bank_name'      => 'required',
            'branch_name'    => 'required',
            'branch_city'    => 'required',
            'branch_address' => 'required',
            'country'        => 'required',
        ]);

        if ($validation->passes())
        {
            // File Entry - Add
            if ($request->hasFile('bank_logo'))
            {
                $bank_logo = $request->file('bank_logo');
                if (isset($bank_logo))
                {
                    $filename       = time() . '.' . $bank_logo->getClientOriginalExtension();
                    $extension      = strtolower($bank_logo->getClientOriginalExtension());
                    $originalName   = $bank_logo->getClientOriginalName();
                    $location       = public_path('uploads/files/bank_logos/' . $filename);
                    $thumn_location = public_path('uploads/files/bank_logos/thumbs/' . $filename);

                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if (file_exists($thumn_location))
                    {
                        unlink($thumn_location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp' || $extension == 'ico')
                    {
                        //logo
                        Image::make($bank_logo)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        //thumb
                        Image::make($bank_logo)->fit(70, 70, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($thumn_location);

                        $file               = new File();
                        $file->admin_id     = Auth::guard('admin')->user()->id;
                        $file->filename     = $filename;
                        $file->originalname = $originalName;
                        $file->type         = $extension;
                        $file->save();
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }

            $bank              = new Bank();
            $bank->admin_id    = Auth::guard('admin')->user()->id;
            $bank->currency_id = $request->currency_id;
            $bank->country_id  = $request->country;

            if (!empty($request->bank_logo) && !empty($file))
            {
                $bank->file_id = $file->id;
            }

            $bank->bank_name           = $request->bank_name;
            $bank->bank_branch_name    = $request->branch_name;
            $bank->bank_branch_city    = $request->branch_city;
            $bank->bank_branch_address = $request->branch_address;
            $bank->account_name        = $request->account_name;
            $bank->account_number      = $request->account_number;
            $bank->swift_code          = $request->swift_code;
            $bank->is_default          = $request->default;
            $bank->save();

            $currencyPaymentMethod              = new CurrencyPaymentMethod();
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;

            if ($request->status == 'Active')
            {
                $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            }
            else
            {
                $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            }

            $bankJson                           = [];
            $bankJson['bank_id']                = $bank->id;
            $currencyPaymentMethod->method_data = json_encode($bankJson);
            $currencyPaymentMethod->save();

            if ($bank->is_default == 'Yes')
            {
                Bank::where(['is_default' => 'Yes', 'currency_id' => $bank->currency_id])->where('id', '!=', $bank->id)->update(['is_default' => 'No']);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Bank Added Successfully!',
            ]);
        }
        else
        {
            return response()->json([
                'status'  => 500,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    public function getCpmId(Request $request)
    {
        $bank = Bank::where(['id' => $request->bank_id])->first();
        // dd($bank->file_id);

        $bankJson            = [];
        $bankJson['bank_id'] = $bank->id;
        $cpm                 = CurrencyPaymentMethod::where(['currency_id' => $request->currency_id, 'method_data' => json_encode($bankJson)])->first(['id', 'activated_for']);
        // dd($cpm);

        $data = [];
        if ($cpm)
        {
            $data['status']              = true;
            $data['cpmId']               = $cpm->id;
            $data['cpmActivatedFor']     = $cpm->activated_for;
            $data['is_default']          = $bank->is_default;
            $data['account_name']        = $bank->account_name;
            $data['account_number']      = $bank->account_number;
            $data['bank_branch_address'] = $bank->bank_branch_address;
            $data['bank_branch_city']    = $bank->bank_branch_city;
            $data['bank_branch_name']    = $bank->bank_branch_name;
            $data['bank_name']           = $bank->bank_name;
            $data['country_id']          = $bank->country_id;
            $data['swift_code']          = $bank->swift_code;

            if (!empty($bank->file_id))
            {
                $data['bank_logo']           = $bank->file->filename;
                $data['file_id']             = $bank->file_id;
            }
        }
        else
        {
            $data['status'] = false;
            $data['cpmId']  = "CurrencyPaymentMethod Not FOund!";
        }
        return $data;
        exit();
    }

    public function deleteBankLogo(Request $request)
    {
        if (isset($request->file_id))
        {
            $file = File::find($request->file_id);
            // dd($file->filename);
            $bank = Bank::where(['file_id' => $request->file_id])->first(['file_id']);

            if ($file && $bank)
            {
                Bank::where(['file_id' => $request->file_id])->update(['file_id' => null]);
                File::find($request->file_id)->delete();

                $dir = public_path('uploads/files/bank_logos/' . $file->filename);
                if (file_exists($dir))
                {
                    unlink($dir);
                }
                $data['success'] = 1;
                $data['message'] = 'Logo deleted successfully!';
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

    //Update Bank
    public function updateBank(Request $request)
    {
        // dd($request->all());

        $validation = Validator::make($request->all(), [
            'account_name'   => 'required',
            'account_number' => 'required',
            'swift_code'     => 'required',
            'bank_name'      => 'required',
            'branch_name'    => 'required',
            'branch_city'    => 'required',
            'branch_address' => 'required',
            'country'        => 'required',
        ]);

        if ($validation->passes())
        {
            // File Entry - Update
            if ($request->hasFile('bank_logo'))
            {
                $bank_logo = $request->file('bank_logo');
                if (isset($bank_logo))
                {
                    $filename       = time() . '.' . $bank_logo->getClientOriginalExtension();
                    $extension      = strtolower($bank_logo->getClientOriginalExtension());
                    $originalName   = $bank_logo->getClientOriginalName();
                    $location       = public_path('uploads/files/bank_logos/' . $filename);
                    $thumn_location = public_path('uploads/files/bank_logos/thumbs/' . $filename);

                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if (file_exists($thumn_location))
                    {
                        unlink($thumn_location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp' || $extension == 'ico')
                    {
                        //logo
                        Image::make($bank_logo)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        //thumb
                        Image::make($bank_logo)->fit(70, 70, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($thumn_location);

                        //check file exists or not
                        $file = File::find($request->file_id);
                        if (empty($file))
                        {
                            //create file, if empty
                            $file               = new File();
                            $file->admin_id     = Auth::guard('admin')->user()->id;
                            $file->filename     = $filename;
                            $file->originalname = $originalName;
                            $file->type         = $extension;
                            $file->save();
                        }
                        else
                        {
                            $file->filename     = $filename;
                            $file->originalname = $originalName;
                            $file->type         = $extension;
                            $file->save();
                        }
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }

            $bank              = Bank::find($request->bank_id);
            $bank->currency_id = $request->currency_id;
            $bank->country_id  = $request->country;
            $bank->admin_id    = Auth::guard('admin')->user()->id;

            if (!empty($request->bank_logo) && !empty($file))
            {
                $bank->file_id = $file->id;
            }

            $bank->bank_name           = $request->bank_name;
            $bank->bank_branch_name    = $request->branch_name;
            $bank->bank_branch_city    = $request->branch_city;
            $bank->bank_branch_address = $request->branch_address;
            $bank->account_name        = $request->account_name;
            $bank->account_number      = $request->account_number;
            $bank->swift_code          = $request->swift_code;
            $bank->is_default          = $request->default;
            $bank->save();

            $currencyPaymentMethod              = CurrencyPaymentMethod::find($request->currencyPaymentMethodId);
            $currencyPaymentMethod->currency_id = $request->currency_id;
            $currencyPaymentMethod->method_id   = $request->paymentMethod;

            if ($request->status == 'Active')
            {
                $currencyPaymentMethod->activated_for = json_encode(['deposit' => '']);
            }
            else
            {
                $currencyPaymentMethod->activated_for = json_encode(['' => '']);
            }

            $bankJson                           = [];
            $bankJson['bank_id']                = $bank->id;
            $currencyPaymentMethod->method_data = json_encode($bankJson);
            $currencyPaymentMethod->save();

            if ($bank->is_default == 'Yes')
            {
                Bank::where(['is_default' => 'Yes', 'currency_id' => $bank->currency_id])->where('id', '!=', $bank->id)->update(['is_default' => 'No']);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Bank Updated Successfully!',
            ]);
        }
        else
        {
            return response()->json([
                'status'  => false,
                'message' => $validation->errors()->all(),
            ]);
        }
    }

    //Delete Bank
    public function deleteBank(Request $request)
    {
        // dd($request->all());

        $bank = Bank::find($request->bank_id);
        if (!empty($bank))
        {
            $bankJson              = [];
            $bankJson['bank_id']   = $bank->id;
            $currencyPaymentMethod = CurrencyPaymentMethod::where(['currency_id' => $bank->currency_id, 'method_data' => json_encode($bankJson)])->first();
            if (!empty($currencyPaymentMethod))
            {
                $bank->delete();
                $currencyPaymentMethod->delete();

                return response()->json([
                    'status'  => true,
                    'type'    => 'success',
                    'message' => __('Bank Deleted Successfully!'),
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => false,
                    'type'    => 'error',
                    'message' => __('Unable To Delete Bank!'),
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => false,
                'type'    => 'error',
                'message' => __('Unable To Delete Bank!'),
            ]);
        }
    }

}
