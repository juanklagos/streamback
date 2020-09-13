@extends('layouts.admin')

@section('title', tr('wallet_payments'))

@section('content-header',tr('wallet_payments'))

@section('breadcrumb')

    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li><a href="{{route('admin.revenue.dashboard')}}"><i class="fa fa-money"></i> {{tr('payments')}}</a></li>

    <li class="active"><i class="fa fa-credit-card"></i> {{tr('wallet_payments')}}</li>
@endsection

@section('content')

	<div class="row">

        <div class="col-xs-12">

          	<div class="box box-primary">

	          	<div class="box-header label-primary">
	                  
	                <b style="font-size:18px;">{{tr('wallet_payments')}} - {{Setting::get('currency', '$')}} {{$wallet_revenue}}</b>

	            </div>

	            <div class="box-body  table-responsive">
	          
	            	@if(count($payments) > 0)

		              	<table id="datatable-withoutpagination" class="table table-bordered table-striped">

							<thead>
							    <tr>
							      <th>{{tr('id')}}</th>
							      <th>{{tr('username')}}</th>
							      <th>{{tr('voucher_code')}}</th>
							      <th>{{tr('payment_mode')}}</th>
							      <th>{{tr('payment_id')}}</th>
							      <!-- <th>{{tr('coupon_code')}}</th> -->
							      <!-- <th>{{tr('coupon_amount')}}</th> -->
							      <th>{{tr('amount')}}</th>
							      <th>{{tr('paid_amount')}}</th>
							      <!-- <th>{{tr('is_coupon_applied')}}</th> -->
							      <th>{{tr('paid_date')}}</th>
							      <th>{{tr('status')}}</th>
							    </tr>
							</thead>

							<tbody>

								@foreach($payments as $i => $payment_details)
									
								    <tr>
								      	<td>{{showEntries($_GET, $i+1)}}</td>

								      	<td>
								      		<a href="{{route('admin.users.view' , ['user_id' => $payment_details->user_id] )}}"> 
								      			{{($payment_details->user) ? $payment_details->user->name : ''}} 
								      		</a>
								      	</td>
								      	<td>
								      		{{$payment_details->voucher_code ?: "-"}}
								      	</td>
								      	<td class="text-capitalize">{{$payment_details->payment_mode ? $payment_details->payment_mode : 'free-plan'}}</td>

								      	<td>{{$payment_details->payment_id}}</td>

								      	<td>{{Setting::get('currency')}} {{$payment_details->actual_amount ?: "0.00" }}</td>

								      	<td>{{Setting::get('currency')}} {{$payment_details->amount ?: "0.00" }}</td>

								      	<td>{{date('Y-m-d' , strtotime($payment_details->paid_date))}}</td>
								      	
								      	<td>
								      		@if($payment_details->status)
											<span class="label label-success">{{tr('paid')}}</span>
											@else
											<span class="label label-danger">{{tr('not_paid')}}</span>
											@endif
								      	</td>
								    </tr>					
								@endforeach

							</tbody>
						
						</table>

						<div align="right" id="paglink"><?php echo $payments->links(); ?></div>

					@else

						<h3 class="no-result">{{tr('no_result_found')}}</h3>

					@endif
	            </div>

          	</div>

      	</div>

    </div>

@endsection


@section('scripts')

<script type="text/javascript">


$(document).ready(function() {
    $('#example3').DataTable( {
        "processing": true,
        "serverSide": true,
	    "bLengthChange": false,
        "ajax": "{{route('admin.ajax.user-payments')}}",
        "deferLoading": "{{$payment_count > 0 ? $payment_count : 0}}"
    } );
} );
</script>

@endsection