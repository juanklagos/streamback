<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\Helper;

use Auth, Log, DB, Validator, Exception, Setting;

use App\Requests;

use App\SubCategory;

use App\Genre;

use App\AdminVideo;

use App\Admin, App\User, App\Moderator;

use App\Settings;

use App\Page;

use App\UserLoggedDevice;

use App\Subscription;

use App\UserPayment;

use App\OfflineAdminVideo;

use App\ReferralCode;

class ApplicationController extends Controller {

    public $expiry_date = "";   

    /**
     * @method payment_failture()
     * 
     * @uses to show thw view page, whenever the payment failed.
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return redirect angular URL
     *
     */

    public function payment_failure($error = "") {

        $paypal_error = \Session::get("paypal_error") ? \Session::get('paypal_error') : "";

        \Session::forget("paypal_error");

        // Redirect to angular payment failture page

        // @TODO Shobana please change this page to angular payment failure page 

        return redirect()->away(Setting::get('ANGULAR_SITE_URL'));

    }

    /**
     * @method generate_index()
     * 
     * @uses to generate index.php file to avoid uploads folder access
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return JSON Response
     *
     */

    public function generate_index(Request $request) {

        if($request->has('folder')) {

            Helper::generate_index_file($request->folder);

        }

        return response()->json(['success' => true , "message" => 'successfully']);

    }

    /**
     * @method select_genre()
     * 
     * @uses to get genres list based on the selected sub category FOR VIDEO UPLOAD
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return JSON Response
     *
     */

    public function select_genre(Request $request) {
        
        $sub_category_id = $request->option;

        $genres = Genre::where('sub_category_id', '=', $sub_category_id)
                        ->where('is_approved' , GENRE_APPROVED)
                          ->orderBy('name', 'asc')
                          ->get();

        return response()->json($genres);
    
    }

    /**
     * @method select_sub_category()
     * 
     * @uses to get subcategory list based on the selected category FOR VIDEO UPLOAD
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return JSON Response
     *
     */

    public function select_sub_category(Request $request) {
        
        $category_id = $request->option;

        $subcategories = SubCategory::where('category_id', '=', $category_id)
                            ->leftJoin('sub_category_images' , 'sub_categories.id' , '=' , 'sub_category_images.sub_category_id')
                            ->select('sub_category_images.picture' , 'sub_categories.*')
                            ->where('sub_category_images.position' , 1)
                            ->where('is_approved' , 1)
                            ->orderBy('name', 'asc')
                            ->get();

        return response()->json($subcategories);
    
    }

    /**
     * @method about()
     * 
     * @uses to return about details from static page table
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return view page
     *
     */

    public function about(Request $request) {

        $page_details = Page::where('type', 'about')->first();

        return view('static.about-us')->with('about' , $page_details)
                        ->with('page' , 'about')
                        ->with('subPage' , '');

    }

    /**
     * @method privacy()
     * 
     * @uses to return privacy details from static page table
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return view page
     *
     */

    public function privacy(Request $request) {

        $page_details = Page::where('type', 'privacy')->first();;

        return view('static.privacy')->with('data' , $page_details)
                        ->with('page' , 'conact_page')
                        ->with('subPage' , '');

    }

    /**
     * @method terms()
     * 
     * @uses to return terms details from static page table
     *
     * @created vidhya R
     *
     * @updated vidhya R
     * 
     * @param 
     *
     * @return view page
     *
     */

    public function terms(Request $request) {

        $page_details = Page::where('type', 'terms')->first();;

        return view('static.terms')->with('data' , $page_details)
                        ->with('page' , 'terms_and_condition')
                        ->with('subPage' , '');

    }

    /**
     * Function cron_publish_video()
     *
     * @uses used to publish the later videos
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return - 
     */
    
