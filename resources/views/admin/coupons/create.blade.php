@extends('layouts.admin')

@section('title',tr('add_coupon'))

@section('content-header',tr('add_coupon'))

@section('breadcrumb')

	<li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>

	<li><a href="{{route('admin.coupons.index')}}"><i class="fa fa-gift"></i>{{tr('coupons')}}</a></li>

	<li class="active">{{tr('add_coupon')}}</li>

@endsection

@section('content')

	<div class="row">

		<div class="col-md-10">

			<div class="box box-primary">

				<div class="box-header label-primary">

					<b style="font-size: 18px">{{tr('add_coupon')}}</b>

					<a href="{{route('admin.coupons.index')}}" class="btn btn-default pull-right">{{tr('coupons')}}</a>

				</div>

				@include('admin.coupons._form')

			</div>

		</div>

	</div>
	
@endsection