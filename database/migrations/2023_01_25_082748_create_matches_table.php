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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('user_type',['singleton','parent'])->default('singleton');
            $table->integer('match_id');
            $table->integer('matched_parent_id');
            $table->integer('singleton_id');
            $table->integer('matched_parent_id');
            $table->enum('match_type',['liked', 'matched', 'un-matched', 're-matched', 'rejected', 'hold'])->default('liked');
            $table->integer('queue');
            $table->enum('is_rematched',['no', 'yes'])->default('no');
            $table->enum('status',['available', 'busy'])->default('available');
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
        Schema::dropIfExists('matches');
    }
};