    public function cron_publish_video(Request $request) {
        
        Log::info('cron_publish_video');

        $admin = Admin::first();
        
        $timezone = 'Asia/Kolkata';

        if($admin) {

            $timezone = $admin->timezone ? $admin->timezone : $timezone;
        }

        $date = convertTimeToUSERzone(date('Y-m-d H:i:s'), $timezone);

        $videos = AdminVideo::where('publish_time' ,'<=' ,"$date")
                        ->where('status' , 0)
                        ->get();

        foreach ($videos as $key => $video_details) {

            Log::info('Change the status');

            $video_details->status = 1;

            $video_details->save();
        
        }
    
    }

    /**
     * Function send_notification_user_payment()
     *
     * @uses used to publish the video
     *
     * @created vidhya R
     *
     * @edited vidhya R
     *
     * @param - 
     *
     * @return - 
     */

    public function send_notification_user_payment(Request $request) {

        Log::info("Notification to User for Payment");

        $time = date("Y-m-d");
        // Get provious provider availability data

        $current_date = date('Y-m-d H:i:s');

        $compare_date = date('Y-m-d', strtotime('-1 day', strtotime($current_date)));

        $payments = UserPayment::where('expiry_date' , $compare_date)->where('status',1)->get();

        // $query = "SELECT *, TIMESTAMPDIFF(SECOND, '$time',expiry_date) AS date_difference FROM user_payments";

        // $payments = DB::select(DB::raw($query));

        // Log::info(print_r($payments,true));

        if($payments) {
            
            foreach($payments as $payment) {

                // if($payment->date_difference <= 864000) {
                   
                    // Delete provider availablity
                   
                    Log::info('Send mail to user');

                    if($user_details = User::where('id' , $payment->user_id)->where('user_type' , 1)->first()) {

                        if($user_details->is_activated == 1 && $user_details->is_verified == 1) {

                            Log::info($user_details->email);

                            $email_data = array();

                            // Send welcome email to the new user:

                           // $subject = tr('payment_notification');

                            $email_data['id'] = $user_details->id;

                            $email_data['name'] = $user_details->name;

                            $email_data['expiry_date'] = $payment->expiry_date;

                            $email_data['template_type'] = PAYMENT_GOING_TO_EXPIRY;

                            $email_data['status'] = 0;

                            $page = "emails.payment-notification-expiry";

                            $email = $user_details->email;

                            $result = Helper::send_email($page,$subject = null,$email,$email_data);

                            \Log::info("Email".$result);

                        }
                    
                    }

                // }
            }

            Log::info("Notification to the User successfully....:-)");
        
        } else {
            Log::info(" records not found ....:-(");
        }
    
    }

