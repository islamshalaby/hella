@extends('admin.app')

@section('title' , __('messages.edit_val'))

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.edit_val') }}</h4>
                 </div>
        </div>
        <form action="" method="post" enctype="multipart/form-data" >
            @csrf
            <div class="form-group mb-4">
                <label for="value_en">{{ __('messages.val_en') }}</label>
                <input required type="text" name="value_en" class="form-control" id="value_en" placeholder="{{ __('messages.val_en') }}" value="{{ $data['val']['value_en'] }}" >
            </div>
            <div class="form-group mb-4">
                <label for="value_ar">{{ __('messages.val_ar') }}</label>
                <input required type="text" name="value_ar" class="form-control" id="value_ar" placeholder="{{ __('messages.val_en') }}" value="{{ $data['val']['value_ar'] }}" >
            </div>
            <input type="submit" value="{{ __('messages.submit') }}" class="btn btn-primary">
        </form>
    </div>
@endsection