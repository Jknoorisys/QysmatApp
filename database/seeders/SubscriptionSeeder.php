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
                'stripe_plan_id' => 'plan_N8VAoxvTkulA4W',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Premium',
                'price' => 10.00,
                'currency' => '',
                'stripe_plan_id' => 'plan_N8VBy9uaXw2W6v',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Joint Subscription',
                'price' => 15.00,
                'currency' => '',
                'stripe_plan_id' => 'plan_N8VB9tBMOsjb1a',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
