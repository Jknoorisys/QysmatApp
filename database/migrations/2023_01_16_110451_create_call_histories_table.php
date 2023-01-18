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
        Schema::create('call_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('caller_id');
            $table->enum('caller_type',['singleton','parent'])->default('singleton');
            $table->integer('receiver_id');
            $table->enum('receiver_type',['singleton','parent'])->default('singleton');
            $table->enum('call_type',['audio', 'video'])->default('audio');
            $table->enum('status',['incoming', 'accepted', 'rejected'])->default('incoming');
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
        Schema::dropIfExists('call_histories');
    }
};