    /**
     * @method user_payment_expiry()
     *
     * @uses to change the paid user to normal user based on the expiry date
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function user_payment_expiry(Request $request) {
        // TIMESTAMPDIFF(SECOND, '$current_time','$expiry_date') - THIS WILL NOT WORK $current_time = "2019-12-13 00:00:00"; $expiry_date = "2018-12-13 00:00:00";

        // $query = "SELECT *, TIMESTAMPDIFF(SECOND, '$time',expiry_date) AS date_difference
        //           FROM user_payments where status=1";

        // $payments = DB::select(DB::raw($query));

        $current_time = date("Y-m-d H:i:s");

        // $current_time = "2018-06-06 18:01:56";

        $pending_payments = UserPayment::leftJoin('users' , 'user_payments.user_id' , '=' , 'users.id')
                    ->where('user_payments.status' , 1)
                    ->where('users.expiry_date' ,"<=" , $current_time)
                    ->where('users.user_type' ,1)
                    ->get();
       
        if($pending_payments) {

            $count = 0;

            foreach($pending_payments as $pending_payment_details) {

                // Check expiry date one more time (Cross Verification)
                
                if(strtotime($pending_payment_details->expiry_date) <= strtotime($current_time)) {

                    // Delete User

                    $email_data = array();
                    
                    if($user_details = User::where('id' ,$pending_payment_details->user_id)->where('user_type' , 1)->first()) {

                        Log::info('Send mail to user');
                        
                        $user_details->user_type = 0;

                        $user_details->user_type_change_by = "CRON";
                        
                        $user_details->save();
                        
                        $count = $count +1;

                        if($user_details->is_activated == 1 && $user_details->is_verified == 1) {

                            // Send welcome email to the new user:

                           // $subject = tr('payment_notification');

                            $email_data['id'] = $user_details->id;

                            $email_data['username'] = $user_details->name;

                            $email_data['expiry_date'] = $pending_payment_details->expiry_date;

                            $email_data['template_type'] = PAYMENT_EXPIRED;

                            $email_data['status'] = 1;

                            $page = "emails.payment-expiry";

                            $email = $user_details->email;

                            $result = Helper::send_email($page,$subject = null,$email,$email_data);

                            \Log::info("Email".print_r($result , true));
                        
                        }
                   
                    }
                
                }
            }

            Log::info("Notification to the User successfully....:-)");

            $response_array = ['success' => true , 'message' => "Notification to the User successfully....:-)" , 'count' => $count];

            return response()->json($response_array , 200);

        } else {

            Log::info("PAYMENT EXPIRY - Records Not Found ....:-(");

            $response_array = ['success' => false , 'error_messages' => "PAYMENT EXPIRY - Records Not Found ....."];

            return response()->json($response_array , 200);

        }
    
    }

    /**
     * @method search_video()
     *
     * @uses Used to get the video results based on the search key
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function search_video(Request $request) {

        $validator = Validator::make(
            $request->all(),
            array(
                'term' => 'required',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );
    
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());
            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);

            return false;
        
        } else {

            $q = $request->term;

            \Session::set('user_search_key' , $q);

            $items = array();
            
            $results = Helper::search_video($q);

            if($results) {

                foreach ($results as $i => $key) {

                    $check = $i+1;

                    if($check <=10) {
     
                        array_push($items,$key->title);

                    } if($check == 10 ) {
                        array_push($items,"View All" );
                    }
                
                }

            }

            return response()->json($items);
        }     
    
    }

    /**
     * @method search_all()
     *
     * @uses Used to get all the videos based on the search key
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return VIEW PAGE
     */

    public function search_all(Request $request) {

        $validator = Validator::make($request->all(),
            array(
                'key' => '',
            ),
            array(
                'exists' => 'The :attribute doesn\'t exists',
            )
        );
    
        if ($validator->fails()) {

            $error_messages = implode(',', $validator->messages()->all());

            $response_array = array('success' => false, 'error_messages' => Helper::get_error_message(101), 'error_code' => 101, 'error_messages'=>$error_messages);
        
        } else {

            if($request->has('key')) {
                $q = $request->key;    
            } else {
                $q = \Session::get('user_search_key');
            }

            if($q == "all") {
                $q = \Session::get('user_search_key');
            }

            $videos = Helper::search_video($q,1);

            return view('user.search-result')->with('key' , $q)->with('videos' , $videos)->with('page' , "")->with('subPage' , "");
        }     
    
    }

    /**
     * @method email_verify()
     *
     * @uses To verify the email from user, while user clicking for email verification
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function email_verify(Request $request) {

        \Log::info('User Detals'.print_r($request->all(), true));

        // Check the request have user ID

        if($request->id) {

            \Log::info('id');

            // Check the user record exists

            if($user = User::find($request->id)) {


                \Log::info('user');

                // Check the user already verified

                if(!$user->is_verified) {


                    \Log::info('is_verified');

                    // Check the verification code and expiry of the code

                    $response = Helper::check_email_verification($request->verification_code , $user->id, $error);

                    if($response) {

                        $user->is_verified = 1;

                        \Log::info('User verified');

                        \Log::info('Before User Id verified'.$user->is_verified);
                        
                        $user->save();

                        \Log::info('After User Id verified'.$user->is_verified);

                        // \Auth::loginUsingId($request->id);

                        // return redirect()->away(Setting::get('ANGULAR_SITE_URL')."signin");

                    } else {

                        // return redirect(route('user.login.form'))->with('flash_error' , $error);
                    }

                } else {

                    \Log::info('User Already verified');

                    // \Auth::loginUsingId($request->id);

                    //return redirect(route('user.dashboard'));
                }

            } else {
                // return redirect(route('user.login.form'))->with('flash_error' , "User Record Not Found");
            }

        } else {

            // return redirect(route('user.login.form'))->with('flash_error' , "Something Missing From Email verification");
        }

        return redirect()->away(Setting::get('ANGULAR_SITE_URL')."/login");
    
    }

    /**
     * @method user_payment_expiry()
     *
     * @uses Used to change the paid user to normal user based on the expiry date
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONS
     */

