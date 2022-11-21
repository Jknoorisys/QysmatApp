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
            $table->string('singleton_id')->default('');
            $table->enum('gender', ['Male','Female', 'Other',''])->default('');
            $table->string('age_range')->default('');
            $table->string('profession')->default('');
            $table->string('location')->default('');
            $table->string('height')->default('');
            $table->string('islamic_sect')->default('');
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
