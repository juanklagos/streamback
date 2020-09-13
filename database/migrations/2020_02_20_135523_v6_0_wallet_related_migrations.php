<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V60WalletRelatedMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_wallets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->float('total')->default(0.00);
            $table->float('used')->default(0.00);
            $table->float('remaining')->default(0.00);
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('custom_wallet_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('payment_id');
            $table->string('wallet_type')->default(CW_WALLET_TYPE_DIRECT);
            $table->integer('voucher_id')->default(0);
            $table->string('voucher_code')->default("");
            $table->float('actual_amount')->default(0.00);
            $table->float('paid_amount')->default(0.00);
            $table->string('payment_mode')->default('card');
            $table->integer('is_cancelled')->default(0);
            $table->string('cancel_reason')->default("");
            $table->dateTime('paid_date')->nullable();
            $table->integer('status')->default(0);
            $table->string('message')->default("");
            $table->timestamps();
        });

        Schema::create('custom_wallet_vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->string('name');
            $table->text('description')->default("");
            $table->string('voucher_code');
            $table->float('amount')->default(0.00);
            $table->integer('total_count')->defeult(0);
            $table->integer('per_user_limit')->defeult(0);
            $table->integer('used_count')->defeult(0);
            $table->integer('remaining_count')->defeult(0);
            $table->dateTime('expiry_date');
            $table->tinyInteger('status')->default(APPROVED);
            $table->timestamps();
        });

        Schema::create('custom_wallet_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->integer('user_id');
            $table->integer('custom_field_id')->default(0);
            $table->integer('custom_payment_id')->default(0);
            $table->string('payment_id');
            $table->string('message')->default("");
            $table->float('amount')->default(0);
            $table->string('history_type')->default(CW_ADD); // add, deduct
            $table->string('transaction_type')->default(CW_HISTORY_TYPE_NONE); // PPV, SUBSCRIPTION
            $table->string('reason')->default("");
            $table->dateTime('paid_date')->nullable();
            $table->string('payment_mode')->default("");
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::table('user_payments', function (Blueprint $table) {
            $table->tinyInteger('is_wallet_credits_applied')->default(0);
            $table->float('wallet_amount')->default(0.00);
        });

        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->tinyInteger('is_wallet_credits_applied')->default(0);
            $table->float('wallet_amount')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('custom_wallets');
        Schema::drop('custom_wallet_payments');
        Schema::drop('custom_wallet_vouchers');
        Schema::drop('custom_wallet_histories');
        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn('is_wallet_credits_applied');
            $table->dropColumn('wallet_amount');
        });

        Schema::table('pay_per_views', function (Blueprint $table) {
            $table->dropColumn('is_wallet_credits_applied');
            $table->dropColumn('wallet_amount');
        });
    }
}