    public function admin_control() {

        if (Auth::guard('admin')->check()) {

            return view('admin.settings.control')->with('page', tr('admin_control'));

        } else {

            return back();

        }
        
    }

    /**
     * @method user_payment_expiry()
     *
     * @uses Used to change the paid user to normal user based on the expiry date
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function save_admin_control(Request $request) {

        $model = Settings::get();

        $basicValidator = Validator::make(
                $request->all(),
                array(
                    'no_of_static_pages' => 'numeric|min:7|max:15',
                )
            );

            if($basicValidator->fails()) {

                $error_messages = implode(',', $basicValidator->messages()->all());

                return back()->with('flash_error', $error_messages);       

            } else {


                foreach ($model as $key => $value) {
        
                $current_key = $value->key;

                if($request->has($current_key)) {

                    $value->value = $request->$current_key;

                }
                
                $value->save();

            }
            return back()->with('flash_success' , tr('settings_success'));
        }
    
    }

    /**
     * @method user_payment_expiry()
     *
     * @uses Used to change the paid user to normal user based on the expiry date
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function set_session_language($lang) {

        $locale = \Session::put('locale', $lang);

        return back()->with('flash_success' , tr('session_success'));
    }

    /**
     * @method check_token_expiry()
     *
     * @uses - 
     *
     * @created Shobana C 
     *
     * @edited Shobana C
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function check_token_expiry() {

        $model = UserLoggedDevice::get();

        foreach ($model as $key => $value) {

            $user = User::find($value->user_id);

            if ($user) {
           
                if(!Helper::is_token_valid('USER', $user->id, $user->token, $error)) {

                    // $response = response()->json($error, 200);
                    
                    if ($value->delete()) {

                        $user->logged_in_account -= 1;

                        $user->save();

                    }

                }

            }

        }

    }


    /**
     * @method automatic_renewal_stripe()
     *
     * @uses to change the paid user to normal user based on the expiry date
     *
     * @created SHOBANA C 
     *
     * @edited Vidhya R
     *
     * @param -
     *
     * @return JSON RESPONSE
     */

