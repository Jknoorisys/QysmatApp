<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaticPagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('static_pages')->insert([
            [
                'page_name'  => 'about_us',
                'page_title' => 'About Us',
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
    ]);
    }
}
