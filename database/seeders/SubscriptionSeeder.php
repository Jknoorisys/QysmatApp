<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                'price' => 'Free',
                'currency' => '',
                'details' => '["Only 5 Profile Views per day", "Unrestricted profile search criteria"]',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Premium',
                'price' => '10',
                'currency' => 'Pound',
                'details' => '["Unlimited swipes per day", "Send instant message  (3 per week)", "In-app telephone and video calls", "Refer profiles to friends and family", "Undo last swipe", "Reset profile search and start again once a month"]',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'subscription_type' => 'Joint Subscription',
                'price' => '15',
                'currency' => 'Pound',
                'details' => '["Unlimited swipes per day", "Send instant message  (3 per week)", "In-app telephone and video calls", "Refer profiles to friends and family", "Undo last swipe", "Reset profile search and start again once a month"]',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
