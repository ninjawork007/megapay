<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\RolesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Illuminate\Http\Request;
use Validator;
use Cache;

class RoleController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(RolesDataTable $dataTable)
    {
        $data['menu'] = 'role';
        return $dataTable->render('admin.roles.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'role';

            $data['permissions'] = $permissions = Permission::where(['user_type' => 'Admin'])->select('id', 'group', 'display_name','user_type')->get();

            $perm = [];
            if (!empty($permissions))
            {
                foreach ($permissions as $key => $value)
                {
                    $perm[$value->group][$key]['id']           = $value->id;
                    $perm[$value->group][$key]['display_name'] = $value->display_name;
                    $perm[$value->group][$key]['user_type'] = $value->user_type;
                }
            }
            $data['perm'] = $perm;
            // d($perm,1);

            return view('admin.roles.add', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
            $rules = array(
                // 'name'         => 'required|unique:roles|max:255',
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
                $role->user_type    = 'Admin';
                // dd($role);
                $role->save();

                foreach ($request->permission as $key => $value)
                {
                    // dd($value);
                    PermissionRole::firstOrCreate(['permission_id' => $value, 'role_id' => $role->id]);
                }
                $this->helper->one_time_message('success', 'Role Added Successfully');
                return redirect('admin/settings/roles');
            }
        }
        else
        {
            return redirect('admin/settings/roles');
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        if (!$_POST)
        {
            $data['menu']   = 'role';
            $data['result'] = $result = Role::find($request->id);

            $data['stored_permissions'] = $stored_permissions = Role::permission_role($request->id)->toArray();

            $permissions = Permission::where(['user_type' => 'Admin'])->select('id', 'group', 'display_name','user_type')->get();

            $perm = [];
            if (!empty($permissions))
            {
                foreach ($permissions as $key => $value)
                {
                    $perm[$value->group][$key]['id']           = $value->id;
                    $perm[$value->group][$key]['display_name'] = $value->display_name;
                    $perm[$value->group][$key]['user_type'] = $value->user_type;
                }
            }
            $data['permissions'] = $perm;
            // d($perm,1);

            return view('admin.roles.edit', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
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
                $role->user_type    = 'Admin';

                // dd($role);
                $role->save();

                $stored_permissions = Role::permission_role($request->id);
                // dd($stored_permissions);

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

                // Cache::forget('admin_permissions');
                // $permissionId = PermissionRole::where('role_id', $role->id)->get(['permission_id']);
                // Cache::put('admin_permissions', $permissionId, 1440);

                $this->helper->one_time_message('success', 'Role Updated Successfully');
                return redirect('admin/settings/roles');
            }
        }
        else
        {
            return redirect('admin/settings/roles');
        }
    }

    public function duplicateRoleCheck(Request $request)
    {
        // dd($request->id);
        // dd($request->name);

        $req_id = $request->id;
        if (isset($request->id))
        {
            if (isset($request->user_type) && $request->user_type == "Admin")
            {
                // dd('has id');
                $name = Role::where(['user_type' => $request->user_type, 'name' => $request->name])
                ->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })
                ->exists();
            }
            else
            {
                // dd('has id');
                $User = $request->user_type;
                $name = Role::where(['user_type' => $User, 'name' => $request->name])
                ->where(function ($query) use ($req_id)
                {
                    $query->where('id', '!=', $req_id);
                })
                ->exists();
            }
        }
        else
        {
            // dd('no id');
            if (isset($request->user_type) && $request->user_type == "Admin")
            {
                $name = Role::where(['user_type' => $request->user_type, 'name' => $request->name])->exists();
            }
            else
            {
                // dd('no id');
                $User = $request->user_type;
                $name = Role::where(['user_type' => $User, 'name' => $request->name])->exists();
            }
        }

        if ($name)
        {
            $data['status'] = true;
            $data['fail']   = "The name has already been taken!";
        }
        else
        {
            $data['status']  = false;
            $data['success'] = "Name Available!";
        }
        return json_encode($data);
    }

    public function delete(Request $request)
    {
        Role::where('id', $request->id)->delete();
        PermissionRole::where('role_id', $request->id)->delete();

        $role_user = \DB::table('role_user')->where(['role_id' => $request->id, 'user_type' => 'Admin'])->first();
        // dd($role_user);

        if (isset($role_user))
        {
            $role_user->delete();
        }
        $this->helper->one_time_message('success', 'Role Deleted Successfully');
        return redirect('admin/settings/roles');
    }
}
