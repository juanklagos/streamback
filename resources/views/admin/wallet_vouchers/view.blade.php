@extends('layouts.admin')

@section('title', tr('view_wallet_voucher'))

@section('content-header', tr('view_wallet_voucher'))

@section('breadcrumb')

    <li><a href="{{route('admin.wallet_vouchers.index')}}"><i class="fa fa-book"></i> {{tr('wallet_vouchers')}}</a></li>

    <li class="active"> {{tr('view_wallet_voucher')}}</li>

@endsection

@section('styles')

<style>
    .products-list .product-info {
        margin-left: 0px;

    }
</style>

@endsection

@section('content')
    
    <div class="row">

        <div class="col-md-12">

            <div class="box box-default info-box" >

                <div class="box-header with-border">

                    <h4 class="box-header-h4">

                        <b>{{$wallet_voucher_details->name}}</b>

                        <a href="{{route('admin.wallet_vouchers.edit',$wallet_voucher_details->id)}}" class="btn btn-primary pull-right">
                            {{tr('edit_wallet_voucher')}}
                        </a>

                    </h4>

                </div>

                <div class="box-body">

                    <div class="col-md-6">

                        <ul class="products-list product-list-in-box">

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('name')}}
                                        <span class="product-description">
                                            {{$wallet_voucher_details->name}}
                                        </span>
                                    </a>
                                </div>
                            </li>

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('voucher_code')}}
                                        <span class="product-description">
                                            {{$wallet_voucher_details->voucher_code}}
                                        </span>
                                    </a>
                                </div>
                            </li>

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('expiry_date')}}
                                        <span class="product-description">
                                            {{$wallet_voucher_details->expiry_date}}
                                        </span>
                                    </a>
                                </div>
                            </li>

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('amount')}}
                                        <span class="product-description">
                                            {{Setting::get('currency')}} {{$wallet_voucher_details->amount}}
                                        </span>
                                    </a>
                                </div>
                            </li>

                            <!-- <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('total_count')}}
                                        <span class="product-description">
                                            {{$wallet_voucher_details->total_count}}
                                        </span>
                                    </a>
                                </div>
                            </li> -->

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('used_count')}}

                                        <br>

                                        <span class="product-description">

                                            @if($wallet_voucher_details->used_count == 0)

                                                <span><i class="fa fa-times fa-2x text-danger"></i></span>

                                            @else

                                                <span><i class="fa fa-check fa-2x text-success"></i></span>

                                            @endif

                                        </span>
                                    </a>
                                </div>
                            </li>

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('username')}}
                                        <span class="product-description">
                                            @if($wallet_voucher_details->used_count >= 1)

                                                <a href="{{route('admin.users.view', $wallet_voucher_details->user_id)}}" class="text-success">{{$wallet_voucher_details->username}}</a>

                                            @else

                                                -

                                            @endif
                                        </span>
                                    </a>
                                </div>
                            </li>

                            <!-- <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('remaining_count')}}
                                        <span class="product-description">
                                            {{$wallet_voucher_details->remaining_count}}
                                        </span>
                                    </a>
                                </div>
                            </li> -->
                            
                        </ul>

                    </div>

                    <div class="col-md-6">

                        <!-- <img class="img-responsive" src="{{$wallet_voucher_details->picture}}" alt="Photo"> -->

                        <ul class="products-list product-list-in-box">

                            <li class="item">
                               
                                <div class="product-info">
                                    <a href="javascript:void(0)" class="product-title">
                                        {{tr('description')}}
                                        <span class="product-description">
                                            <?php echo $wallet_voucher_details->description; ?>
                                        </span>
                                    </a>
                                </div>
                            </li>

                        </ul>

                    </div>

                </div>

                <div class="box-footer">

                    @if($wallet_voucher_details->status == APPROVED)

                        <a class="btn bg-yellow bg-flat" href="{{route('admin.wallet_vouchers.status' , $wallet_voucher_details->id)}}" onclick="return confirm(&quot;{{$wallet_voucher_details->name}} - {{tr('admin_wallet_voucher_decline_confirmation')}}&quot;);">
                            {{tr('decline')}}
                        </a>

                    @else

                        <a class="btn bg-green bg-flat" href="{{route('admin.wallet_vouchers.status' , $wallet_voucher_details->id)}}">
                            {{tr('approve')}}
                        </a> 

                    @endif

                    <a class="btn bg-maroon bg-flat" onclick="return confirm(&quot;{{tr('admin_wallet_voucher_delete_confirmation' , $wallet_voucher_details->name)}}&quot;);" href="{{route('admin.wallet_vouchers.delete', $wallet_voucher_details->id)}}">
                        <b>{{tr('delete')}}</b>
                    </a>

                </div>

            </div>
            <!-- /.box -->
        </div>

    </div>
@endsection


