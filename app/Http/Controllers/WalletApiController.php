<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use App\Repositories\PaymentRepository as PaymentRepo;

use App\Repositories\CustomWalletRepository as CustomWalletRepo;

use Log, File, DB, Setting, Validator, Exception;

use App\User, App\UserCoupon, App\SubProfile;

use App\Card, App\Subscription, App\UserPayment;

use App\AdminVideo, App\PayPerView, App\Coupon;

use App\CustomWallet, App\CustomWalletPayment, App\CustomWalletVoucher, App\CustomWalletHistory;

class WalletApiController extends Controller
{
    public function __construct(Request $request) {

        $this->middleware('UserApiVal' , ['except' => ['voucher_code_check']]);

    }

    // Not using in the version 5.0. Will be adding this feature in upcoming versions

    /**
     * @method custom_wallet_index()
     * 
     * @uses Wallet details
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return json response
     *
     */

    public function custom_wallet_index(Request $request) {

    	try {

            $data = [];

            // In apps, to avoid sending the wallet details while using the skip and take in the history.

            if($request->skip <= 0) {

        		$custom_wallet_details = CustomWallet::where('user_id', $request->id)
        									->select('total', 'used', 'remaining')
        									->first();


        		if(!$custom_wallet_details) {

        			$custom_wallet_details = new \stdClass;

                    $custom_wallet_details->user_id = $request->id;

        			$custom_wallet_details->total = $custom_wallet_details->used = $custom_wallet_details->remaining = 0.00;
        		}

                $custom_wallet_details->total = $custom_wallet_details->total;

                $custom_wallet_details->used = $custom_wallet_details->used;

                $custom_wallet_details->remaining = $custom_wallet_details->remaining;

        		$custom_wallet_details->currency = Setting::get('currency', '$');

        		$data['wallet'] = $custom_wallet_details ?: [];
            }

            $histories = CustomWalletRepo::custom_wallet_history($request->id, $request->skip);

    		$data['payments'] = $histories;

            return $this->sendResponse($message = "", $code = 200, $data);

    	} catch(Exception $e) {

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }

    /**
     * @method custom_wallet_add_money_via_paypal()
     * 
     * @uses Add money to wallet
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return json response
     *
     */

    public function custom_wallet_add_money_via_paypal(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(),
                [
                    'amount' => 'required|numeric',
                    'payment_id' => 'required',
                ]);

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages);

            } else {

                $user_details = User::find($request->id);

                if(!$user_details) {

                	throw new Exception(Helper::get_error_message(133), 133);
                	
                }

                // To update payment record (using common fn)

                $form_data = ['total' => $request->amount, 'payment_mode' => PAYPAL, 'wallet_type' => CW_WALLET_TYPE_DIRECT];

                $request->request->add($form_data);

                $total = $request->amount;

                $response_array = CustomWalletRepo::custom_wallet_payment_save($request);


                // check the response contains error, if yes throw exception

                if($response_array['success'] == false) {

                    throw new Exception($response_array['error_messages'], $response_array['error_code']);
                    
                }

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());
        
        }

    }

    /**
     * @method custom_wallet_add_money_via_stripe()
     * 
     * @uses Add money to wallet
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return json response
     *
     */

    public function custom_wallet_add_money_via_stripe(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(),
                array(
                    'amount' => 'required',
                    // 'coupon_code' => 'exists:coupons,coupon_code',
                ),  array(
                    // 'coupon_code.exists' => tr('coupon_code_not_exists'),
                ));

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages);

            } else {

                $stripe_secret_key = Setting::get('stripe_secret_key');

                if(!$stripe_secret_key) {
                    
                    throw new Exception(Helper::get_error_message(902), 902);

                }

                $user_details = User::find($request->id);

                if (!$user_details) {

                    throw new Exception(tr('no_user_detail_found') , 101);

                }

                $check_card_exists = User::where('users.id' , $request->id)
                                ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                ->where('cards.id' , $user_details->card_id)
                                ->where('cards.is_default' , DEFAULT_TRUE);

                if($check_card_exists->count() == 0) {
                   
                    throw new Exception(Helper::get_error_message(901), 901);

                }

                $user_card_details = $check_card_exists->first();

                $customer_id = $user_card_details->customer_id;

                \Stripe\Stripe::setApiKey($stripe_secret_key);

                $total = $request->amount;

                try {

                	$payment_data = ["amount" => $total * 100, "currency" => "usd", "customer" => $customer_id];

                   	$stripe_payment_charge =  \Stripe\Charge::create($payment_data);

                   	$payment_id = $stripe_payment_charge->id;

                   	$amount = $stripe_payment_charge->amount/100;

                   	$paid_status = $stripe_payment_charge->paid;

                    if($paid_status) {

		                // To update payment record (using common fn)

                        $form_data = ['total' => $request->amount, 'payment_mode' => CARD, 'wallet_type' => CW_WALLET_TYPE_DIRECT, 'payment_id' => $payment_id];

                        $request->request->add($form_data);

                        $total = $request->amount;

                        $response_array = CustomWalletRepo::custom_wallet_payment_save($request);


                        // check the response contains error, if yes throw exception

                        if($response_array['success'] == false) {

                            throw new Exception($response_array['error_messages'], $response_array['error_code']);
                            
                        }

                    } else {

                        throw new Exception(Helper::get_error_message(903), 903);

                    }

                } catch(\Stripe\Error\RateLimit | \Stripe\Error\Card | \Stripe\Error\InvalidRequest | \Stripe\Error\Authentication | \Stripe\Error\ApiConnection | \Stripe\Error\Base $e) {

                    return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());


                } catch (Exception $e) {

                    // Something else happened, completely unrelated to Stripe

                    return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

                }

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }


    /**
     * @method custom_wallet_add_money_via_voucher()
     * 
     * @uses Add money to wallet
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return json response
     *
     */

    public function custom_wallet_add_money_via_voucher(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(),
                array(
                    'voucher_code' => 'required',
                    // 'payment_id' => 'required',
                    // 'coupon_code' => 'exists:coupons,coupon_code',
                ),  array(
                    // 'coupon_code.exists' => tr('coupon_code_not_exists'),
                ));

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages);

            } else {

                $user_details = User::find($request->id);

                if(!$user_details) {

                	throw new Exception(tr('no_user_detail_found'), 101);
                	
                }
                // Check the voucher code available

                // $voucher_code = str_replace("-", "", $request->voucher_code);

                $voucher_code = $request->voucher_code;

                $voucher_details = CustomWalletVoucher::where('voucher_code' , $voucher_code)
                						->where('status', YES)
                						->where('remaining_count' , '>', 0)
                						->first();

                if(!$voucher_details) {

                	throw new Exception(Helper::get_error_message(10002), 10002);
                }

                $current_date = date('Y-m-d');

                if((date('Y-m-d' , strtotime($voucher_details->expiry_date))) < $current_date) {

                	throw new Exception(Helper::get_error_message(10003), 10003);
                	
                }

                $check_voucher_details = CustomWalletPayment::where('voucher_code', $request->voucher_code)->where('user_id', $request->id)->where('status', PAID_STATUS)->count();

                if($check_voucher_details) {

                	// throw new Exception(Helper::get_error_message(10004), 10004);

                }

                $total = $voucher_details->amount;

                // To update payment record (using common fn)

                $form_data = ['total' => $voucher_details->amount, 'payment_mode' => VOUCHER, 'wallet_type' => CW_WALLET_TYPE_VOUCHER, 'voucher_id' => $voucher_details->id, 'voucher_code' => $request->voucher_code];

                $form_data['payment_id'] = VOUCHER.'-'.$request->voucher_code.'-'.uniqid();

                $request->request->add($form_data);

                $total = $request->amount;

                $response_array = CustomWalletRepo::custom_wallet_payment_save($request);


                // check the response contains error, if yes throw exception

                if($response_array['success'] == false) {

                    throw new Exception($response_array['error_messages'], $response_array['error_code']);
                    
                }
    

            }

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }


    /**
     * @method ppv_pay_via_wallet()
     * 
     * @uses Pay the payment for Pay per view through wallet
     * 
     * @created vidhya R
     *
     * @updated
     *
     * @usage
     *
     * @param object $request - Admin video id
     * 
     * @return response of success/failure message
     */
    public function ppv_pay_via_wallet(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make($request->all(),
                [
                    'admin_video_id' => 'required|integer|exists:admin_videos,id',
                    'coupon_code'=>'exists:coupons,coupon_code',
                    // 'payment_id' => 'required_if:payment_mode,'.PAYPAL
                ],
                [
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'admin_video_id.exists' => Helper::get_error_message(157),
                ]
            );

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages , 101);

            }

            $admin_video_details = AdminVideo::where('admin_videos.id', $request->admin_video_id)
                                        ->where('status', VIDEO_PUBLISHED)
                                        ->where('is_approved', VIDEO_APPROVED)
                                        ->first();

            if(!$admin_video_details) {

                throw new Exception(Helper::get_error_message(157), 157);
                
            }

            if($admin_video_details->is_pay_per_view == PPV_DISABLED) {

                throw new Exception(Helper::get_error_message(171), 171);
                
            }

            $user_details = User::find($request->id);

            if(!$user_details) {

                throw new Exception(Helper::get_error_message(154), 154);
            }

            // Initial detault values

            $total = $admin_video_details->amount; 

            $coupon_amount = 0.00; $coupon_reason = ""; $is_coupon_applied = COUPON_NOT_APPLIED;

            // Check the coupon code

            if($request->coupon_code) {
                
                $coupon_code_response = PaymentRepo::check_coupon_code($request, $user_details, $admin_video_details->amount);

                $coupon_amount = $coupon_code_response['coupon_amount'];

                $coupon_reason = $coupon_code_response['coupon_reason'];

                $is_coupon_applied = $coupon_code_response['is_coupon_applied'];

                $total = $coupon_code_response['total'];

            }

            // Update the coupon details and total to the request

            $request->coupon_amount = $coupon_amount ?: 0.00;

            $request->coupon_reason = $coupon_reason ?: "";

            $request->is_coupon_applied = $is_coupon_applied;

            $request->payment_mode = PPV_PAY_VIA_WALLET;

            $request->total = $total ?: 0.00;

            $request->payment_id = 'WALLET-'.$admin_video_details->id.'-'.uniqid();

            $request->message = "PPV paid by wallet.(".substr($admin_video_details->title, 0, 25).")";

            // Check the user have enough amount in wallet

            $allow_payment = CustomWalletRepo::check_user_wallet($request->id, $total);

            if($allow_payment == NO) {

                throw new Exception( Helper::get_error_message(10005), 10005);
                
            }

            $response_array = PaymentRepo::ppv_payment_save($request, $admin_video_details, $user_details);

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());
        }

    }

    /**
     * @method subscription_pay_via_wallet()
     * 
     * @uses Pay the payment of subscription plan using wallet 
     *
     * @created : Shobana C
     *
     * @edited : 
     *
     * @param object $request - payment id, subscription id
     * 
     * @return resposne of success/failure message
     */
    public function subscription_pay_via_wallet(Request $request) {

        try {

            DB::beginTransaction();
            
            $validator = Validator::make($request->all(),
                [
                    'subscription_id' => 'required|integer|exists:subscriptions,id,status,'.APPROVED,
                    'coupon_code'=>'exists:coupons,coupon_code',
                ],
                [
                    'coupon_code.exists' => tr('coupon_code_not_exists'),
                    'subscription_id.exists' => tr('subscription_not_exists'),
                ]
            );

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages);

            }

            $subscription_details = Subscription::find($request->subscription_id);

            $user_details = User::find($request->id);

            if(!$subscription_details) {

                throw new Exception(Helper::get_error_message(154), 154);
            }
            
            // Initial detault values

            $total = $subscription_details->amount; 

            $coupon_amount = 0.00; $coupon_reason = ""; 

            $is_coupon_applied = COUPON_NOT_APPLIED;

            // Check the coupon code

            if($request->coupon_code) {
                
                $coupon_code_response = PaymentRepo::check_coupon_code($request, $user_details, $subscription_details->amount);

                $coupon_amount = $coupon_code_response['coupon_amount'];

                $coupon_reason = $coupon_code_response['coupon_reason'];

                $is_coupon_applied = $coupon_code_response['is_coupon_applied'];

                $total = $coupon_code_response['total'];

            }

            // Update the coupon details and total to the request

            $request->coupon_amount = $coupon_amount ?: 0.00;

            $request->coupon_reason = $coupon_reason ?: "";

            $request->is_coupon_applied = $is_coupon_applied;

            $request->payment_mode = SUBSCRIPTION_PAY_VIA_WALLET;

            $request->total = $total ?: 0.00;

            $request->payment_id = 'WALLET-'.$subscription_details->id.'-'.uniqid();

            $request->message = "Subscription paid by wallet.(".substr($subscription_details->title, 0, 25).")";

            // Check the user have enough amount in wallet

            $allow_subscription_payment = CustomWalletRepo::check_user_wallet($request->id, $total);

            if($allow_subscription_payment == NO) {

            	throw new Exception( Helper::get_error_message(10005), 10005);
            	
            }

            $response_array = PaymentRepo::subscriptions_payment_save($request, $subscription_details, $user_details);

            DB::commit();

            return response()->json($response_array, 200);

        } catch (Exception $e) {

            DB::rollback();

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }

     /**
     * @method check_coupon_applicable_to_user()
     *
     * @uses To check the coupon code applicable to the user or not
     *
     * @created: Shobana Chandrasekar
     *
     * @edited:
     *
     * @param objects $coupon - Coupon details
     *
     * @param objects $user - User details
     *
     * @return response of success/failure message
     */
    public function check_coupon_applicable_to_user($user, $coupon) {

        try {

            $sum_of_users = UserCoupon::where('coupon_code', $coupon->coupon_code)->sum('no_of_times_used');

            if ($sum_of_users < $coupon->no_of_users_limit) {


            } else {

                throw new Exception(tr('total_no_of_users_maximum_limit_reached'));
                
            }

            $user_coupon = UserCoupon::where('user_id', $user->id)
                ->where('coupon_code', $coupon->coupon_code)
                ->first();

            // If user coupon not exists, create a new row

            if ($user_coupon) {

                if ($user_coupon->no_of_times_used < $coupon->per_users_limit) {

                   // $user_coupon->no_of_times_used += 1;

                   // $user_coupon->save();

                    $response_array = ['success'=>true, 'message'=>tr('add_no_of_times_used_coupon'), 'code'=>2002];

                } else {

                    throw new Exception(tr('per_users_limit_exceed'));
                }

            } else {

                $response_array = ['success'=>true, 'message'=>tr('create_a_new_coupon_row'), 'code'=>2001];

            }

            return response()->json($response_array);

        } catch (Exception $e) {

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }

    /**
     * @method voucher_code_check()
     * 
     * @uses check the voucher code is valid or not
     *
     * @created Vithya R
     * 
     * @updated
     *
     * @param
     * 
     * @return json response
     *
     */

    public function voucher_code_check(Request $request) {

        try {

            $validator = Validator::make($request->all(),
                [
                    'voucher_code' => 'required|exists:custom_wallet_vouchers,voucher_code',
                   
                ]);

            if ($validator->fails()) {

                // Error messages added in response for debugging

                $error_messages = implode(',',$validator->messages()->all());

                throw new Exception($error_messages);

            }

            $voucher_code = $request->voucher_code;

            $voucher_details = CustomWalletVoucher::where('voucher_code' , $voucher_code)
                                    ->where('status', APPROVED)
                                    ->where('remaining_count' , '>', 0)
                                    ->first();

            if(!$voucher_details) {

                throw new Exception(Helper::get_error_message(10002), 10002);
            }

            $current_date = date('Y-m-d');

            if((date('Y-m-d' , strtotime($voucher_details->expiry_date))) < $current_date) {

                throw new Exception(Helper::get_error_message(10003), 10003);
                
            }

            $data = [];

            $response_array = ['success' => true, 'data' => $data];

            return response()->json($response_array, 200);
            
        } catch (Exception $e) {

            return $this->sendError($error = $e->getMessage(), $error_code = $e->getCode());

        }

    }

}
