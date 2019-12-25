<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{

    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index($id)
    {
        $data['menu']      = 'email_template';

        $data['list_menu'] = 'menu-' . $id;

        $data['tempId']    = $id;

        $data['temp_Data'] = $temp_Data = EmailTemplate::where(['temp_id' => $id, 'type' => 'email'])->get();

        // dd($temp_Data[0]);
        return view('admin.email_templates.index', $data);
    }

    public function update(Request $request, $id)
    {
         //dd($request->all());

        $data[] = $request->en;
        $data[] = $request->ar;
        $data[] = $request->fr;
        $data[] = $request->pt;
        $data[] = $request->ru;
        $data[] = $request->es;
        $data[] = $request->tr;
        $data[] = $request->ch;
        // dd($data);

        $array = $data;

        array_unshift($array, "");

        unset($array[0]);
        // dd(array_unshift($array, ""));

        for ($i = 1; $i < 9; $i++)
        {
            EmailTemplate::where([
                'temp_id'     => $id,
                'language_id' => $i,
            ])->update($array[$i]);
        }

        $this->helper->one_time_message('success', 'Email Template Updated successfully!');
        return redirect()->intended('admin/template/' . $id);
    }
}
