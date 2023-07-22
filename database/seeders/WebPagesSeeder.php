<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WebPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('web_pages')->insert([
            [
                'page_name'  => 'about_us',
                'page_title' => 'Company overview',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'privacy_policy',
                'page_title' => 'Privacy Policy',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'terms_and_conditions',
                'page_title' => 'Terms And Conditions',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'cookies',
                'page_title' => 'Cookies',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'reviews',
                'page_title' => 'Clients Reviews',
                'created_at' => date('Y-m-d H:i:s')
            ],
        ]);
    }
}
