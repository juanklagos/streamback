<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
	/**
     * Scope a query to only include active users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCommonResponse($query) {

        $currency = \Setting::get('currency' , '$');

        return $query->leftJoin('custom_wallets','custom_wallets.user_id' ,'=' , 'referral_codes.user_id')
            ->select(
            'referral_codes.id as referral_code_id',
            'referral_code','total_referrals','referral_earnings','referee_earnings',
            \DB::raw('IFNULL(custom_wallets.total,0) as amount_total'),
            \DB::raw('IFNULL(custom_wallets.used,0) as amount_used'),
            \DB::raw('IFNULL(custom_wallets.remaining,0) as remaining')
        );
    
    }
}