    public function automatic_renewal_stripe() {

        // Check the stripe 

        $stripe_secret_key = Setting::get('stripe_secret_key');

        if(!$stripe_secret_key) {

            $response_array = ['success' => false , 'error_messages' => "STRIPE NOT CONFIGURED"];

            return response()->json($response_array , 200);

        }

        $current_time = date("Y-m-d H:i:s");

        $pending_payments = UserPayment::select(DB::raw('*, max(id) as user_payment_id'), DB::raw("TIMESTAMPDIFF(SECOND, '$current_time',expiry_date) AS date_difference"), 'is_cancelled')
                        ->where('user_payments.status', 1)
                        ->where('user_payments.amount', '>', 0)
                        ->orderBy('user_payments.expiry_date', 'desc')
                        ->groupBy('user_payments.user_id')
                        ->get();

        if($pending_payments) {

            $total_renewed = 0;

            $s_data = $data = [];

            foreach($pending_payments as $pending_payment_details){

                $pending_payment_details = UserPayment::select(DB::raw('*, max(id) as user_payment_id'), 'user_payments.user_id')->where('id', $pending_payment_details->user_payment_id)->first();

                if ($pending_payment_details->is_cancelled == AUTORENEWAL_CANCELLED) {

                    Log::info("User cancelled....:-)");

                } else {

                    // Check the pending payments expiry date

                    if(strtotime($pending_payment_details->expiry_date) <= strtotime($current_time)) {

                        // Delete provider availablity

                        Log::info('Send mail to user');

                        $email_data = array();
                        
                        if($user_details = User::find($pending_payment_details->user_id)) {

                            Log::info("the User exists....:-)");

                            $check_card_exists = User::where('users.id' , $pending_payment_details->user_id)
                                            ->leftJoin('cards' , 'users.id','=','cards.user_id')
                                            ->where('cards.id' , $user_details->card_id)
                                            ->where('cards.is_default' , DEFAULT_TRUE);

                            if($check_card_exists->count() != 0) {

                                $user_card = $check_card_exists->first();
                            
                                $subscription = Subscription::find($pending_payment_details->subscription_id);

                                if ($subscription) {

                                    $customer_id = $user_card->customer_id;

                                    if($stripe_secret_key) {

                                        \Stripe\Stripe::setApiKey($stripe_secret_key);

                                    } else {

                                        Log::info(Helper::get_error_message(902));
                                    }

                                    $total = $subscription->amount;

                                    try {

                                        $user_charge =  \Stripe\Charge::create(array(
                                            "amount" => $total * 100,
                                            "currency" => "usd",
                                            "customer" => $customer_id,
                                        ));

                                       $payment_id = $user_charge->id;
                                       $amount = $user_charge->amount/100;
                                       $paid_status = $user_charge->paid;

                                        if($paid_status) {

                                            $previous_payment = UserPayment::where('user_id' , $pending_payment_details->user_id)
                                                ->where('status', DEFAULT_TRUE)->orderBy('created_at', 'desc')->first();

                                            $user_payment = new UserPayment;

                                            if($previous_payment) {

                                                $expiry_date = $previous_payment->expiry_date;

                                                $user_payment->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_date. "+".$subscription->plan." months"));

                                            } else {
                                                
                                                $user_payment->expiry_date = date('Y-m-d H:i:s',strtotime("+".$subscription->plan." months"));
                                            }

                                            $user_payment->payment_id  = $payment_id;

                                            $user_payment->user_id = $pending_payment_details->user_id;

                                            $user_payment->subscription_id = $subscription->id;

                                            $user_payment->status = 1;

                                            $user_payment->from_auto_renewed = 1;

                                            $user_payment->amount = $amount;

                                            if ($user_payment->save()) {

                                                $user_details->user_type = 1;
                                                
                                                $user_details->expiry_date = $user_payment->expiry_date;

                                                $user_details->save();
                                            
                                                Log::info(tr('payment_success'));

                                                $total_renewed = $total_renewed + 1;

                                            } else {

                                                Log::info(Helper::get_error_message(902));

                                            }

                                        } else {

                                           Log::info(Helper::get_error_message(903));

                                        }

                                    
                                    } catch(\Stripe\Error\RateLimit $e) {

                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        Log::info("response array".print_r($response_array , true));

                                    } catch(\Stripe\Error\Card $e) {

                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();

                                        Log::info("response array".print_r($response_array , true));

                                    } catch (\Stripe\Error\InvalidRequest $e) {
                                        // Invalid parameters were supplied to Stripe's API
                                       
                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();


                                        Log::info("response array".print_r($response_array , true));

                                    } catch (\Stripe\Error\Authentication $e) {

                                        // Authentication with Stripe's API failed

                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();

                                        Log::info("response array".print_r($response_array , true));

                                    } catch (\Stripe\Error\ApiConnection $e) {

                                        // Network communication with Stripe failed

                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();

                                        Log::info("response array".print_r($response_array , true));

                                    } catch (\Stripe\Error\Base $e) {
                                      // Display a very generic error to the user, and maybe send
                                        
                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();

                                        Log::info("response array".print_r($response_array , true));

                                    } catch (Exception $e) {
                                        // Something else happened, completely unrelated to Stripe

                                        $error_message = $e->getMessage();

                                        $error_code = $e->getCode();

                                        $response_array = ['success'=>false, 'error_messages'=> $error_message , 'error_code' => $error_code];

                                        $pending_payment_details->reason_auto_renewal_cancel = $error_message;

                                        $pending_payment_details->save();

                                        $user_details->user_type = 0;

                                        $user_details->user_type_change_by = "AUTO-RENEW-PAYMENT-ERROR";
                                        
                                        $user_details->save();

                                        Log::info("response array".print_r($response_array , true));
                                   
                                    }

                                }

                                // Send welcome email to the new user:

                                $subject = tr('automatic_renewal_notification');

                                $email_data['id'] = $user_details->id;
                                $email_data['username'] = $user_details->name;
                                $email_data['expiry_date'] = $pending_payment_details->expiry_date;
                                $email_data['status'] = 1;

                                $page = "emails.automatic-renewal";

                                $email = $user_details->email;

                                $result = Helper::send_email($page,$subject,$email,$email_data);

                                // \Log::info("Email".$result);

                            } else {

                                $pending_payment_details->reason_auto_renewal_cancel = "NO CARD";

                                $pending_payment_details->save();

                                $user_details->user_type = 0;

                                $user_details->user_type_change_by = "AUTO-RENEW-NO-CARD";
                                
                                $user_details->save();

                                Log::info("No card available....:-)");

                            }
                       
                        }

                        $data['user_payment_id'] = $pending_payment_details->user_payment_id;

                        $data['user_id'] = $pending_payment_details->user_id;

                        array_push($s_data , $data);
                   
                    }

                }            
            
            }
            
