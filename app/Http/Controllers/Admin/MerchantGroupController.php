<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MerchantGroupsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\MerchantGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerchantGroupController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(MerchantGroupsDataTable $dataTable)
    {
        $data['menu'] = 'merchant_group';
        return $dataTable->render('admin.merchant_group.list', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'merchant_group';
            $data['merchantGroups'] = $merchantGroups = MerchantGroup::get();
            return view('admin.merchant_group.add', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
            $rules = array(
                'name'        => 'required|unique:merchant_groups,name',
                'description' => 'required',
                'fee'         => 'required|numeric',
            );

            $fieldNames = array(
                'name'        => 'Name',
                'description' => 'Description',
                'fee'         => 'Fee',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $merchantGroup              = new MerchantGroup();
                $merchantGroup->name        = $request->name;
                $merchantGroup->description = $request->description;
                $merchantGroup->fee         = $request->fee;
                $merchantGroup->is_default  = $request->default;
                // dd($merchantGroup);
                $merchantGroup->save();

                if ($merchantGroup->is_default == 'Yes')
                {
                    MerchantGroup::where(['is_default' => 'Yes'])->where('id', '!=', $merchantGroup->id)->update(['is_default' => 'No']);
                }
                $this->helper->one_time_message('success', 'Merchant Group Added Successfully');
                return redirect('admin/settings/merchant-group');
            }
        }
        else
        {
            return redirect('admin/settings/merchant-group');
        }
    }

    public function update(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']          = 'merchant_group';
            $data['merchantGroup'] = $merchantGroup = MerchantGroup::find($request->id);
            return view('admin.merchant_group.edit', $data);
        }
        else if ($_POST)
        {
            $rules = array(
                'name'        => 'required|unique:merchant_groups,name,' . $request->id,
                'description' => 'required',
                'fee'         => 'required|numeric',
            );

            $fieldNames = array(
                'name'        => 'Name',
                'description' => 'Description',
                'fee'         => 'Fee',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {

                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $merchantGroup              = MerchantGroup::find($request->id,['id','name','description','fee','is_default']);
                $merchantGroup->name        = $request->name;
                $merchantGroup->description = $request->description;
                $merchantGroup->fee         = $request->fee;
                $merchantGroup->is_default  = $request->default;
        		$merchantGroup->save();

                if ($merchantGroup->is_default == 'Yes')
                {
                    MerchantGroup::where(['is_default' => 'Yes'])->where('id', '!=', $merchantGroup->id)->update(['is_default' => 'No']);
                }
        		$this->helper->one_time_message('success', 'Merchant Group Updated Successfully');
                return redirect('admin/settings/merchant-group');
            }
        }
        else
        {
            return redirect('admin/settings/merchant-group');
        }
    }

    public function delete(Request $request)
    {
        $merchantGroup = MerchantGroup::find($request->id);
        if (isset($merchantGroup) && $merchantGroup->is_default == 'Yes')
        {
            $this->helper->one_time_message('error', 'Default Group Cannot Be Deleted');
        }
        else
        {
            if (isset($merchantGroup))
            {
                $merchantGroup->delete();
                $this->helper->one_time_message('success', 'Group Deleted Successfully');
            }
        }
        return redirect('admin/settings/merchant-group');
    }
}
