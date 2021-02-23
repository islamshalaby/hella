@extends('admin.app')

@section('title' , __('messages.edit_coupon'))

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.edit_coupon') }}</h4>
                 </div>
        </div>
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="list-unstyled mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="" method="post" enctype="multipart/form-data" >
            @csrf
                       
            <div class="form-group mb-4">
                <label for="code">{{ __('messages.code') }}</label>
                <input required type="text" name="code" class="form-control" id="code" placeholder="{{ __('messages.code') }}" value="{{ $data['coupon']['code'] }}" >
            </div>
            <div class="form-group mb-4">
                <label for="value">{{ __('messages.discount_value') }} ( % )</label>
                <input required type="text" name="value" class="form-control" id="value" placeholder="{{ __('messages.discount_value') }}" value="{{ $data['coupon']['value'] }}" >
            </div>
            <div class="form-group mb-4">
                <label for="period">{{ __('messages.period') }} ( {{ __('messages.days') }} )</label>
                <input required type="number" name="period" class="form-control" id="period" placeholder="{{ __('messages.period') }}" value="{{ $data['coupon']['period'] }}" >
            </div>
            <div class="form-group mb-4">
                <label for="max_discount">{{ __('messages.max_discount') }} ( {{ __('messages.dinar') }} )</label>
                <input required type="number" step="any" min="0" name="max_discount" class="form-control" id="max_discount" placeholder="{{ __('messages.max_discount') }}" value="{{ $data['coupon']['max_discount'] }}" >
            </div>

            <input type="submit" value="{{ __('messages.submit') }}" class="btn btn-primary">
        </form>
    </div>
@endsection