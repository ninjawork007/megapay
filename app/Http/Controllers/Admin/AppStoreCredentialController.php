<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Common;
use App\Models\AppStoreCredentials;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Storage;

class AppStoreCredentialController extends Controller
{
    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }

    public function getAppStoreCredentials()
    {
        $data['menu'] = 'app-store-credentials';

        $data['appStoreCredentialsForGoogle'] = $appStoreCredentialsForGoogle = AppStoreCredentials::where(['company' => 'Google'])->first();
        // dd($appStoreCredentialsForGoogle);

        $data['appStoreCredentialsForApple'] = $appStoreCredentialsForApple = AppStoreCredentials::where(['company' => 'Apple'])->first();
        // dd($appStoreCredentialsForApple);

        return view('admin.settings.appStoreCredentials', $data);
    }

    public function updateGoogleCredentials(Request $request)
    {
        // dd($request->all());

        $appStoreCredentialsForGoogle = AppStoreCredentials::where(['company' => $request->playstorecompany])->first();
        // dd($appStoreCredentialsForGoogle);

        if (!empty($appStoreCredentialsForGoogle))
        {
        	// dd('not empty google');
            $googleStoreCredentials                      = AppStoreCredentials::find($request->playstoreid);
            $googleStoreCredentials->has_app_credentials = isset($request->has_app_playstore_credentials) ? $request->has_app_playstore_credentials : 'No';

            $googleStoreCredentials->link = (isset($request->playstore['link'])) ? $request->playstore['link'] : '';

            $playstoreLogo = (isset($request->playstore['logo'])) ? $request->playstore['logo'] : '';
            // dd($playstoreLogo);
            if (!empty($playstoreLogo))
            {
                $filename  = time() . '.' . $playstoreLogo->getClientOriginalExtension();
                $extension = strtolower($playstoreLogo->getClientOriginalExtension());
                $location  = public_path('uploads/app-store-logos/' . $filename);

                if (file_exists($location))
                {
                    unlink($location);
                }

                if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                {
                    Image::make($playstoreLogo)->save($location);

                    //Old file assigned to a variable
                    $oldfilename = $googleStoreCredentials->logo;

                    //Update the database
                    $googleStoreCredentials->logo = $filename;

                    //Delete old photo
                    Storage::delete($oldfilename);
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid Image Format!');
                }
            }
            // dd($googleStoreCredentials);
            // $googleStoreCredentials->company = 'Google';
            $googleStoreCredentials->save();
        }
        else
        {
        	// dd('empty google');
            $googleStoreCredentials                      = new AppStoreCredentials();
            $googleStoreCredentials->has_app_credentials = isset($request->has_app_playstore_credentials) ? $request->has_app_playstore_credentials : 'No';

            $googleStoreCredentials->link = $request->playstore['link'];

            $playstoreLogo = $request->playstore['logo'];
            if (!empty($playstoreLogo))
            {
                $filename  = time() . '.' . $playstoreLogo->getClientOriginalExtension();
                $extension = strtolower($playstoreLogo->getClientOriginalExtension());
                $location  = public_path('uploads/app-store-logos/' . $filename);

                if (file_exists($location))
                {
                    unlink($location);
                }

                if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                {
                    Image::make($playstoreLogo)->save($location);

                    $googleStoreCredentials->logo = $filename;
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid Image Format!');
                }
            }
            $googleStoreCredentials->company = 'Google';
            // dd($googleStoreCredentials);
            $googleStoreCredentials->save();
        }
        $this->helper->one_time_message('success', 'Play Store Credentials Updated Successfully');
        return redirect('admin/settings/app-store-credentials');
    }

    public function updateAppleCredentials(Request $request)
    {
        // dd($request->all());

        $appStoreCredentialsForApple = AppStoreCredentials::where(['company' => $request->appstorecompany])->first();
        // dd($appStoreCredentialsForApple);

        if (!empty($appStoreCredentialsForApple))
        {
        	// dd('not empty apple');
            $appStoreCredentials                      = AppStoreCredentials::find($request->appstoreid);
            $appStoreCredentials->has_app_credentials = isset($request->has_app_appstore_credentials) ? $request->has_app_appstore_credentials : 'No';

            $appStoreCredentials->link = $request->applestore['link'];

            $applestoreLogo = (isset($request->applestore['logo'])) ? $request->applestore['logo'] : '';
            // dd($applestoreLogo);

            if (!empty($applestoreLogo))
            {
                $filename  = time() . '.' . $applestoreLogo->getClientOriginalExtension();
                $extension = strtolower($applestoreLogo->getClientOriginalExtension());
                $location  = public_path('uploads/app-store-logos/' . $filename);

                if (file_exists($location))
                {
                    unlink($location);
                }

                if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                {
                    Image::make($applestoreLogo)->save($location);

                    $oldfilename               = $appStoreCredentials->logo;
                    $appStoreCredentials->logo = $filename;
                    //Delete old photo
                    Storage::delete($oldfilename);
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid Image Format!');
                }
            }
            $appStoreCredentials->save();
        }
        else
        {
        	// dd('empty apple');
            $appleStoreCredentials = new AppStoreCredentials();

            $appleStoreCredentials->has_app_credentials = isset($request->has_app_appstore_credentials) ? $request->has_app_appstore_credentials : 'No';

            $appleStoreCredentials->link = $request->applestore['link'];

            $applestoreLogo = $request->applestore['logo'];
            if (!empty($applestoreLogo))
            {
                $filename  = time() . '.' . $applestoreLogo->getClientOriginalExtension();
                $extension = strtolower($applestoreLogo->getClientOriginalExtension());
                $location  = public_path('uploads/app-store-logos/' . $filename);

                if (file_exists($location))
                {
                    unlink($location);
                }

                if ($extension == 'png' || $extension == 'jpg' || $extension == 'jpeg' || $extension == 'gif' || $extension == 'bmp')
                {
                    Image::make($applestoreLogo)->save($location);
                    $appleStoreCredentials->logo = $filename;
                }
                else
                {
                    $this->helper->one_time_message('error', 'Invalid Image Format!');
                }
            }
            $appleStoreCredentials->company = 'Apple';

            // dd($appleStoreCredentials);
            $appleStoreCredentials->save();
        }
        $this->helper->one_time_message('success', 'Apple Store Credentials Updated Successfully');
        return redirect('admin/settings/app-store-credentials');
    }

    public function deletePlaystoreLogo(Request $request)
    {
        $playstoreLogo = $_POST['playstoreLogo'];

        if (isset($playstoreLogo))
        {
            $appStoreCredentialsForGoogle = AppStoreCredentials::where(['company' => $request->playstorecompany, 'logo' => $request->playstoreLogo])->first();

            if ($appStoreCredentialsForGoogle)
            {
                AppStoreCredentials::where(['company' => $request->playstorecompany, 'logo' => $request->playstoreLogo])->update(['logo' => null]);

                if ($playstoreLogo != null)
                {
                    $dir = public_path('uploads/app-store-logos/' . $playstoreLogo);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = 'Logo Deleted Successfully!';
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

    public function deleteAppStoreLogo(Request $request)
    {
        $appleStoreLogo = $_POST['appleStoreLogo'];

        if (isset($appleStoreLogo))
        {
            $appStoreCredentialsForApple = AppStoreCredentials::where(['company' => $request->appstorecompany, 'logo' => $request->appleStoreLogo])->first();

            if ($appStoreCredentialsForApple)
            {
                AppStoreCredentials::where(['company' => $request->appstorecompany, 'logo' => $request->appleStoreLogo])->update(['logo' => null]);

                if ($appleStoreLogo != null)
                {
                    $dir = public_path('uploads/app-store-logos/' . $appleStoreLogo);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = 'Logo Deleted Successfully!';
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
}
