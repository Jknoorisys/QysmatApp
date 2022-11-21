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
            $table->string('booking_id')->default('');
            $table->string('user_id')->default('');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('user_name')->default('');
            $table->string('paid_by')->default('');
            $table->string('paid_amount')->default('');
            $table->string('currency_code')->default('');
            $table->string('subscription_type')->default('');
            $table->string('transaction_datetime')->default('');
            $table->string('payment_status')->default('');
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
