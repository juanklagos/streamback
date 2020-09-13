<form  action="{{$wallet_voucher_details->id ? route('admin.wallet_vouchers.save') : route('admin.wallet_vouchers.generate')}}" method="POST" enctype="multipart/form-data" role="form">

    <input type="hidden" name="wallet_voucher_id" value="{{$wallet_voucher_details->id}}">

    <div class="box-body">

        <div class="col-md-6">

            <div class="form-group">

                <label for="name"> * {{tr('name')}}</label>

                <input type="text" name="name" role="name" min="5" max="20" class="form-control" value="{{ old('name')?: $wallet_voucher_details->name }}" required placeholder="{{tr('name')}}">
            </div> 

            <input type="hidden" name="per_user_limit" value="1">

            <input type="hidden" name="total_count" value="1">

            <div class="form-group">

                <label for="amount"> * {{tr('amount')}}</label>

                <input type="number" name="amount" min="1" max="5000" step="any" class="form-control" placeholder="{{tr('amount')}}" value="{{old('amount')?: $wallet_voucher_details->amount}}" required title="{{tr('only_number')}}">

            </div>

        </div>


        <div class="col-md-6">

            <div class="form-group">
                <label for="expiry_date"> * {{tr('expiry_date')}}</label>
                <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="{{tr('expiry_date_coupon')}}" value="{{old('expiry_date')?: ($wallet_voucher_details->expiry_date ? date('d-m-Y', strtotime($wallet_voucher_details->expiry_date)) : '')}}" required readonly>
            </div>


            @if($wallet_voucher_details->id)

            <div class="form-group">

                <label for="voucher_code"> * {{tr('voucher_code')}}</label>

                <input type="hidden" name="voucher_code" value="{{old('voucher_code')?: $wallet_voucher_details->voucher_code}}">

                <input type="text" disabled class="form-control" value="{{old('voucher_code')?: $wallet_voucher_details->voucher_code}}" placeholder="{{tr('voucher_code')}}" title="{{tr('validation')}}">
            </div>

            @else

                <div class="form-group">
                    
                    <label for="how_many_vouchers"> * {{tr('how_many_vouchers')}}</label>
                    
                    <input type="number" pattern="[0-9]{1,4}" name="how_many_vouchers" class="form-control" placeholder="{{tr('how_many_vouchers')}}" value="{{old('how_many_vouchers')?: ''}}" required title="{{tr('how_many_vouchers')}}">

                </div>

            @endif

        </div>

    
        <div class="clearfix"></div>

        <div class="col-md-12">

            <div class="form-group">

                <label for="description"> {{tr('description')}}</label>

                <textarea id="ckeditor" name="description" class="form-control" placeholder="{{tr('enter_text')}}">{{old('description') ?: $wallet_voucher_details->description}}</textarea>
                
            </div>

        </div>


    </div>

    <div class="box-footer">

        <button type="reset" class="btn btn-danger">{{tr('reset')}}</button>

        <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>

    </div>

</form>