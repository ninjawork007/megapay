<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::truncate();

        Setting::insert([
            ['name' => 'name', 'value' => 'Pay Money', 'type' => 'general'],

            ['name' => 'logo', 'value' => '1532175849_logo.png', 'type' => 'general'],

            ['name' => 'favicon', 'value' => '1530689937_favicon.png', 'type' => 'general'],

            ['name' => 'head_code', 'value' => 'Pay Money', 'type' => 'general'],

            ['name' => 'default_currency', 'value' => 1, 'type' => 'general'],

            ['name' => 'default_language', 'value' => 1, 'type' => 'general'],

            ['name' => 'client_id', 'value' => 'id', 'type' => 'PayPal'],
            ['name' => 'client_secret', 'value' => 'secrect', 'type' => 'PayPal'],
            ['name' => 'signature', 'value' => 'sign', 'type' => 'PayPal'],
            ['name' => 'mode', 'value' => 'sandbox', 'type' => 'PayPal'],

            ['name' => 'publishable', 'value' => '', 'type' => 'Stripe'],
            ['name' => 'secret', 'value' => '', 'type' => 'Stripe'],

            ['name' => 'driver', 'value' => 'sendmail', 'type' => 'email'],
            ['name' => 'host', 'value' => '', 'type' => 'email'],
            ['name' => 'port', 'value' => '587', 'type' => 'email'],
            ['name' => 'from_address', 'value' => '', 'type' => 'email'],
            ['name' => 'from_name', 'value' => '', 'type' => 'email'],
            ['name' => 'encryption', 'value' => 'tls', 'type' => 'email'],
            ['name' => 'username', 'value' => '', 'type' => 'email'],
            ['name' => 'password', 'value' => '', 'type' => 'email'],

            ['name' => 'site_key', 'value' => '', 'type' => 'recaptcha'],
            ['name' => 'secret_key', 'value' => '', 'type' => 'recaptcha'],

            ['name' => 'seller_id', 'value' => '', 'type' => '2Checkout'],
            ['name' => 'mode', 'value' => '', 'type' => '2Checkout'],

            ['name' => 'mode', 'value' => '', 'type' => 'PayUmoney'],
            ['name' => 'key', 'value' => '', 'type' => 'PayUmoney'],
            ['name' => 'salt', 'value' => '', 'type' => 'PayUmoney'],

            ['name' => 'merchant_id', 'value' => '', 'type' => 'Coinpayments'],
            ['name' => 'private_key', 'value' => '', 'type' => 'Coinpayments'],
            ['name' => 'public_key', 'value' => '', 'type' => 'Coinpayments'],

            ['name' => 'default_timezone', 'value' => 'Asia/Dhaka', 'type' => 'general'],

            ['name' => 'has_captcha', 'value' => 'Disabled', 'type' => 'general'],

            //paymoney 1.3 below
            // Nexmo - start
            ['name' => 'Key', 'value' => '', 'type' => 'Nexmo'],
            ['name' => 'Secret', 'value' => '', 'type' => 'Nexmo'],
            ['name' => 'is_nexmo_default', 'value' => 'No', 'type' => 'Nexmo'],
            ['name' => 'nexmo_status', 'value' => 'Inactive', 'type' => 'Nexmo'],
            ['name' => 'default_nexmo_phone_number', 'value' => '', 'type' => 'Nexmo'],
            // Nexmo - end

            ['name' => 'login_via', 'value' => 'email_only', 'type' => 'general'],

        ]);
    }
}
