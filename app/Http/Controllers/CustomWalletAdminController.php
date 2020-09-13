<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\CustomWalletVoucher;

use App\CustomWallet;

use App\CustomWalletPayment;

use App\User;

use DB, Validator, Setting, Exception;

class CustomWalletAdminController extends Controller
{   
    //Not using in the version 5.0. Will be adding this feature in upcoming versions

    /**
     * @method wallet_vouchers_index
     *
     * @uses Get the wallet_vouchers list
     *
     * @created vidhya
     *
     * @updated vidhya 
     *
     * @param 
     * 
     * @return view page
     *
     */

    public function custom_wallet_vouchers_index() {

        $wallet_vouchers = CustomWalletVoucher::orderBy('created_at','desc')->get();

        foreach ($wallet_vouchers as $key => $wallet_voucher_details) {


            if($wallet_voucher_details->used_count >= 1) {

                $check_wallet = CustomWalletPayment::where('voucher_code', $wallet_voucher_details->voucher_code)->first();

                if($check_wallet) {

                    $user_details = User::find($check_wallet->user_id);

                    $wallet_voucher_details->user_id = $user_details ? $user_details->id : "";

                    $wallet_voucher_details->username = $user_details ? $user_details->name : "";

                }
            }
        }

        return view('admin.wallet_vouchers.index')
                    ->with('page' , 'wallet_vouchers')
                    ->with('sub_page','wallet_vouchers-view')
                    ->with('wallet_vouchers' , $wallet_vouchers);

    }

    /**
     * @method custom_wallet_vouchers_create
     *
     * @uses create wallet_voucher page
     *
     * @created vidhya
     *
     * @updated vidhya 
     *
     * @param 
     * 
     * @return view page
     *
     */

    public function  custom_wallet_vouchers_create() {

        $wallet_voucher_details = new CustomWalletVoucher;
        
        return view('admin.wallet_vouchers.create')
                ->with('page' , 'wallet_vouchers')
                ->with('sub_page','wallet_vouchers-create')
                ->with('wallet_voucher_details', $wallet_voucher_details);
    
    }

    /**
     * @method wallet_vouchers_edit
     *
     * @uses To edit a wallet_voucher based on their id
     *
     * @created vidhya
     *
     * @updated vidhya 
     *
     * @param Integer $request - wallet_voucher id
     * 
     * @return response of new wallet_voucher object
     *
     */    
    public function custom_wallet_vouchers_edit($wallet_voucher_id, Request $request){

        $wallet_voucher_details = CustomWalletVoucher::find($wallet_voucher_id);

        if($wallet_voucher_details){

            return view('admin.wallet_vouchers.edit')
                        ->with('page','wallet_vouchers')
                        ->with('sub_page','wallet_vouchers-view')
                        ->with('wallet_voucher_details',$wallet_voucher_details);

        } else {

            return back()->with('flash_error', tr('admin_wallet_voucher_not_found'));

        }
    
    }

    /**
     * @method wallet_vouchers_save
     *
     * @uses To save the details based on wallet_voucher or to create a new wallet_voucher
     *
     * @created Vidhya
     *
     * @updated vidhya
     *
     * @param object $request -  wallet_voucher object details
     * 
     * @return response of success/failure response details
     *
     */

