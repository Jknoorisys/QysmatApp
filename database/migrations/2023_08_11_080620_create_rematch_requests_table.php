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
        Schema::create('rematch_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->enum('user_type',['singleton','parent'])->default('singleton');
            $table->integer('matched_table_id');
            $table->integer('match_id');
            $table->integer('singleton_id');
            $table->integer('matched_parent_id');
            $table->enum('is_rematched',['no', 'yes'])->default('no');
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
        Schema::dropIfExists('rematch_requests');
    }
};
