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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id');
            $table->integer('user_id');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('user_name');
            $table->string('user_email');
            $table->string('other_user_id');
            $table->string('other_user_type');
            $table->enum('active_subscription_id',['1','2','3'])->default('1');
            $table->string('customer_id');
            $table->string('subscription_id');
            $table->string('subscription_item1_id');
            $table->string('subscription_item2_id');
            $table->string('item1_plan_id');
            $table->string('item2_plan_id');
            $table->string('item1_unit_amount');
            $table->string('item2_unit_amount');
            $table->integer('item1_quantity');
            $table->integer('item2_quantity');
            $table->string('currency');
            $table->string('plan_interval');
            $table->string('plan_interval_count');
            $table->string('amount_paid');
            $table->string('payer_email');
            $table->string('plan_period_start');
            $table->string('plan_period_end');
            $table->string('invoice_url');
            $table->string('payment_status');
            $table->string('subs_status');
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
        Schema::dropIfExists('transactions');
    }
};
