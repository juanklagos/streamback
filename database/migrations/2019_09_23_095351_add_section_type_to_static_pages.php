<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSectionTypeToStaticPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('unique_id')->after('id');
            $table->enum('section_type', [STATIC_PAGE_SECTION_1, STATIC_PAGE_SECTION_2, STATIC_PAGE_SECTION_3, STATIC_PAGE_SECTION_4])->default(STATIC_PAGE_SECTION_1)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('unique_id');
            $table->dropColumn('section_type');
        });
    }
}
