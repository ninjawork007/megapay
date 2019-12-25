<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Session;

class HomeController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function index()
    {
        // dd(session()->all());
        $data         = [];
        $data['menu'] = 'home';
        return view('frontend.home.index', $data);
    }

    public function setLocalization(Request $request)
    {
        if (!in_array($request->lang, ['ru', 'en', 'ar', 'fr', 'pt', 'fr', 'es', 'tr', 'ch']))
        {
            return 0;
        }
        if (!$request->ajax())
        {
            return 0;
        }

        if ($request->lang)
        {
            App::setLocale($request->lang);
            Session::put('dflt_lang', $request->lang);
            return 1;
        }
        else
        {
            return 0;
        }
    }
}
