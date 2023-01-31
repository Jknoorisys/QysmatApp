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
        Schema::create('instant_match_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('user_type',['singleton','parent'])->default('singleton');
            $table->integer('requested_id');
            $table->integer('singleton_id');
            // $table->integer('requested_parent_id');
            $table->enum('request_type',['pending', 'un-matched', 'matched', 'rejected'])->default('pending');
            $table->enum('status',['active', 'inactive'])->default('active');
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
        Schema::dropIfExists('instant_match_requests');
    }
};
