<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Meta;
use App\Models\Pages;
use App\Models\Setting;
use Illuminate\Support\Facades\Input;

class ContentController extends Controller
{
    protected $data = [];

    public function pageDetail($url)
    {
        $data['menu'] = 'deposit';
        if ($url == 'send-money')
        {
            $data['pageInfo']  = 'Request Money';
            $data['exception'] = Meta::where('url', $url)->first();
            $data['menu']      = 'send-money';
            return view('frontend.pages.send-money', $data);

        }
        elseif ($url == 'request-money')
        {

            $data['pageInfo']  = 'Request Money';
            $data['exception'] = Meta::where('url', $url)->first();
            $data['menu']      = 'request-money';
            return view('frontend.pages.request-money', $data);

        }
        elseif ($url == 'developer')
        {
            $data['pageInfo']   = 'Developer';
            $data['exception']  = Meta::where('url', $url)->first();
            $data['menu']       = 'Developer';
            $type               = Input::get('type');
            $publication_status = Setting::where(['type' => 'envato', 'name' => 'publication_status'])->first(['value']);
            if (!empty($publication_status))
            {
                $data['publication_status'] = $publication_status->value;
            }

            if ($type == 'express')
            {
                return view('frontend.pages.express', $data);
            }
            elseif ($type == 'woocommerce')
            {
                $plugin_name = Setting::where(['type' => 'envato', 'name' => 'plugin_name'])->first(['value']);
                if (!empty($plugin_name))
                {
                    $data['plugin_name'] = $plugin_name->value;
                }
                return view('frontend.pages.woocommerce', $data);
            }
            else
            {
                return view('frontend.pages.standard', $data);
            }
        }
        else
        {
            $info = Pages::where(['url' => $url])->first();
            if (empty($info))
            {
                abort(404);
            }
            $data['pageInfo']  = $info;
            $data['exception'] = Meta::where('url', $url)->first();
            $data['menu']      = $url;
            return view('frontend.pages.detail', $data);
        }
    }

    public function downloadPackage()
    {
        return response()->download(\Storage::disk('local')->path('paymoney_sdk.zip'));
    }
}
