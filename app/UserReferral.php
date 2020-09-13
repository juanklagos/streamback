<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReferral extends Model
{
    public function userDetails() {

        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Scope a query to only include active users.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function scopeCommonResponse($query) {

        return $query->leftJoin('users', 'users.id', '=', 'user_referrals.user_id')
        	->select(
	            'user_referrals.id as user_referral_id' ,
	            'user_referrals.user_id as user_id' ,
	            'user_referrals.parent_user_id as parent_user_id' ,
	            'user_referrals.referral_code_id as referral_code_id' ,
	            'user_referrals.referral_code as referral_code' ,
	            'user_referrals.device_type',  
	            \DB::raw('IFNULL(users.name,"") as user_name'),
                \DB::raw('IFNULL(users.picture,"") as user_picture'), 
	            'user_referrals.created_at',
				'user_referrals.updated_at'
	        );
    
    }
}
