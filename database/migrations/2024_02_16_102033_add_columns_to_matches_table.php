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
        if (!Schema::hasColumn('matches', 'blur_image')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->enum('blur_image', ['yes', 'no'])->default('yes')->after('is_reset');
            });
        }

        // Add the matched_at column
        if (!Schema::hasColumn('matches', 'matched_at')) {
            Schema::table('matches', function (Blueprint $table) {
                $table->dateTime('matched_at')->nullable()->after('status');
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
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('blur_image');
            $table->dropColumn('matched_at');
        });
    }
};
