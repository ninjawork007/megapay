<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\UserRolesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersRoleController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(UserRolesDataTable $dataTable)
    {
        $data['menu'] = 'user_role';
        return $dataTable->render('admin.user_roles.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'user_role';

            $data['permissions'] = $permissions = Permission::where(['user_type' => 'User'])->where('group','!=','Voucher')->select('id', 'group','user_type')->get();
            // dd($permissions);

            return view('admin.user_roles.add', $data);
        }
        else if ($_POST)
        {
            $rules = array(
                'name'         => 'required',
                'display_name' => 'required',
                'description'  => 'required',
            );

            $fieldNames = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $role               = new Role();
                $role->name         = $request->name;
                $role->display_name = $request->display_name;
                $role->description  = $request->description;
                $role->is_default   = $request->default;
                $role->user_type    = 'User';
                // dd($role);
                $role->save();

                //Only one role can have default - 'Yes'
                if ($role->is_default == 'Yes')
                {
                    Role::where(['is_default' => 'Yes'])->where('id', '!=', $role->id)->update(['is_default' => 'No']);
                }

                foreach ($request->permission as $key => $value)
                {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $role->id]);
                }
                $this->helper->one_time_message('success', 'User Group Added Successfully');
                return redirect('admin/settings/user_role');
            }
        }
        else
        {
            return redirect('admin/settings/user_role');
        }
    }

    public function update(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']   = 'user_role';
            $data['result'] = $result = Role::find($request->id);

            $data['stored_permissions'] = $stored_permissions = Role::permission_role($request->id)->toArray();
            $data['permissions'] = $permissions = Permission::where(['user_type' => 'User'])->where('group','!=','Voucher')->select('id', 'group','user_type')->get();
            return view('admin.user_roles.edit', $data);
        }
        else if ($_POST)
        {
            $rules = array(
                'name'         => 'required',
                'display_name' => 'required',
                'description'  => 'required',
            );

            $fieldNames = array(
                'name'         => 'Name',
                'display_name' => 'Display Name',
                'description'  => 'Description',
            );

            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {

                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $role               = Role::find($request->id);
                $role->name         = $request->name;
                $role->display_name = $request->display_name;
                $role->description  = $request->description;
                $role->is_default   = $request->default;
                $role->user_type    = 'User';
                // dd($role);
                $role->save();

                //Only one role can have default - 'Yes'
                if ($role->is_default == 'Yes')
                {
                    Role::where(['is_default' => 'Yes'])->where('id', '!=', $role->id)->update(['is_default' => 'No']);
                }

                $stored_permissions = Role::permission_role($request->id);
                foreach ($stored_permissions as $key => $value)
                {
                    if (!in_array($value, $request->permission))
                    {
                        PermissionRole::where(['permission_id' => $value, 'role_id' => $request->id])->delete();
                    }
                }
                foreach ($request->permission as $key => $value)
                {
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $request->id]);
                }

                // Cache::forget('permissions');
                // $permissionId = PermissionRole::where('role_id', $role->id)->get(['permission_id']);
                // Cache::put('permissions', $permissionId, 1440);

                $this->helper->one_time_message('success', 'User Group Updated Successfully');
                return redirect('admin/settings/user_role');
            }
        }
        else
        {
            return redirect('admin/settings/user_role');
        }
    }

    public function delete(Request $request)
    {
        // dd('on user role delete');
        Role::where('id', $request->id)->delete();
        PermissionRole::where('role_id', $request->id)->delete();

        $role_user = RoleUser::where(['role_id' => $request->id, 'user_type' => 'User'])->first();
        // dd($role_user);

        if (isset($role_user))
        {
            $role_user->delete();
        }
        $this->helper->one_time_message('success', 'User Group Deleted Successfully');
        return redirect('admin/settings/user_role');
    }
}
