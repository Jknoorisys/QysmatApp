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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['singleton','parent'])->default('parent');
            $table->string('name')->default('');
            $table->string('email')->unique();
            $table->string('mobile')->default('');
            $table->string('profile_pic')->default('');
            $table->string('password')->default('');
            $table->string('email_otp')->default('');
            $table->enum('is_email_verified',['verified', 'not verified'])->default('not verified');
            $table->string('mobile_otp')->default('');
            $table->enum('is_mobile_verified',['verified', 'not verified'])->default('not verified');
            $table->string('nationality')->default('');
            $table->string('ethnic_origin')->default('');
            $table->string('islamic_sect')->default('');
            $table->string('location')->default('');
            $table->string('lat')->default('');
            $table->string('long')->default('');
            $table->string('live_photo')->default('');
            $table->string('id_proof')->default('');
            $table->string('active_subscription_id')->default('1');
            $table->enum('is_social',['1','0'])->default('0');
            $table->enum('social_type',['google','facebook','apple',''])->default('');
            $table->string('social_id')->default('');
            $table->string('device_id')->default('');
            $table->enum('device_type',['android','ios',''])->default('');
            $table->string('fcm_token')->default('');
            $table->string('device_token')->default('');
            $table->enum('status',['Blocked','Unblocked', 'Deleted'])->default('Unblocked');
            $table->enum('is_verified',['verified', 'rejected',''])->default('');
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
        Schema::dropIfExists('parents');
    }
};
