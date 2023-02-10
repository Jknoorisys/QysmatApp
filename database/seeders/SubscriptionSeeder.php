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
                'currency' => '£',
                'stripe_plan_id' => '',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Premium',
                'price' => 10.00,
                'currency' => '£',
                'stripe_plan_id' => 'price_1MYpphKnRvfDWQjItPjZuiFg',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Joint Subscription',
                'price' => 5.00,
                'currency' => '£',
                'stripe_plan_id' => 'price_1MYpr1KnRvfDWQjI2OqRA4Ak',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