    public function custom_wallet_vouchers_save(Request $request) {

        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(),[
                'id' => 'exists:wallet_vouchers,id',
                'name' => 'required|max:255',
                'voucher_code' => $request->wallet_voucher_id ? 'required|max:10|min:1|unique:wallet_vouchers,voucher_code,'.$request->wallet_voucher_id : 'required|unique:wallet_vouchers,voucher_code|min:1|max:10',
                'amount' => 'required|numeric|min:1|max:5000',
                'expiry_date' => 'required|date_format:d-m-Y|after:today',
                'total_count' => 'numeric|min:1|max:1000',
                'per_user_limit' => 'numeric|min:1|max:100',
            ]);
             
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);

            } else {
            
                $wallet_voucher_details = new CustomWalletVoucher;

                $message = tr('admin_wallet_voucher_created_success');

                if($request->wallet_voucher_id != '') {

                    $wallet_voucher_details = CustomWalletVoucher::find($request->wallet_voucher_id);

                    $message = tr('admin_wallet_voucher_updated_success');

                    if($request->has('total_count') && $request->total_count != $wallet_voucher_details->total_count) {

                        $remaining_count = $request->total_count - $wallet_voucher_details->used_count;

                        $wallet_voucher_details->remaining_count = $remaining_count >= 0 ? $remaining_count : 0;

                        if($remaining_count < 0) {
                            
                            $wallet_voucher_details->used_count = 0;
                        }

                    }
        
                } else {
                   
                    $wallet_voucher_details->status = YES;

                    $wallet_voucher_details->remaining_count = $request->total_count ?: $wallet_voucher_details->total_count;

                }

                $wallet_voucher_details->name = $request->name ?: $wallet_voucher_details->name;

                // Remove the string space and special characters
                $voucher_code_format  = preg_replace("/[^A-Za-z0-9\-]+/", "", $request->voucher_code);

                // Replace the string uppercase format
                $wallet_voucher_details->voucher_code = strtoupper($voucher_code_format);

                // Convert date format year,month,date purpose of database storing
                $wallet_voucher_details->expiry_date = date('Y-m-d',strtotime($request->expiry_date));
 
                $wallet_voucher_details->total_count = $request->total_count ?: $wallet_voucher_details->total_count;

                $wallet_voucher_details->description = $request->description ?: ($wallet_voucher_details->description ?: "");

                $wallet_voucher_details->amount = $request->amount ?: $wallet_voucher_details->amount;

                if($wallet_voucher_details->save()) {

                    DB::commit();

                    return redirect()->route('admin.wallet_vouchers.view', ['id' => $wallet_voucher_details->id])->with('flash_success', $message);

                } else {

                    return back()->with('flash_error', tr('admin_wallet_voucher_save_error'));
                }
            }

        } catch(Exception $e) {

            DB::rollback();

            return redirect()->back()->withInput()->with('flash_error', $e->getMessage());

        }
        
    }

    /**
     * @method wallet_vouchers_generate
     *
     * @uses To save the details based on wallet_voucher or to create a new wallet_voucher
     *
     * @created Vidhya
     *
     * @updated vidhya
     *
     * @param object $request -  wallet_voucher object details
     * 
     * @return response of success/failure response details
     *
     */

    public function custom_wallet_vouchers_generate(Request $request) {

        try {

            DB::beginTransaction();

           
            $validator = Validator::make($request->all(),[
                'id' => 'exists:custom_wallet_vouchers,id',
                'name' => 'required|max:255',
                'voucher_code' => "",
                'amount' => 'required|numeric|min:1|max:5000',
                'expiry_date' => 'required|date_format:d-m-Y|after:today',
                'total_count' => 'numeric|min:1|max:1000',
                'per_user_limit' => 'numeric|min:1|max:100',
                'how_many_vouchers' => $request->wallet_voucher_id ? "required" : ""
            ]);
             
            if($validator->fails()) {

                $error = implode(',', $validator->messages()->all());

                throw new Exception($error, 101);

            } else {

                for ($i = 0; $i < $request->how_many_vouchers ; $i++) { 
            
                    $wallet_voucher_details = new CustomWalletVoucher;

                    $message = tr('admin_wallet_voucher_created_success');
                       
                    $wallet_voucher_details->status = YES;

                    $wallet_voucher_details->remaining_count = $request->total_count ?: $wallet_voucher_details->total_count;

                    $wallet_voucher_details->name = $request->name ?: $wallet_voucher_details->name;

                    // $voucher_code = rand(1000, 9999).rand(1000, 9999).rand(10000000, 99999999);

                    $voucher_code = randomKey(16);

                    // Remove the string space and special characters
                    # $voucher_code_format  = preg_replace("/[^A-Za-z0-9\-]+/", "", $voucher_code);

                    // Replace the string uppercase format
                    // $wallet_voucher_details->voucher_code = strtoupper($voucher_code_format);

                    $wallet_voucher_details->voucher_code = $voucher_code;

                    // Convert date format year,month,date purpose of database storing
                    $wallet_voucher_details->expiry_date = date('Y-m-d',strtotime($request->expiry_date));
     
                    $wallet_voucher_details->total_count = $request->total_count ?: $wallet_voucher_details->total_count;

                    $wallet_voucher_details->description = $request->description ?: ($wallet_voucher_details->description ?: "");

                    $wallet_voucher_details->amount = $request->amount ?: $wallet_voucher_details->amount;

                    $wallet_voucher_details->save();

                }

                DB::commit();

                return redirect()->route('admin.wallet_vouchers.index')->with('flash_success', $message);
            }

        } catch(Exception $e) {

            DB::rollback();

            return redirect()->back()->withInput()->with('flash_error', $e->getMessage());

        }
        
    }

    /**
     * @method wallet_vouchers_view
     *
     * @uses view the selected wallet_voucher details 
     *
     * @created vidhya
     *
     * @updated Vidhya
     *
     * @param integer $wallet_voucher_id
     * 
     * @return view page
     *
     */
    public function custom_wallet_vouchers_view($wallet_voucher_id) {

        $wallet_voucher_details = CustomWalletVoucher::where('id', $wallet_voucher_id)->first();

        if($wallet_voucher_details) {

            if($wallet_voucher_details->used_count >= 1) {

                $check_wallet = CustomWalletPayment::where('voucher_code', $wallet_voucher_details->voucher_code)->first();

                if($check_wallet) {

                    $user_details = User::find($check_wallet->user_id);

                    $wallet_voucher_details->user_id = $user_details ? $user_details->id : "";

                    $wallet_voucher_details->username = $user_details ? $user_details->name : "";

                }
            }

            return view('admin.wallet_vouchers.view')
                        ->withPage('wallet_vouchers')
                        ->with('sub_page','wallet_vouchers-view')
                        ->with('wallet_voucher_details' , $wallet_voucher_details);

        } else {

            return redirect()->route('admin.wallet_vouchers.index')->with('flash_error',tr('admin_wallet_voucher_not_found'));

        }
    
    }

    /**
     * @method custom_wallet_vouchers_delete
     *
     * @uses To delete the wallet_voucher details based on selected wallet_voucher id
     *
     * @created Vidhya
     *
     * @updated Vidhya
     *
     * @param integer $wallet_voucher_id
     * 
     * @return response of success/failure details
     *
     */

    public function  custom_wallet_vouchers_delete($wallet_voucher_id) {

        try {

            DB::beginTransaction();

            $wallet_voucher_details = CustomWalletVoucher::find($wallet_voucher_id);

            if(count($wallet_voucher_details) == 0) {

                throw new Exception(tr('admin_wallet_voucher_not_found'), 101);
                
            }

            if ($wallet_voucher_details->delete()) {

                DB::commit();

                return back()->with('flash_success',tr('admin_wallet_voucher_deleted_success')); 

            } else {

                throw new Exception(tr('admin_wallet_voucher_delete_error'));

            }

        } catch(Exception $e) {

            DB::rollback();

            return redirect()->route('admin.wallet_vouchers.index')->with('flash_error', $e->getMessage());

        }
   
    }

    /**
     * @method  wallet_vouchers_status
     *
     * @uses To delete the wallet_voucher details based on wallet_voucher id
     *
     * @created Vidhya
     *
     * @updated Vidhya
     *
     * @param integer $wallet_voucher_id
     * 
     * @return response success/failure message
     *
     */
    public function custom_wallet_vouchers_status($wallet_voucher_id) {

        try {

            DB::beginTransaction();

            $wallet_voucher_details = CustomWalletVoucher::find($wallet_voucher_id);

            if(count($wallet_voucher_details) == 0) {

                throw new Exception(tr('admin_wallet_voucher_not_found'), 101);
                
            }

            $wallet_voucher_details->status = $wallet_voucher_details->status ? DECLINED : APPROVED;

            $wallet_voucher_details->save();

            DB::commit();

            $message = $wallet_voucher_details->status ? tr('admin_wallet_voucher_approve_success') : tr('admin_wallet_voucher_decline_success');

            return redirect()->back()->with('flash_success', $message);


        } catch(Exception $e) {

            DB::rollback();

            return redirect()->route('admin.wallet_vouchers.index')->with('flash_error', $e->getMessage());

        }

    }

    /**
     * @method custom_wallet_vouchers_payments
     *
     * @uses Get the wallet_vouchers list
     *
     * @created vidhya
     *
     * @updated vidhya 
     *
     * @param 
     * 
     * @return view page
     *
     */

    public function custom_wallet_payments() {

        $payments = CustomWalletPayment::orderBy('created_at' , 'desc')->paginate(10);

        $wallet_revenue = CustomWalletPayment::where('wallet_type', CW_WALLET_TYPE_DIRECT)->where('status', YES)->sum('paid_amount');

        $wallet_revenue = $wallet_revenue ?: 0.00;

        $payment_count = CustomWalletPayment::count(); 

        return view('admin.wallet_vouchers.payments')
                     ->with('page','payments')
                    ->with('sub_page','wallet-payments')
                    ->with('payments' , $payments)
                    ->with('payment_count', $payment_count)
                    ->with('wallet_revenue', $wallet_revenue); 

    }

}
