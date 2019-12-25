<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // if(env('DB_DATABASE') != '') {
        //     if(Schema::hasTable('currencies'))
        //         $this->currency();

        //     if(Schema::hasTable('languages'))
        //         $this->language();

        //     if(Schema::hasTable('settings')){
        //         $this->settings();
        //         $this->api_info_set();
        //     }
        //     if(Schema::hasTable('pages'))
        //         $this->pages();

        //     $this->creditcard_validation();
        // }
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }

    // public function currency()
    // {
    //     $currency = Currency::where('status', '=', 'Active')->pluck('code', 'code');
    //     View::share('currency', $currency);

    //     $ip     = getenv("REMOTE_ADDR");
    //     $result = unserialize(@file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));

    //     if ($result['geoplugin_currencyCode'])
    //     {
    //         $default_currency = Currency::where('status', '=', 'Active')->where('code', '=', $result['geoplugin_currencyCode'])->first();
    //         if (!@$default_currency)
    //         {
    //             $default_currency = Currency::where('status', '=', 'Active')->where('default', '=', '1')->first();
    //         }

    //     }
    //     else
    //     {
    //         $default_currency = Currency::where('status', '=', 'Active')->where('default', '=', '1')->first();
    //     }

    //     if (!@$default_currency)
    //     {
    //         $default_currency = Currency::where('status', '=', 'Active')->first();
    //     }

    //     if (isset($default_currency->code))
    //     {
    //         Session::put('currency', $default_currency->code);
    //         $symbol = Currency::code_to_symbol($default_currency->code);
    //         Session::put('symbol', $symbol);
    //     }
    //     View::share('default_currency', $default_currency);
    //     View::share('default_country', $result['geoplugin_countryCode']);
    // }
    // public function language()
    // {
    //     $language = Language::where('status', '=', 'Active')->pluck('name', 'short_name');
    //     View::share('language', $language);

    //     $default_language = Language::where('status', '=', 'Active')->where('default', '=', '1')->limit(1)->get();
    //     View::share('default_language', $default_language);
    //     if ($default_language->count() > 0)
    //     {
    //         Session::put('language', $default_language[0]->value);
    //         App::setLocale($default_language[0]->value);
    //     }
    // }
    public function settings()
    {
        // $settings = Settings::all();

        // if (isset($settings[0]))
        // {
        //     $general = Settings::where('type', 'general')->pluck('value', 'name')->toArray();
        //     $map     = Settings::where('type', 'googleMap')->pluck('value', 'name')->toArray();

        //     View::share('settings', $settings);

        //     $join_us = Settings::where('type', 'join_us')->get();
        //     View::share('join_us', $join_us);

        //     define('SITE_NAME', $general['name']);
        //     define('LOGO_URL', url('public/front/images/logos/' . $general['logo']));
        //     define('EMAIL_LOGO_URL', url('public/front/images/logos/' . $general['email_logo']));

        //     View::share('site_name', $general['name']);
        //     View::share('head_code', $general['head_code']);
        //     View::share('logo', url('public/front/images/logos/' . $general['logo']));
        //     View::share('favicon', url('public/front/images/logos/' . $general['favicon']));
        //     if (isset($settings[26]->value))
        //     {
        //         \View::share('map_key', $map['key']);
        //         define('MAP_KEY', $map['key']);
        //     }
        //     Config::set('site_name', $general['name']);
        // }
    }
}
