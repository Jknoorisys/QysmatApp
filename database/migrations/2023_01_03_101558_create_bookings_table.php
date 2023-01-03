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
            $table->integer('user_id');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->integer('plan_id');
            $table->string('price');
            $table->string('transaction_id');
            $table->enum('payment_type',['stripe','in-app']);
            $table->integer('other_user_id');
            $table->enum('other_user_type', ['singleton','parent'])->default('singleton');
            $table->string('currency_code');
            $table->enum('status',['pending','inprogress','completed'])->default('pending');
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
