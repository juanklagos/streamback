<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V60ReferralRelatedMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->integer('user_id');
            $table->string('referral_code')->default("");
            $table->integer('total_referrals')->default(0);
            $table->float('referral_earnings')->default(0.00)->comment("Using the current user code, if someone joined means the current user will get this earnings");
            $table->float('referee_earnings')->default(0.00)->comment("if the current user joined using someother user referral code means the current user will get some earnings");
            $table->integer('status')->default(APPROVED);
            $table->timestamps();
        });

        Schema::create('user_referrals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->integer('user_id');
            $table->integer('parent_user_id');
            $table->integer('referral_code_id');
            $table->string('referral_code')->default("");
            $table->string('device_type')->default(DEVICE_WEB);           
            $table->integer('status')->default(APPROVED);
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
        Schema::drop('referral_codes');
        Schema::drop('user_referrals');
    }
}
