<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_versions')->insert([
            [
                'version' => '1.0.0',
                'platform' => 'android',
                'forcefully_update' => 'no',
                'url' => 'https://play.google.com/store/apps/details?id=com.app.qysmat',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'version' => '1.0.0',
                'platform' => 'ios',
                'forcefully_update' => 'no',
                'url' => 'https://apps.apple.com/gb/app/qysmat-the-new-way-to-marry/id6445908697',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
