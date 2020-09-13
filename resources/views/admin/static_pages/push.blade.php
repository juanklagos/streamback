@extends('layouts.admin')

@section('title', tr('custom_push'))

@section('content-header', tr('custom_push'))

@section('breadcrumb')
    <li><a href="{{route('admin.dashboard')}}"><i class="fa fa-dashboard"></i>{{tr('home')}}</a></li>
    <li class="active"><i class="fa fa-send"></i> {{tr('custom_push')}}</li>
@endsection

@section('content')

    <div class="row">

        <div class="col-md-10">
            
            <div class="box box-info">

                <div class="box-header">
                    <p class="help-note">Note: {{tr('custom_push_note')}}</p>
                </div>

                @if($is_push_enabled)

                    <form action="{{route('admin.send.push')}}" method="POST" enctype="multipart/form-data" role="form">

                @else 

                    <form action="#not-enabled">

                @endif

                    <div class="box-body">

                        <div class="form-group">
                            
                            <label for="message">{{tr('message')}}</label>

                            <input type="text" required name="message" class="form-control" id="message" placeholder="{{tr('enter')}} {{tr('message')}}">
                        </div>

                    </div>

                    @if($is_push_enabled) 

                        <div class="box-footer">
                            <button type="reset" class="btn btn-danger">{{tr('cancel')}}</button>
                            <button type="submit" class="btn btn-success pull-right">{{tr('submit')}}</button>
                        </div>

                    @else

                        <div class="col-md-12">

                            <h4 class="text-danger">
                            {{tr('push_notification_configuration_failed')}}</h4>

                            <p><b>{{tr('FCM_SERVER_KEY')}}:</b> {{envfile('FCM_SERVER_KEY') ?: "NOT UPDATED"}}</p>

                            <p><b>{{tr('FCM_SENDER_ID')}}:</b> {{envfile('FCM_SENDER_ID') ?: "NOT UPDATED"}}</p>

                        </div>

                        <div class="box-footer">
                            <button type="reset" disabled class="btn btn-danger">{{tr('cancel')}}</button>
                            <button type="button" disabled class="btn btn-success pull-right">{{tr('submit')}}</button>
                        </div>

                    @endif

                    
                </form>
            
            </div>

        </div>

    </div>

@endsection