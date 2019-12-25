<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\Merchant;
use App\Models\MerchantGroup;
use App\Models\MerchantPayment;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Image;
use Validator;

class MerchantController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index()
    {
        $data['menu']          = 'merchant';
        $data['sub_menu']      = 'merchant';
        $data['content_title'] = 'Merchant';
        $data['icon']          = 'user';
        $data['list']          = $merchants          = Merchant::with(['appInfo', 'currency:id,code'])->where(['user_id' => Auth::user()->id])->orderBy('id', 'desc')->paginate(10);
        // dd($merchants);

        $data['defaultWallet']    = $defaultWallet    = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']); //new
        return view('user_dashboard.Merchant.list', $data);
    }

    public function add()
    {
        $data['menu']     = 'merchant';
        $data['sub_menu'] = 'merchant';

        //pm_v2.3
        $data['activeCurrencies'] = $activeCurrencies = Currency::where(['status' => 'Active'])->get(['id', 'code']);
        $data['defaultWallet']    = $defaultWallet    = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        return view('user_dashboard.Merchant.add', $data);
    }

    public function store(Request $request)
    {
        $rules = array(
            'business_name' => 'required|unique_merchant_business_name',
            'site_url'      => 'required|url',
            'type'          => 'required',
            'note'          => 'required',
            'logo'          => 'mimes:png,jpg,jpeg,bmp',
        );

        $fieldNames = array(
            'business_name' => 'Business Name',
            'site_url'      => 'Site url',
            'type'          => 'Type',
            'note'          => 'Note',
            'logo'          => 'The file must be an image (png, jpg, jpeg,bmp)',
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

                $filename = null;
                $picture  = $request->logo;
                if (isset($picture))
                {
                    $dir = public_path("/user_dashboard/merchant/");
                    $ext = $picture->getClientOriginalExtension();
                    // dd($ext);
                    $filename = time() . '.' . $ext;

                    if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'bmp')
                    {
                        $img = Image::make($picture->getRealPath());
                        $img->fit(100, 100, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($dir . '/' . $filename);
                        $img->fit(70, 70, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($dir . '/thumb/' . $filename);
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }

                $merchantGroup               = MerchantGroup::where(['is_default' => 'Yes'])->select('id', 'fee')->first();
                $Merchant                    = new Merchant();
                $Merchant->user_id           = Auth::user()->id;
                $Merchant->currency_id       = $request->currency_id;
                $Merchant->merchant_group_id = isset($merchantGroup) ? $merchantGroup->id : null;
                $Merchant->business_name     = $request->business_name;
                $Merchant->site_url          = $request->site_url;
                $uuid                        = unique_code();
                $Merchant->merchant_uuid     = $uuid;
                $Merchant->type              = $request->type;
                $Merchant->note              = $request->note;
                $Merchant->logo              = $filename;
                $Merchant->fee               = isset($merchantGroup) ? $merchantGroup->fee : 0.00;
                // dd($Merchant);
                $Merchant->save();

                //If wallet does not exist, create it here
                // $wallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $request->currency_id])->first(['id']);
                // if (empty($wallet))
                // {
                //     $wallet              = new Wallet();
                //     $wallet->user_id     = auth()->user()->id;
                //     $wallet->currency_id = $request->currency_id;
                //     $wallet->balance     = 0.00; // as initially, transaction status will be pending
                //     $wallet->is_default  = 'No';
                //     $wallet->save();
                // }

                if (strtolower($request->type) == 'express')
                {
                    try {
                        $Merchant->appInfo()->create([
                            'client_id'     => str_random(30),
                            'client_secret' => str_random(100),
                        ]);
                    }
                    catch (\Exception $ex)
                    {
                        DB::rollBack();
                        $this->helper->one_time_message('error', __('Client id must be unique. Please try again!'));
                        return back();
                    }
                }

                \DB::commit();
                $this->helper->one_time_message('success', __('Merchant Created Successfully!'));
                return redirect('merchants');
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect('merchants');
            }
        }
    }

    public function edit($id)
    {
        $data['menu']             = 'merchant';
        $data['sub_menu']         = 'merchant';
        $data['content_title']    = 'Merchant';
        $data['icon']             = 'user';
        $data['activeCurrencies'] = $activeCurrencies = Currency::where(['status' => 'Active'])->get(['id', 'code']);
        $data['merchant']         = $merchant         = Merchant::with('currency:id,code')->find($id);
        $data['defaultWallet']    = $defaultWallet    = Wallet::with(['currency:id,code'])->where(['user_id' => $merchant->user->id, 'is_default' => 'Yes'])->first(['currency_id']); //new
        if (!isset($merchant) || $merchant->user_id != Auth::user()->id)
        {
            abort(404);
        }
        return view('user_dashboard.Merchant.edit', $data);
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $rules = array(
            'business_name' => 'required|unique:merchants,business_name,' . $request->id,
            'site_url'      => 'required|url',
            'note'          => 'required',
            'logo'          => 'mimes:png,jpg,jpeg,gif,bmp',
        );

        $fieldNames = array(
            'business_name' => 'Business Name',
            'site_url'      => 'Site url',
            'note'          => 'Note',
            'logo'          => 'The file must be an image (png, jpg, jpeg, gif,bmp)',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $picture  = $request->logo;
            $filename = null;

            try
            {
                \DB::beginTransaction();

                if (isset($picture))
                {
                    $dir      = public_path("/user_dashboard/merchant/");
                    $ext      = $picture->getClientOriginalExtension();
                    $filename = time() . '.' . $ext;

                    if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'bmp')
                    {
                        $img = Image::make($picture->getRealPath());
                        $img->save($dir . '/' . $filename);
                        $img->fit(100, 100, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($dir . '/thumb/' . $filename);

                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
                $Merchant                = Merchant::find($request->id, ['id', 'currency_id', 'business_name', 'site_url', 'note', 'logo']);
                $Merchant->currency_id   = $request->currency_id; //2.3
                $Merchant->business_name = $request->business_name;
                $Merchant->site_url      = $request->site_url;
                $Merchant->note          = $request->note;
                if ($filename != null)
                {
                    $Merchant->logo = $filename;
                }
                $Merchant->save();

                //If wallet does not exist, create it here
                // $wallet = Wallet::where(['user_id' => auth()->user()->id, 'currency_id' => $request->currency_id])->first(['id']);
                // if (empty($wallet))
                // {
                //     $wallet              = new Wallet();
                //     $wallet->user_id     = auth()->user()->id;
                //     $wallet->currency_id = $request->currency_id;
                //     $wallet->balance     = 0.00; // as initially, transaction status will be pending
                //     $wallet->is_default  = 'No';
                //     $wallet->save();
                // }
                \DB::commit();
                $this->helper->one_time_message('success', __('Merchant Updated Successfully!'));
                return redirect('merchants');
            }
            catch (\Exception $e)
            {
                \DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect('merchants');
            }
        }
    }

    public function detail($id)
    {
        $data['menu']          = 'merchant';
        $data['sub_menu']      = 'merchant';
        $data['content_title'] = 'Merchant';
        $data['icon']          = 'user';
        $data['merchant']      = $merchant      = Merchant::find($id);
        $data['defaultWallet'] = $defaultWallet = Wallet::with(['currency:id,code'])->where(['user_id' => $merchant->user->id, 'is_default' => 'Yes'])->first(['currency_id']); //new
        if (!isset($merchant) || $merchant->user_id != Auth::user()->id)
        {
            abort(404);
        }
        return view('user_dashboard.Merchant.detail', $data);
    }

    public function payments()
    {
        $data['menu']              = 'merchant_payment';
        $data['sub_menu']          = 'merchant_payment';
        $data['content_title']     = 'Merchant payments';
        $data['icon']              = 'user';
        $merchant                  = Merchant::where('user_id', Auth::user()->id)->pluck('id')->toArray();
        $data['merchant_payments'] = MerchantPayment::with(['merchant:id,business_name', 'payment_method:id,name', 'currency:id,code'])->whereIn('merchant_id', $merchant)
            ->select('id', 'created_at', 'merchant_id', 'payment_method_id', 'order_no', 'amount', 'charge_percentage', 'charge_fixed', 'total', 'currency_id', 'status')
            ->orderBy('id', 'desc')->paginate(15);

        return view('user_dashboard.Merchant.payments', $data);
    }
}
