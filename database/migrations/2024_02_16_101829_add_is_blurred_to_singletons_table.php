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
        if (!Schema::hasColumn('singletons', 'is_blurred')) {
            Schema::table('singletons', function (Blueprint $table) {
                $table->enum('is_blurred', ['NA', 'yes', 'no'])->default('NA')->after('is_verified');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('singletons', function (Blueprint $table) {
            $table->dropColumn('is_blurred');
        });
    }
};
