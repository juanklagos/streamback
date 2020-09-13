@extends('layouts.admin')

@section('title', tr('referrals'))

@section('content-header', tr('referrals'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.users.index')}}"><i class="fa fa-user"></i> {{tr('users')}}</a></li>
    
    <li class="active"><i class="fa fa-key"></i> {{tr('referrals')}}</li>
@endsection

@section('after-styles')

<style>

.subscription-image {
	overflow: hidden !important;
	position: relative !important;
	height: 15em !important;
	background-position: center !important;
	background-repeat: no-repeat !important;
	background-size: cover !important;
	margin: 0 !important;
	width: 100%;
}

.subscription-desc {
	min-height: 10em !important;
	max-height: 10em !important;
	overflow: scroll !important;
	margin-bottom: 10px !important;
}

</style>

@endsection

@section('content')
	
	<div class="row">

		<div class="row col-md-12">

	    		<h4 class="col-md-3">{{tr('total')}}: {{formatted_amount($wallet_details->total)}}</h4>

	    		<h4 class="col-md-3">{{tr('remaining')}}: <span class="text-green"> <b>{{formatted_amount($wallet_details->remaining)}}</b></span></h4>

	    		<h4 class="col-md-3">{{tr('used')}}: {{formatted_amount($wallet_details->used)}}</h4>

	    	</div>

        <div class="col-xs-12">

        	

          <div class="box">

				<div class="box-header label-primary">

	          		<b style="font-size:18px;">
	          			{{tr('referrals')}} - 
	          			<a style="color: white;text-decoration: underline;" href="{{route('admin.users.view' , ['user_id' => $user_details->id])}}"> 
	          				{{$user_details ? $user_details->name : ""}}
	          			</a>
	          		</b>
	              
	            </div>

            <div class="box-body table-responsive">

            	@if(count($wallet_histories) > 0)

	              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

						<thead>
						    <tr>
						      <th>{{tr('id')}}</th>
						      <th>{{tr('payment_id')}}</th>
						      <th>{{tr('message')}}</th>
						      <th>{{tr('payment_mode')}}</th>
						      <th>{{tr('paid_amount')}}</th>
						      <th>{{tr('date')}}</th>
						      <th>{{tr('status')}}</th>
						    </tr>
						</thead>

						<tbody>

							@foreach($wallet_histories as $i => $wallet_history_details)

							    <tr>
							      	<td>{{$i+1}}</td>
							      	
							      	<td>{{$wallet_history_details->payment_id}}</td>

							      	<td>{{$wallet_history_details->message}}</td>

							      	<td>{{$wallet_history_details->payment_mode}}</td>

							      	<td>
							      		@if($wallet_history_details->transaction_type == CW_ADD)
							      			<span class="text-green">
								      			+ {{formatted_amount($wallet_history_details->amount)}}
								      		</span>
							      		@else
							      			<span class="text-red">
							      				- {{formatted_amount($wallet_history_details->amount)}}
							      			</span>

							      		@endif
							      	</td>

							      	<td>{{common_date($wallet_history_details->paid_date,Auth::guard('admin')->user()->timezone, 'd-m-Y H:i a')}}</td>
							      	
							      	<td class="text-center">

							      		@if($wallet_history_details->status)

							      			<span class="label label-success">{{tr('paid')}}</span>

							      		@else

							      			<span class="label label-warning">{{tr('unpaid')}}</span>

							      		@endif

							      		

							      	</td>
							    </tr>	
				
							@endforeach
						</tbody>
					</table>

					<div align="right" id="paglink"><?php echo $wallet_histories->links(); ?></div>

				@else
					<h3 class="no-result">{{tr('no_result_found')}}</h3>
				@endif

            </div>
          </div>
        </div>
    
    </div>

@endsection