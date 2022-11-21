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
        Schema::create('block_lists', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->default('');
            $table->enum('user_type', ['singleton','parent'])->default('singleton');
            $table->string('singleton_id')->default('');
            $table->string('blocked_user_id')->default('');
            $table->string('blocked_user_type')->default('');
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
        Schema::dropIfExists('block_lists');
    }
};
