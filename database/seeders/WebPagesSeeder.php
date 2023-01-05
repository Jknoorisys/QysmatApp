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
                'page_name'  => 'social_links',
                'page_title' => 'Instagram',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'Facebook',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'WhatsApp',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'Twitter',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'LinkedIn',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'Telegram',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'social_links',
                'page_title' => 'Skype',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'download_links',
                'page_title' => 'Play Store',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'page_name'  => 'download_links',
                'page_title' => 'iOS App Store',
                'created_at' => date('Y-m-d H:i:s')
            ],
        ]);
    }
}
