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
        Schema::create('re_verify_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('name');
            $table->string('lname');
            $table->string('email')->unique();
            $table->string('mobile');
            $table->string('photo1');
            $table->string('photo2');
            $table->string('photo3');
            $table->string('photo4');
            $table->string('photo5');
            $table->string('profile_pic');
            $table->string('relation_with_singleton');
            $table->string('dob');
            $table->string('age');
            $table->enum('gender', ['Male','Female', 'Other']);
            $table->enum('marital_status', ['none','Never Married','Divorced', 'Widowed'])->default('none');
            $table->string('height');
            $table->string('profession');
            $table->string('nationality');
            $table->string('country_code');
            $table->string('nationality_code');
            $table->string('ethnic_origin');
            $table->string('islamic_sect');
            $table->text('short_intro');
            $table->string('location');
            $table->string('lat');
            $table->string('long');
            $table->string('live_photo');
            $table->string('id_proof');
            $table->enum('status',['pending','verified','rejected'])->default('pending');
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
        Schema::dropIfExists('re_verify_requests');
    }
};
