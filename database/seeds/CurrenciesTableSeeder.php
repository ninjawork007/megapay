<?php

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Currency::truncate();
        Currency::insert([
            [
                'name'          => 'US Dollar',
                'symbol'        => '$',
                'code'          => 'USD',
                'hundreds_name' => 'one thousand',
                'rate'          => '0.05',
                'logo'          => 'icons8-us-dollar-64.png',
                'exchange_from' => 'local',
                'default'       => '1',
                'status'        => 'Active',
            ],
            [
                'name'          => 'Pound Sterling',
                'symbol'        => '£',
                'code'          => 'GBP',
                'hundreds_name' => 'one thousand',
                'rate'          => '0.75',
                'logo'          => 'icons8-british-pound-64.png',
                'exchange_from' => 'api',
                'default'       => '0',
                'status'        => 'Active',
            ],
            [
                'name'          => 'Europe',
                'symbol'        => '€',
                'code'          => 'EUR',
                'hundreds_name' => 'one thousand',
                'rate'          => '0.85',
                'logo'          => 'icons8-euro-64.png',
                'exchange_from' => 'local',
                'default'       => '0',
                'status'        => 'Active',
            ],
        ]);
    }
}
