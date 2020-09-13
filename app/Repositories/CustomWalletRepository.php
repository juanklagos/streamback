<?php

/**
 * Used to manage wallet releated features
 *
 * @author vithya R
 * 
 */

/*
|--------------------------------------------------------------------------
| Columns defination
|--------------------------------------------------------------------------
|
| Table: custom_wallet_payments
| 
| 	* wallet_type - direct amount or voucher code (CW_WALLET_TYPE_DIRECT or CW_WALLET_TYPE_VOUCHER)
| 
| Table: custom_wallet_histories
| 
| 	* history_type - PPV | SUBSCRIPTION | WALLET (used to identify history from ppv, subs or wallet)
|
|	* transaction_type - ADD | DEDUCT (used to identify the record is added or deducted the money)
|
|
*/

namespace App\Repositories;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Validator, Log, Setting, DB, Exception;

use App\PayPerView;

use App\CustomWallet;

use App\CustomWalletPayment;

use App\CustomWalletHistory;

use App\CustomWalletVoucher;

class CustomWalletRepository {

 	/**
	 *
	 * @method custom_wallet_payment_save
	 *
	 * @uses save the wallet details
	 *
	 * @created Vidhya R
	 *
	 * @updated Vidhya R
	 *
	 * @param 
	 *
	 * @return
	 */

 	public static function custom_wallet_payment_save($request) {

 		// Check the record exists

 		$wallet_payment_details = new CustomWalletPayment;

 		if($request->custom_wallet_payment_id) {

 			$wallet_payment_details = CustomWalletPayment::find($request->custom_wallet_payment_id);

 		}
     
        $wallet_payment_details->user_id = $request->id;

        $wallet_payment_details->payment_id  = $request->payment_id ?: 'CW-'.uniqid();

        $wallet_payment_details->voucher_id = $request->voucher_id ?: 0;

        $wallet_payment_details->voucher_code = $request->voucher_code ?: "";

        $wallet_payment_details->status = PAID_STATUS;
                                
        $wallet_payment_details->paid_date = date('Y-m-d');

        $wallet_payment_details->payment_mode = $request->payment_mode;

        $wallet_payment_details->wallet_type = $request->wallet_type;

        $wallet_payment_details->actual_amount = $wallet_payment_details->paid_amount = $request->total;

        $wallet_payment_details->message = Helper::get_message(10001);

        if($wallet_payment_details->save()) {

        	// Update wallet history 

            $message = "Added to Wallet";

        	$history_data = ['custom_field_id' => 0, 'custom_payment_id' => $wallet_payment_details->id, 'history_type' => CW_HISTORY_TYPE_WALLET, 'transaction_type' => CW_ADD, 'message' => $message];

        	$request->request->add($history_data);

        	self::custom_wallet_history_save($request);

        	// Update user wallet 

        	self::custom_wallet_update($request->id, $wallet_payment_details->paid_amount);

        	// Update the voucher used count, if the payment using voucher

        	if($request->wallet_type == CW_WALLET_TYPE_VOUCHER) {

        		self::wallet_voucher_update($request->voucher_id);

        	}

        	$data = ['payment_id' => $wallet_payment_details->payment_id, 'paid_amount' => $wallet_payment_details->paid_amount, 'payment_mode' => $wallet_payment_details->payment_mode, 'paid_amount_formatted' => Setting::get('currency', '$')." ".$wallet_payment_details->paid_amount];

        	$response_array = ['success' => true , 'message' => Helper::get_message(10001), 'data' => $data];

        } else {
        	
        	$response_array = ['success' => true , 'error_messages' => get_error_message(10001), 'error_code' => 10001];

        }

        return $response_array;

 	}

 	/**
	 *
	 * @method custom_wallet_history_save
	 *
	 * @uses save the wallet details
	 *
	 * @created Vidhya R
	 *
	 * @updated Vidhya R
	 *
	 * @param 
	 *
	 * @return
	 */

