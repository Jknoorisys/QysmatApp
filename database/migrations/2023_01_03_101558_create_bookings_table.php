<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->longText('stripe_session_id');
            $table->integer('user_id');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('other_user_id');
            $table->string('other_user_type');
            $table->enum('active_subscription_id',['1','2','3'])->default('1');
            $table->string('subscription_id');
            // $table->string('subscription_item_id');
            $table->string('customer_id');
            // $table->string('plan_id');
            // $table->string('unit_amount');
            $table->string('currency');
            // $table->string('plan_interval');
            // $table->string('plan_interval_count');
            // $table->integer('quantity');
            $table->string('amount_paid');
            // $table->string('payer_email');
            // $table->string('plan_period_start');
            // $table->string('plan_period_end');
            $table->string('session_status');
            $table->string('payment_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
