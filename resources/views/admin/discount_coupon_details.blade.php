@extends('admin.app')

@section('title' , __('messages.discount_coupon_details'))

@section('content')
        <div id="tableSimple" class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>{{ __('messages.discount_coupon_details') }}</h4>
                </div>
            </div>
        </div>
        <div class="widget-content widget-content-area">
            <div class="table-responsive"> 
                <table class="table table-bordered mb-4">
                    <tbody>
                            <tr>
                                <td class="label-table" > {{ __('messages.code') }}</td>
                                <td>
                                    {{ $data['coupon']['code'] }}
                                </td>
                            </tr>
                            <tr>
                                <td class="label-table" > {{ __('messages.discount_value') }}</td>
                                <td>
                                    {{ $data['coupon']['value'] }}%
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="label-table" > {{ __('messages.period') }} ( {{ __('messages.days') }} ) </td>
                                <td>
                                    {{ $data['coupon']['period'] }} {{ __('messages.days') }}
                                </td>
                            </tr>    

                            <tr>
                                <td class="label-table" > {{ __('messages.max_discount') }} ( {{ __('messages.dinar') }} ) </td>
                                <td>
                                    {{ $data['coupon']['max_discount'] }} {{ __('messages.dinar') }}
                                </td>
                            </tr>  

                            <tr>
                                <td class="label-table" > {{ __('messages.status') }} </td>
                                <td>
                                    @if($data['status'])
                                    <span class="badge outline-badge-success">
                                        {{ __('messages.valid') }}
                                    </span>
                                    @else
                                    <span class="badge outline-badge-danger">
                                        {{ __('messages.not_valid') }}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            
                    </tbody>
                </table>
            </div>
        </div>
    </div>  
    
@endsection