 	public static function custom_wallet_history_save($request) {

        try {

            DB::beginTransaction();

     		// Check the record exists

     		$history_details = new CustomWalletHistory;
         
            $history_details->user_id = $request->id;

            $history_details->custom_field_id = $request->custom_field_id ?: 0;
            
            $history_details->custom_payment_id = $request->custom_payment_id ?? 0;

            $history_details->payment_id = $request->payment_id ?? 0;

            $history_details->amount = $request->wallet_amount;

            $history_details->history_type = $request->history_type;

            $history_details->transaction_type = $request->transaction_type;

            $history_details->payment_mode = $request->payment_mode;

            $history_details->paid_date = date('Y-m-d H:i:s');

            $history_details->reason = $request->reason ?? "";

            $history_details->status = PAID_STATUS;

            $history_details->message = $request->message ?: "";  
            
            if($history_details->save()) {

                $custom_wallet_details = CustomWallet::where('user_id', $request->id)->first();
                
                if($custom_wallet_details) {

                    if($request->transaction_type == CW_DEDUCT) {

                        $custom_wallet_details->used += $request->wallet_amount;

                        $custom_wallet_details->remaining -= $request->wallet_amount;

                    } else {

                        // $custom_wallet_details->total += $request->total;

                        // $custom_wallet_details->remaining += $request->total;

                    }

                    $custom_wallet_details->save();

                }

            }

            DB::commit();

            return true; 

        } catch(Exception $e) {

            DB::rollback();

            $response = ['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response, 200);

        } 
 	
 	}

	/**
     * @method custom_wallet_update()
     * 
     * @uses Update the wallet money
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return boolean
     *
     */

    public static function custom_wallet_update($user_id, $paid_amount) {

        try {

            DB::beginTransaction();

            $custom_wallet_details = CustomWallet::where('user_id', $user_id)->first();

            if(!$custom_wallet_details) {

                $custom_wallet_details = new CustomWallet;

                $custom_wallet_details->user_id = $user_id;
            }

            $custom_wallet_details->total += $paid_amount;

            $custom_wallet_details->remaining += $paid_amount;

            $custom_wallet_details->save();

            DB::commit();

            return true; 

        } catch(Exception $e) {

            DB::rollback();

            $response = ['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response, 200);

        }
    
    }


    /**
     * @method wallet_voucher_update()
     * 
     * @uses Update the wallet money
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return boolean
     *
     */

    public static function wallet_voucher_update($voucher_id) {

    	if($voucher_details = CustomWalletVoucher::find($voucher_id)) {

    		$voucher_details->used_count +=  1;

	    	$voucher_details->remaining_count -= 1;

	    	$voucher_details->save();

	    	return true;

	    } else {

        	return false;
        }
    
    }

    /**
     * @method check_user_wallet()
     * 
     * @uses Update the wallet money
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return boolean
     *
     */

    public static function check_user_wallet($user_id, $total) {

    	$allow_subscription_payment = YES;

        if($total > 0) {

            $allow_subscription_payment = NO;

        	// Check the wallet is not empty
        	if($custom_wallet_details = CustomWallet::where('user_id', $user_id)->first()) {

        		if($custom_wallet_details->remaining < $total) {

        			$allow_subscription_payment = NO;
        		}

        	}
        
        }

        return $allow_subscription_payment;
    
    }

    /**
     * @method custom_wallet_history()
     * 
     * @uses Update the wallet money
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return boolean
     *
     */

    public static function custom_wallet_history($user_id, $skip = 0, $take = 0) {

        $skip = $skip ?: 0;

        $take = $take ?: Setting::get('admin_take_count', 6);

        $base_query = CustomWalletHistory::orderBy('custom_wallet_histories.updated_at', 'desc');

        if($user_id) {
            $base_query = $base_query->where('custom_wallet_histories.user_id', $user_id);
        }

        $histories = $base_query->skip($skip)->take($take)->get();

        $data = [];

        foreach ($histories as $key => $history_details) {

            $history_data = new \stdClass;

            $history_data->history_id = $history_details->id;

            $history_data->payment_id = $history_details->payment_id;

            $history_data->title = $history_details->message;

            $history_data->currency = Setting::get('currency', '$');

            $history_data->amount = $history_details->amount;

            $history_data->paid_date = date('d M Y', strtotime($history_details->paid_date));

            $history_data->transaction_type = $history_details->transaction_type;

            array_push($data, $history_data);

        }

        return $data;
    
    }

    /**
     * @method wallet_credits()
     * 
     * @uses Check wallet amount applied for the user
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return boolean
     *
     */

    public static function wallet_credits($wallet_details, $total = 0.00) {

        try {

            $is_wallet_applied = WALLET_CREDITS_NOT_APPLIED;

            $wallet_amount = $save_total = 0.00;

            if($wallet_details->remaining > 0 && $total > 0) {

                $user_wallet_amount = $wallet_details->remaining;

                $pay_amount = $total;

                $is_wallet_applied = WALLET_CREDITS_APPLIED;

                if($user_wallet_amount >= $pay_amount) {

                    $wallet_details->remaining = $user_wallet_amount - $pay_amount;

                    $save_total = (($user_wallet_amount - $pay_amount) - $wallet_details->remaining);

                    $wallet_amount = $pay_amount;

                } else if($user_wallet_amount < $pay_amount) {

                    $save_total = $pay_amount - $user_wallet_amount;

                    $wallet_details->remaining = (($user_wallet_amount + $save_total) - $pay_amount);

                    $wallet_amount = $user_wallet_amount;
                }

            }

            $data = ['wallet_amount' => $wallet_amount, 'save_total' => $save_total, 'is_wallet_applied' => $is_wallet_applied];
            
            return (object)$data;

        } catch(Exception $e) {

            $response = ['success' => false, 'error' => $e->getMessage(), 'error_code' => $e->getCode()];

            return response()->json($response, 200);

        }

    }


}