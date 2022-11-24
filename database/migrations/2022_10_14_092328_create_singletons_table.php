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
        Schema::create('singletons', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->integer('parent_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile');
            $table->string('password');
            $table->string('email_otp');
            $table->enum('is_email_verified',['verified', 'not verified'])->default('not verified');
            $table->string('mobile_otp');
            $table->enum('is_mobile_verified',['verified', 'not verified'])->default('not verified');
            $table->string('photo1');
            $table->string('photo2');
            $table->string('photo3');
            $table->string('photo4');
            $table->string('photo5');
            $table->string('dob');
            $table->string('age');
            $table->enum('gender', ['Male','Female', 'Other']);
            $table->string('height');
            $table->string('profession');
            $table->string('nationality');
            $table->string('ethnic_origin');
            $table->string('islamic_sect');
            $table->text('short_intro');
            $table->string('location');
            $table->string('lat');
            $table->string('long');
            $table->string('live_photo');
            $table->string('id_proof');
            $table->string('active_subscription_id')->default('1');
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
     **
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('singletons');
    }
};
