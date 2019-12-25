<?php

use Illuminate\Database\Seeder;

class SocialsTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('socials')->truncate();
        \DB::table('socials')->insert([
        	[
        		'id'           => 1,
                'name'         => 'facebook',
                'icon' => '<i class="ti-facebook" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 2,
                'name'         => 'google_plus',
                'icon' => '<i class="ti-google plus" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 3,
                'name'         => 'twitter',
                'icon' => '<i class="ti-twitter" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 4,
                'name'         => 'linkedin',
                'icon' => '<i class="ti-linkedin" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 5,
                'name'         => 'pinterest',
                'icon' => '<i class="ti-pinterest" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 6,
                'name'         => 'youtube',
                'icon' => '<i class="ti-youtube" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        	[
        		'id'           => 7,
                'name'         => 'instagram',
                'icon' => '<i class="ti-instagram" aria-hidden="true"></i>',
                'url'  => '#',
        	],
        ]);
    }
}
