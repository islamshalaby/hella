@extends('admin.app')

@section('title' , __('messages.sub_category_edit'))

@push('scripts')
    <script>
        $("#category").on("change", function() {
            $('select#brand').html("")
            var categoryId = $(this).find("option:selected").val();
            
            $.ajax({
                url : "/admin-panel/sub_categories/fetchbrand/" + categoryId,
                type : 'GET',
                success : function (data) {
                    $('#brandsParent').show()
                    
                    data.forEach(function (brand) {
                        $('select#brand').append(
                            "<option value='" + brand.id + "'>" + brand.title_en + "</option>"
                        )
                    })
                }
            })
        })

        var category = $("#category").find("option:selected").val(),
            brandId = "{{ $data['sub_category']['brand_id'] }}"
            
        $.ajax({
            url : "/admin-panel/sub_categories/fetchbrand/" + category,
            type : 'GET',
            success : function (data) {
                $('#brandsParent').show()
                data.forEach(function (brand) {
                    var selected = ""
                    if (brandId == brand.id) {
                        selected = "selected"
                    }
                    $('select#brand').append(
                        "<option " + selected + " value='" + brand.id + "'>" + brand.title_en + "</option>"
                    )
                })
            }
        })
    </script>
@endpush

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.sub_category_edit') }}</h4>
                 </div>
        </div>
        <form action="" method="post" enctype="multipart/form-data" >
            @csrf


            <div class="form-group mb-4">
                <label for="title_en">{{ __('messages.title_en') }}</label>
                <input required type="text" name="title_en" class="form-control" id="title_en" placeholder="{{ __('messages.title_en') }}" value="{{ $data['sub_category']['title_en'] }}" >
            </div>
            <div class="form-group mb-4">
                <label for="title_ar">{{ __('messages.title_ar') }}</label>
                <input required type="text" name="title_ar" class="form-control" id="title_ar" placeholder="{{ __('messages.title_ar') }}" value="{{ $data['sub_category']['title_ar'] }}" >
            </div>
            <div class="form-group">
                <label for="category">{{ __('messages.category') }}</label>
                <select id="category" name="category_id" class="form-control">
                    <option selected>{{ __('messages.select') }}</option>
                    @foreach ( $data['categories'] as $category )
                    <option {{ $category->id == $data['sub_category']['category_id'] ? 'selected' : '' }} value="{{ $category->id }}">{{ App::isLocale('en') ? $category->title_en : $category->title_ar }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="sub_category">{{ __('messages.sub_category') }}</label>
                <select id="sub_category" name="sub_category_id" class="form-control">
                    <option disabled selected>{{ __('messages.select') }}</option>
                    @foreach ( $data['sub_categories'] as $category )
                    <option {{ $category->id == $data['sub_category']['sub_category_id'] ? 'selected' : '' }} value="{{ $category->id }}">{{ App::isLocale('en') ? $category->title_en : $category->title_ar }}</option>
                    @endforeach
                </select>
            </div>


            <input type="submit" value="{{ __('messages.submit') }}" class="btn btn-primary">
        </form>
    </div>
@endsection