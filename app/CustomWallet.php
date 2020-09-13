<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomWallet extends Model
{	

	// Get User wallet Details
    public function userWalletHistory()
    {
        return $this->hasMany(CustomWalletHistory::class, 'user_id');
    }

    public static function boot() {

        parent::boot();

	    static::deleting(function ($model) {

	    	$user->userWalletHistory()->delete();
	    });

	}
}
