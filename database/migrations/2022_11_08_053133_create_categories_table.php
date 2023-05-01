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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('singleton_id');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->enum('gender', ['Male','Female', 'Other']);
            $table->string('age_range');
            $table->string('profession');
            $table->string('location');
            $table->string('lat');
            $table->string('long');
            $table->enum('search_by',['radius','country', 'none'])->default('none');
            $table->string('radius');
            $table->string('country_code');
            $table->string('height');
            $table->string('islamic_sect');
            $table->enum('status',['Active','Inactive', 'Deleted'])->default('Active');
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
        Schema::dropIfExists('categories');
    }
};
