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
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile');
            $table->string('profile_pic');
            $table->string('password');
            $table->string('relation_with_singleton');
            $table->string('email_otp');
            $table->enum('is_email_verified',['verified', 'not verified'])->default('not verified');
            $table->string('nationality');
            $table->string('ethnic_origin');
            $table->string('islamic_sect');
            $table->string('location');
            $table->string('lat');
            $table->string('long');
            $table->string('live_photo');
            $table->string('id_proof');
            $table->string('active_subscription_id')->default('1');
            $table->string('stripe_id');
            $table->string('stripe_plan_id');
            $table->enum('is_social',['1','0'])->default('0');
            $table->enum('social_type',['google','facebook','apple']);
            $table->string('social_id');
            $table->string('device_id');
            $table->enum('device_type',['android','ios']);
            $table->string('fcm_token');
            $table->string('device_token');
            $table->enum('status',['Blocked','Unblocked', 'Deleted'])->default('Unblocked');
            $table->enum('is_verified',['verified', 'rejected'])->default('rejected');
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