            Log::info("Notification to the User successfully....:-)");

            $response_array = ['success' => true, 'total_renewed' => $total_renewed , 'data' => $s_data];

            return response()->json($response_array , 200);

        } else {

            Log::info(" records not found ....:-(");

            $response_array = ['success' => false , 'error_messages' => "NO PENDING PAYMENTS"];
        }

        return response()->json($response_array , 200);

    }

    /**
     * @method signout_all_devices
     *
     * @uses To check the expiry date.if the token expired, changed the no of accounts into "zero"
     *
     * @created - Shobana Chandrasekar
     *
     * @updated - -
     *s
     * @param --
     *
     * @return response of log info
     */
    public function signout_all_devices(Request $request) {

        // Get expired users

        $users = User::select('id', 'token_expiry', 'logged_in_account')
            ->where('token_expiry', '<=', time())
            ->where('logged_in_account', '>', 0)
            ->get();

        // Check users exists or not

        if ($users) {

            // Log::info("Count of Users ".print_r(count($users)));

            foreach ($users as $key => $value) {
                
                $value->logged_in_account = 0;

                $value->save();
            }
        }
    }

    /**
     * @method configuration_mobile()
     *
     * @uses used to get the configurations for base products
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return JSON Response
     */

    public function configuration_site(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:users,id',
                'token' => 'required',

            ]);

            if($validator->fails()) {

                $error = implode(',',$validator->messages()->all());

                throw new Exception($error, 101);

            } else {

                $config_data = $data = [];

                $payment_data['is_stripe'] = 1;

                $payment_data['stripe_publishable_key'] = Setting::get('stripe_publishable_key') ?: "";

                $payment_data['stripe_secret_key'] = Setting::get('stripe_secret_key') ?: "";

                $payment_data['stripe_secret_key'] = Setting::get('stripe_secret_key') ?: "";

                $payment_data['is_paypal'] = 1;

                $payment_data['PAYPAL_ID'] = envfile('PAYPAL_ID') ?: "";

                $payment_data['PAYPAL_SECRET'] = envfile('PAYPAL_SECRET') ?: "";

                $payment_data['PAYPAL_MODE'] = envfile('PAYPAL_MODE') ?: "sandbox";

                $payment_data['currency_code'] = Setting::get('currency_code') ?: "USD";

                $payment_data['currency'] = Setting::get('currency') ?: "$";

                $data['payments'] = $payment_data;

                $data['urls']  = [];

                $url_data['base_url'] = envfile("APP_URL") ?: "";

                $url_data['socket_url'] = Setting::get("socket_url") ?: "";

                $data['urls'] = $url_data;

                $notification_data['FCM_SENDER_ID'] = envfile('FCM_SENDER_ID') ?: "";

                $notification_data['FCM_SERVER_KEY'] = $notification_data['FCM_API_KEY'] = envfile('FCM_SERVER_KEY') ?: "";

                $notification_data['FCM_PROTOCOL'] = envfile('FCM_PROTOCOL') ?: "";

                $data['notification'] = $notification_data;

                $data['site_name'] = Setting::get('site_name');

                $data['site_logo'] = Setting::get('site_logo');

                $data['currency'] = Setting::get('currency');

                $response_array = ['success' => true , 'data' => $data];

                return response()->json($response_array , 200);

            }

        } catch(Exception $e) {

            $error_message = $e->getMessage();

            $response_array = ['success' => false,'error' => $error_message,'error_code' => 101];

            return response()->json($response_array , 200);

        }
   
    }




    /**
     * @method checkDownloadVideoStatus()
     *
     * To check downloaded videos list every day night
     *
     * @param - 
     *
     * @created_by shobana
     *
     * @updated_by shobana
     *
     * @return response of json
     *
     */
    public function checkDownloadVideoStatus(Request $request){

        $model = OfflineAdminVideo::where('download_status', DOWNLOAD_COMPLETE_STAUTS)->get();

        foreach ($model as $key => $value) {

            $current_date = date('Y-m-d');
            
            $diff_bw_days = calculateDays($current_date,$value->expiry_date);

            if ($diff_bw_days > 0) {


            } else {

                $value->is_expired = DEFAULT_TRUE;

                Log::info("downloaded id ".$value->id);

                $value->save();

            }

        }

    }


    /**
     * @method static_pages_api()
     *
     * @uses to get the pages
     *
     * @created Vidhya R 
     *
     * @edited Vidhya R
     *
     * @param - 
     *
     * @return JSON Response
     */

    public function static_pages_api(Request $request) {

        $base_query = Page::where('status' , APPROVED)->orderBy('title', 'asc')
                                ->select('id as page_id' , 'heading as title' , 'description','type as page_type', 'status' , 'created_at' , 'updated_at');
                                
        if($request->page_type) {

            $static_pages = $base_query->where('type' , $request->page_type)->first();

        } elseif($request->page_id) {

            $static_pages = $base_query->where('id' , $request->page_id)->first();

        } else {

            $static_pages = $base_query->get();

        }

        $response_array = ['success' => true , 'data' => $static_pages ? $static_pages->toArray(): []];

        return response()->json($response_array , 200);

    }


    /**
     * @method demo_credential_cron()
     *
     * @uses To update demo login credentials.
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param  
     *
     * @return 
     */
    public function demo_credential_cron() {

        Log::info('Demo credential CRON STARTED');

        try {
            
            DB::beginTransaction(); 

            $demo_admin = 'admin@streamview.com';

            $admin_details = Admin::where('email', $demo_admin)->first();

            if(!$admin_details) {

                $admin_details = new Admin;

                $admin_details->name = 'Admin';

                $admin_details->email = $demo_admin;  

                $admin_details->picture = "http://adminview.streamhash.com/placeholder.png";
            }

            $admin_details->password = \Hash::make('123456');
            
            $demo_user = 'user@streamview.com';

            $user_details = User::where('email', $demo_user)->first();
            
            if(!$user_details) {

                $user_details = new User;

                $user_details->name = 'User';

                $user_details->picture ="http://adminview.streamhash.com/placeholder.png";

                $user_details->login_by = "manual";

                $user_details->device_type = "web";

                $user_details->is_activated = $user_details->status = $user_details->user_type = $user_details->is_verified =1;

                $user_details->token = Helper::generate_token();

                $user_details->token_expiry = Helper::generate_token_expiry();
            
            }
            
            $user_details->email = 'user@streamview.com';

            $user_details->password = \Hash::make('123456');

            $demo_moderator = 'moderator@streamview.com';

            $moderator_details = Moderator::where('email', $demo_moderator)->first();
            
            if(!$moderator_details) {

                $moderator_details = New Moderator;

                $moderator_details->name = 'Moderator';

                $moderator_details->email = 'moderator@streamview.com';

                $moderator_details->picture ="http://adminview.streamhash.com/placeholder.png";

                $moderator_details->is_activated = 1;

                $moderator_details->token = Helper::generate_token();

                $moderator_details->token_expiry = Helper::generate_token_expiry();
            
            }
            
            $moderator_details->password = \Hash::make('123456');

            if($user_details->save() && $moderator_details->save() && $admin_details->save()) {

                DB::commit();

            } else {

                throw new Exception("Demo Credential CRON - Credential Could not be updated", 101);                
            }
            
         } catch(Exception $e) {

            DB::rollback();

            Log::info('Demo Credential CRON Error:'.print_r($e->getMessage() , true));

        }       
        
        Log::info('Demo Credential CRON END');

    }

    /**
     * @method admin_videos_auto_clear_cron()
     *
     * @uses To auto-clear videos uploaded
     *
     * @created Anjana H
     *
     * @updated Anjana H
     *
     * @param  
     *
     * @return 
     */
    public function admin_videos_auto_clear_cron() {

        Log::info('VideoTapes Auto-Clear Cron STARTED');

        try {
            
            $date = date('Y-m-d');

            DB::beginTransaction(); 

            if(AdminVideo::where('uploaded_by', '!=', ADMIN)->whereDate('created_at','<', $date)->delete()) {

                DB::commit();

                Log::info('VideoTapes Auto-Cleared');
            } 
                        
         } catch(Exception $e) {

            DB::rollback();
        }       
        
        Log::info('VideoTapes Auto-Clear Cron END');

    }

    /**
     * @method  referrals_signup()
     *
     * @uses Referral SignUp View
     *
     * @created Bhawya
     *
     * @updated
     *
     * @param string referral_code 
     *
     * @return redirect signup page
     */
    public function referrals_signup($referral_code){

        try {

            if(!$referral_code) {

                return redirect()->away(Setting::get('ANGULAR_SITE_URL'))->with('flash_error',tr('referral_code_invalid'));
            }

            $referral_codes =  ReferralCode::where('referral_code', $referral_code)->where('status', APPROVED)->first();

            if(!$referral_codes) {

                return redirect()->away(Setting::get('ANGULAR_SITE_URL'))->with('flash_error',tr('referral_code_invalid'));
            }

            $user_details = User::where('status', USER_APPROVED)->where('id', $referral_codes->user_id)->first();

            if(!$user_details) {

                return redirect()->away(Setting::get('ANGULAR_SITE_URL'))->with('flash_error',tr('referral_code_invalid'));
            }

            return redirect()->away(Setting::get('ANGULAR_SITE_URL')."/register/$referral_code");

        } catch(Exception $e) {

            return $this->sendError($e->getMessage(), $e->getCode());
        }

    }

    // Used For Version Upgrade - V6.0 Referral Option
    /**
     * @method  generate_referral_code()
     *
     * @uses Generate Referral code for Existing Users.
     *
     * @created Bhawya
     *
     * @updated Bhawya
     *
     * @param string 
     *
     * @return
     */
    public function generate_referral_code(){

        try {

            $referral_users = ReferralCode::where('status', APPROVED)->count();

            // $users_count = User::whereNotIn('id',$referral_users)->count();

            User::chunk(100, function($users){

                Log::info("Count of users ".count($users));
                
                foreach ($users as $key => $user_details) {
                    
                    $referral_codes = ReferralCode::where('user_id', $user_details->id)->first();
            
                    if(!$referral_codes) {

                        $referral_codes = Helper::user_referral_code($user_details->id);

                    }

                }

            });

            return view('admin.settings.referral_users')
                ->with('users_count',$referral_users)
                ->with('flash_success', tr('referral_code_generated_for_all_users'));

        } catch(Exception $e) {

            return $this->sendError($e->getMessage(), $e->getCode());
        }

    }


}