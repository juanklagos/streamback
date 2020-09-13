@extends('layouts.admin')

@section('title',tr('referral_users'))

@section('content-header',tr('referral_users'))


@section('content')

<div class="row">
    
    @if(!empty($flash_success))
        <div class="alert alert-success"> {{ $flash_success }}</div>
    @endif

    <h4>{{tr('total_users')}} - {{$users_count}}</h4>
</div>

@endsection