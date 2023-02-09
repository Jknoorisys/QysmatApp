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
            $table->enum('other_user_type', ['singleton','parent'])->default('singleton');
            $table->enum('payment_method',['stripe','in-app'])->default('stripe');
            $table->string('stripe_subscription_id');
            $table->string('stripe_customer_id');
            $table->string('stripe_plan_id');
            $table->string('plan_amount');
            $table->string('plan_amount_currency');
            $table->string('plan_interval');
            $table->string('plan_interval_count');
            $table->integer('quantity');
            $table->string('amount_paid');
            $table->string('payer_email');
            $table->string('transaction_datetime');
            $table->string('sub_created');
            $table->string('plan_period_start');
            $table->string('plan_period_end');
            $table->string('session_status');
            $table->string('status');
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
