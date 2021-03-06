<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableInVideos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            DB::statement('ALTER TABLE `admin_videos` CHANGE `uploaded_by` `uploaded_by` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_videos', function (Blueprint $table) {
            //
        });
    }
}
