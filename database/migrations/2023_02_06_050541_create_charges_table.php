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
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->string('charge_id');
            $table->text('object');
            $table->string('charge_customer_id');
            $table->string('balance_transaction');
            $table->string('plan_amount');
            $table->string('payer_email');
            $table->string('plan_amount_currency');
            $table->string('charge_create');
            $table->string('charge_currency');
            $table->text('charge_description');
            $table->string('charge_invoice');
            $table->text('seller_message');
            $table->string('payment_intent');
            $table->string('payment_method');
            $table->string('paid_status');
            $table->string('charge_country');
            $table->string('exp_month');
            $table->string('exp_year');
            $table->string('funding');
            $table->string('last4');
            $table->string('network');
            $table->string('type');
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
        Schema::dropIfExists('charges');
    }
};
