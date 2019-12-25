<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CurrenciesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Storage;

class CurrencyController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(CurrenciesDataTable $dataTable)
    {
        $data['menu'] = 'currency';
        return $dataTable->render('admin.currencies.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'currency';
            return view('admin.currencies.add', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
            $this->validate($request, [
                'name'   => 'required|unique:currencies',
                'code'   => 'required',
                'symbol' => 'required',
                // 'rate'   => 'required|numeric|min:1',
                'rate'   => 'required|numeric',
                'logo'   => 'mimes:png,jpg,jpeg,gif,bmp|max:10000',
            ], [
                'rate.min' => 'Please enter values greater than zero!',
            ]);

            $currency                = new Currency();
            $currency->name          = $request->name;
            $currency->code          = $request->code;
            $currency->symbol        = $request->symbol;
            $currency->rate          = $request->rate;
            $currency->status        = $request->status;
            $currency->exchange_from = $request->exchange_from;

            //check if logo exits
            if ($request->hasFile('logo'))
            {
                $logo = $request->file('logo');
                if (isset($logo))
                {
                    $filename  = time() . '.' . $logo->getClientOriginalExtension();
                    $extension = strtolower($logo->getClientOriginalExtension());
                    $location  = public_path('uploads/currency_logos/' . $filename);

                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                    {
                        Image::make($logo)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        $currency->logo = $filename; //Store
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }

            $currency->default = '0';
            // dd($currency);
            $currency->save();

            $this->helper->one_time_message('success', 'Currency Added Successfully');
            return redirect('admin/settings/currency');
        }
    }

    public function update(Request $request, $id)
    {
        if (!$_POST)
        {
            $data['menu']   = 'currency';
            $data['result'] = Currency::find($request->id);
            return view('admin.currencies.edit', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
            $this->validate($request, [
                'name'   => 'required',
                'code'   => 'required',
                'symbol' => 'required',
                // 'rate'   => 'required|numeric|min:0.01',
                'rate'   => 'required|numeric',
                'logo'   => 'mimes:png,jpg,jpeg,gif,bmp|max:10000',
            ], [
                'rate.min' => 'Please enter values greater than 0!',
            ]);

            $currency         = Currency::find($request->id);
            $currency->name   = $request->name;
            $currency->code   = $request->code;
            $currency->symbol = $request->symbol;
            $currency->rate   = $request->rate;

            if ($request->default_currency == 1)
            {
                $currency->status  = 'Active';
                $currency->default = 1;
            }
            else
            {
                $currency->status  = $request->status;
                $currency->default = 0;
            }

            $currency->exchange_from = $request->exchange_from;

            // Update logo
            if ($request->hasFile('logo'))
            {
                $logo = $request->file('logo');

                if (isset($logo))
                {
                    $filename  = time() . '.' . $logo->getClientOriginalExtension();
                    $extension = strtolower($logo->getClientOriginalExtension());

                    $location = public_path('uploads/currency_logos/' . $filename);
                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                    {
                        Image::make($logo)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        //Old file assigned to a variable
                        $oldfilename = $currency->logo;

                        //Update the database
                        $currency->logo = $filename;

                        //Delete old photo
                        Storage::delete($oldfilename);
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }
            // dd($currency);

            $currency->save();

            $this->helper->one_time_message('success', 'Currency Updated Successfully');
            return redirect('admin/settings/currency');
        }
    }

    public function deleteCurrencyLogo(Request $request)
    {
        $logo = $_POST['logo'];

        if (isset($logo))
        {
            $currency = Currency::where(['id' => $request->currency_id, 'logo' => $request->logo])->first();

            if ($currency)
            {
                Currency::where(['id' => $request->currency_id, 'logo' => $request->logo])->update(['logo' => null]);

                if ($logo != null)
                {
                    $dir = public_path('uploads/currency_logos/' . $logo);
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

    public function delete(Request $request)
    {
        $currency = Currency::find($request->id);

        $transaction = Transaction::where(['currency_id' => $currency->id])->first();
        // dd($transaction);

        if (isset($transaction))
        {
            $this->helper->one_time_message('error', 'Sorry, You can\'t delete this currency, it\'s transaction exists!');
        }
        elseif (isset($currency) && $currency->default == 1)
        {
            $this->helper->one_time_message('error', 'Sorry, You can\'t delete this currency, it\'s default currency!');
        }
        else
        {
            if (isset($currency->id))
            {
                $currency->delete();
                Storage::delete($request->image); //Delete the photo from the server , to save space
            }
            $this->helper->one_time_message('success', 'Currency Deleted Successfully');
        }
        return redirect('admin/settings/currency');
    }
}
