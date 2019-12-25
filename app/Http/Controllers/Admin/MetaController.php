<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\MetasDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MetaController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(MetasDataTable $dataTable)
    {
        $data['menu'] = 'meta';
        return $dataTable->render('admin.metas.view', $data);
    }

    public function update(Request $request)
    {
        if (!$_POST)
        {
            $data['menu']   = 'meta';
            $data['result'] = Meta::find($request->id);
            return view('admin.metas.edit', $data);
        }
        else if ($_POST)
        {
            $rules = array(
                'url'         => 'required|unique:metas,url,' . $request->id,
                'title'       => 'required',
                'description' => 'required',
            );

            $fieldNames = array(
                'url'         => 'Url',
                'title'       => 'Title',
                'description' => 'Description',
                'keywords'    => 'Keywords',
            );
            $validator = Validator::make($request->all(), $rules);
            $validator->setAttributeNames($fieldNames);

            if ($validator->fails())
            {
                return back()->withErrors($validator)->withInput();
            }
            else
            {
                $metas              = Meta::find($request->id);
                $metas->url         = $request->url;
                $metas->title       = $request->title;
                $metas->description = $request->description;
                $metas->keywords    = $request->keywords;
                $metas->save();
                $this->helper->one_time_message('success', 'Meta Updated Successfully');
                return redirect('admin/settings/metas');
            }
        }

    }

    public function delete(Request $request)
    {
        Meta::find($request->id)->delete();
        $this->helper->one_time_message('success', 'Meta Deleted Successfully');
        return redirect('admin/settings/metas');
    }

    /*Extra Function -- maybe needed later to add metas*/

    /*public function add(Request $request)
    {
        if ($_POST)
        {
            $this->validate($request, [
                'url'         => 'required|unique:metas',
                'title'       => 'required|alpha_spaces',
                'description' => 'required|alpha_spaces',
            ]);

            $user               = new Meta();
            $metas->url         = $request->url;
            $metas->title       = $request->title;
            $metas->description = $request->description;
            $metas->keywords    = $request->keywords;
            $user->save();

            $this->helper->one_time_message('success', 'Meta Added Successfully');
            return redirect('admin/metas');
        }
    }*/
}
