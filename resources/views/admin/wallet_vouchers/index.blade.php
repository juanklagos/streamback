@extends('layouts.admin')

@section('title', tr('wallet_vouchers'))

@section('content-header', tr('wallet_vouchers'))

@section('breadcrumb')

    <li class="active"><i class="fa fa-building"></i> {{tr('wallet_vouchers')}}</li>

@endsection

@section('content')

    <div class="row">

        <div class="col-xs-12">

            <div class="box box-default info-box" >

                <div class="box-header with-border">

                    <h4 class="box-header-h4">

                        <b>{{tr('wallet_vouchers')}}</b>

                        <a href="{{route('admin.wallet_vouchers.create')}}" class="btn btn-primary pull-right">{{tr('add_wallet_voucher')}}</a>

                    </h4>

                </div>

                <div class="box-body table-responsive">

                    <table id="example1" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                              <th>#{{tr('id')}}</th>
                              <th>{{tr('name')}}</th>
                              <th>{{tr('voucher_code')}}</th>
                              <th>{{tr('amount')}}</th>
                              <!-- <th>{{tr('no_of_users_limit')}}</th> -->
                              <!-- <th>{{tr('remaining_count')}}</th> -->
                              <th>{{tr('used_count')}}</th>
                              <th>{{tr('username')}}</th>
                              <!-- <th>{{tr('status')}}</th> -->
                              <th>{{tr('action')}}</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($wallet_vouchers as $i => $wallet_voucher_details)
                    
                                <tr>
                                    <td>{{$i+1}}</td>
                                    <td>
                                        <a href="{{route('admin.wallet_vouchers.view', $wallet_voucher_details->id)}}">
                                            {{$wallet_voucher_details->name}}
                                        </a>

                                    </td>
                                   
                                    <td>{{$wallet_voucher_details->voucher_code}}</td>
                                    <td>{{Setting::get('currency')}} {{$wallet_voucher_details->amount}}</td>
                                    <!-- <td>{{$wallet_voucher_details->total_count}}</td> -->
                                    <!-- <td>{{$wallet_voucher_details->remaining_count}}</td> -->
                                    <!-- <td>{{$wallet_voucher_details->used_count}}</td> -->

                                    <td>
                                        
                                        @if($wallet_voucher_details->used_count == 0)

                                            <span><i class="fa fa-times fa-2x text-danger"></i></span>

                                        @else

                                            <span><i class="fa fa-check fa-2x text-success"></i></span>

                                        @endif
                                    </td>

                                    <td>
                                        @if($wallet_voucher_details->used_count >= 1)

                                            <a href="{{route('admin.users.view', $wallet_voucher_details->user_id)}}">{{$wallet_voucher_details->username}}</a>

                                        @else

                                            -

                                        @endif
                                    </td>
                                    
                                    <td>

                                        <div class="btn-group">
                                            
                                            <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{tr('action')}}
                                                <span class="caret"></span>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu">

                                                <li>
                                                   
                                                    <a href="{{route('admin.wallet_vouchers.view', array('id' => $wallet_voucher_details->id))}}">
                                                        <b>{{tr('view')}}</b>
                                                    </a>

                                                </li>

                                                <li>
                                                    @if(Setting::get('admin_delete_control'))
                                                        <a href="javascript:;" class="btn disabled">
                                                            <b>{{tr('edit')}}</b>
                                                        </a>
                                                    @else
                                                        <a href="{{route('admin.wallet_vouchers.edit', array('id' => $wallet_voucher_details->id))}}">
                                                            <b>{{tr('edit')}}</b>
                                                        </a>
                                                    @endif
                                                </li>

                                                @if($wallet_voucher_details->used_count == 0)

                                                <li>
                                                    @if($wallet_voucher_details->status == APPROVED)

                                                        <a class="dropdown-item" href="{{route('admin.wallet_vouchers.status' , $wallet_voucher_details->id)}}" onclick="return confirm(&quot;{{$wallet_voucher_details->name}} - {{tr('admin_wallet_voucher_decline_confirmation')}}&quot;);">
                                                            <b>{{tr('decline')}}</b>
                                                        </a> 
                           
                                                    @else

                                                  
                                                        <a class="dropdown-item" href="{{route('admin.wallet_vouchers.status' , $wallet_voucher_details->id)}}">
                                                            <b>{{tr('approve')}}</b>
                                                        </a> 

                                                    @endif 

                                                </li>


                                                <li>

                                                    @if(Setting::get('admin_delete_control'))
                                                       
                                                        <a href="javascript:;" class="btn disabled">
                                                            <b>{{tr('delete')}}</b>
                                                        </a>

                                                    @else
                                                        <a onclick="return confirm(&quot;{{tr('admin_wallet_voucher_delete_confirmation' , $wallet_voucher_details->name)}}&quot;);" href="{{route('admin.wallet_vouchers.delete', $wallet_voucher_details->id)}}"><b>{{tr('delete')}}</b></a>

                                                    @endif

                                                </li>                                

                                                @endif

                                            </ul>

                                        </div>
                                        
                                    </td>
                                
                                </tr>

                            @endforeach

                        </tbody>
                    
                    </table>

                </div>

            </div>
        </div>
    </div>

@endsection