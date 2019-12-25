<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\LanguagesDataTable;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\Language;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Storage;

class LanguageController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index(LanguagesDataTable $dataTable)
    {
        $data['menu'] = 'language';
        return $dataTable->render('admin.languages.view', $data);
    }

    public function add(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'language';
            return view('admin.languages.add', $data);
        }
        else if ($_POST)
        {
            $this->validate($request, [
                'name'       => 'required|unique:languages,name',
                'short_name' => 'required',
                'flag'       => 'mimes:png,jpg,jpeg,gif,bmp|max:10000',
            ]);

            $language             = new Language();
            $language->name       = $request->name;
            $language->short_name = $request->short_name;
            $language->status     = $request->status;

            //flag
            if ($request->hasFile('flag'))
            {
                $flag = $request->file('flag');
                if (isset($flag))
                {
                    $filename  = time() . '.' . $flag->getClientOriginalExtension();
                    $extension = strtolower($flag->getClientOriginalExtension());
                    $location  = public_path('uploads/languages-flags/' . $filename);

                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                    {
                        Image::make($flag)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        $language->flag = $filename; //Store
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }
            // dd($language);
            $language->save();
            $this->helper->one_time_message('success', 'Language Added Successfully');
            return redirect('admin/settings/language');
        }
    }

    public function update(Request $request)
    {
        if (!$_POST)
        {
            $data['menu'] = 'language';

            $data['result'] = $result = Language::find($request->id);
            // dd($result);
            return view('admin.languages.edit', $data);
        }
        else if ($_POST)
        {
            // dd($request->all());
            $this->validate($request, [
                'name'       => 'required|unique:languages,name,' . $request->id,
                'short_name' => 'required',
                'flag'       => 'mimes:png,jpg,jpeg,gif,bmp|max:10000',
            ]);
            $language             = Language::find($request->id);
            $language->name       = $request->name;
            $language->short_name = $request->short_name;

            // Update logo
            if ($request->hasFile('flag'))
            {
                $flag = $request->file('flag');

                if (isset($flag))
                {
                    $filename  = time() . '.' . $flag->getClientOriginalExtension();
                    $extension = strtolower($flag->getClientOriginalExtension());

                    $location = public_path('uploads/languages-flags/' . $filename);
                    if (file_exists($location))
                    {
                        unlink($location);
                    }

                    if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                    {
                        Image::make($flag)->fit(120, 80, function ($constraint)
                        {
                            $constraint->aspectRatio();
                        })->save($location);

                        //Old file assigned to a variable
                        $oldfilename = $language->flag;

                        //Update the database
                        $language->flag = $filename;

                        //Delete old photo
                        Storage::delete($oldfilename);
                    }
                    else
                    {
                        $this->helper->one_time_message('error', 'Invalid Image Format!');
                    }
                }
            }
            $language->status = $request->status;
            // dd($language);
            $language->save();

            $this->helper->one_time_message('success', 'Language Updated Successfully');

            return redirect('admin/settings/language');
        }
        else
        {
            return redirect('admin/settings/language');
        }
    }

    public function deleteFlag(Request $request)
    {
        $flag = $_POST['flag'];
        // dd($flag);

        if (isset($flag))
        {
            $lang = Language::where(['id' => $request->language_id, 'flag' => $request->flag])->first();
            if ($lang)
            {
                Language::where(['id' => $request->language_id, 'flag' => $request->flag])->update(['flag' => null]);
                if ($flag != null)
                {
                    $dir = public_path('uploads/languages-flags/' . $flag);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = 'Flag has been successfully deleted!';
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
        if (isset($request->id))
        {
            Language::find($request->id)->delete();
            Storage::delete($request->flag); //Delete the flag image from the server , to save space
        }
        $this->helper->one_time_message('success', 'Language Deleted Successfully');
        return redirect('admin/settings/language');
    }
}
