<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('contact_details')->insert([
            [
                'contact_type'  => 'email',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'phone',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'address',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'instagram',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'facebook',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'twitter',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'contact_type'  => 'linkedin',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
