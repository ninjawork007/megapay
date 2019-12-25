<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CountriesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(CountriesDataTable $dataTable)
    {
        $data['menu'] = 'country';
        return $dataTable->render('admin.countries.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'country';
            return view('admin.countries.add', $data);
        }
        else if ($_POST)
        {
            $this->validate($request, [
                'short_name'  => 'required|unique:countries,short_name',
                'name'        => 'required',
                'iso3'        => 'required|max:3',
                'number_code' => 'required|numeric',
                'phone_code'  => 'required|numeric',
            ]);

            $country              = new Country();
            $country->short_name  = $request->short_name;
            $country->name        = $request->name;
            $country->iso3        = $request->iso3;
            $country->number_code = $request->number_code;
            $country->phone_code  = $request->phone_code;
            $country->save();
            $this->helper->one_time_message('success', 'Country Added Successfully');
            return redirect('admin/settings/country');
        }

    }

    public function update(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']   = 'country';
            $data['result'] = Country::find($request->id);
            return view('admin.countries.edit', $data);
        }
        else if ($_POST)
        {
            $this->validate($request, [
                'short_name'  => 'required|unique:countries,short_name,' . $request->id,
                'name'        => 'required',
                'iso3'        => 'required|max:3',
                'number_code' => 'required|numeric',
                'phone_code'  => 'required|numeric',
            ]);

            $country              = Country::find($request->id);
            $country->short_name  = $request->short_name;
            $country->name        = $request->name;
            $country->iso3        = $request->iso3;
            $country->number_code = $request->number_code;
            $country->phone_code  = $request->phone_code;
            $country->save();
            $this->helper->one_time_message('success', 'Country Updated Successfully');
            return redirect('admin/settings/country');
        }
    }

    public function delete(Request $request)
    {
        Country::find($request->id)->delete();
        $this->helper->one_time_message('success', 'Country Deleted Successfully');
        return redirect('admin/settings/country');
    }
}
