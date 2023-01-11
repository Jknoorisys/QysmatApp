<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Stripe\Plan;
use Stripe\Stripe;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subscriptions')->insert([
            [
                'subscription_type' => 'Basic',
                'price' => 0.00,
                'currency' => '',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Premium',
                'price' => 10.00,
                'currency' => '',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Joint Subscription',
                'price' => 15.00,
                'currency' => '',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
