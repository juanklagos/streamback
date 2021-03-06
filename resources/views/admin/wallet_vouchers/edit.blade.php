@extends('layouts.admin')

@section('title', tr('wallet_vouchers'))

@section('content-header', tr('wallet_vouchers'))

@section('breadcrumb')

    <li><a href="{{route('admin.wallet_vouchers.index')}}"><i class="fa fa-file"></i>{{tr('wallet_vouchers')}}</a></li>

    <li class="active">{{tr('edit_wallet_voucher')}}</li>

@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">

            <div class="box box-default info-box" >

                <div class="box-header with-border">

                    <h4 class="box-header-h4">

                        <b>{{tr('edit_wallet_voucher')}}</b>

                        <a href="{{route('admin.wallet_vouchers.index')}}" class="btn btn-primary pull-right">{{tr('wallet_vouchers')}}</a>

                    </h4>

                </div>

                @include('admin.wallet_vouchers._form')
            
            </div>

        </div>

    </div>
   
@endsection

@section('scripts')

    <script src="https://cdn.ckeditor.com/4.5.5/standard/ckeditor.js"></script>

    <script>
        CKEDITOR.replace( 'ckeditor' );
    </script>
@endsection


