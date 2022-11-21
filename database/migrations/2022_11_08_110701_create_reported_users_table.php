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
        Schema::create('reported_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->default('');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('singleton_id')->default('');
            $table->string('reported_user_id')->default('');
            $table->enum('reported_user_type', ['singleton','parent'])->default('singleton');
            $table->string('reported_user_name')->default('');
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
        Schema::dropIfExists('reported_users');
    }
};
