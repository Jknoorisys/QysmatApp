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
            $table->integer('other_user_id');
            $table->enum('other_user_type', ['singleton','parent'])->default('singleton');
            $table->string('paid_by');
            $table->string('paid_amount');
            $table->string('currency_code');
            $table->enum('payment_type',['stripe','in-app']);
            $table->string('transaction_id');
            $table->string('subscription_type');
            $table->string('transaction_datetime');
            $table->enum('payment_status',['pending','inprogress','completed'])->default('pending');
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
