@extends('admin.app')

@section('title' , __('messages.ad_edit'))

@push('scripts')
    <script>
        var language = "{{ Config::get('app.locale') }}"
        $("#ad_type").on("change", function() {
            if(this.value == 2) {
                $(".outside").show()
                $('.productsParent').hide()
                $('select#products').prop("disabled", true)
                $(".outside input").prop("disabled", false)
                $("#link_type").parent('.form-group').hide()
                $("#link_type").prop('disabled', true)
                $(".inside").hide()
            }else {
                $(".outside").hide()
                $(".outside input").prop("disabled", true)
                $("#link_type").parent('.form-group').show()
                $("#link_type").prop('disabled', false)
                $('select#products').html("")
            }
        })

        @if ($data['ad']['type'] == 1)
        var contentId = {{ $data['ad']['content'] }}
            @if($data['ad']['content_type'] == 1)
                $.ajax({
                    url : "/admin-panel/ads/fetchproducts",
                    type : 'GET',
                    success : function (data) {
                        $('.productsParent').show()
                        $('select#products').prop("disabled", false)
                        $('select#products').siblings('label').text(productSelect)
                        data.forEach(function (product) {
                            var productName = product.title_en,
                                selected = ""

                            if (contentId == product.id) {
                                selected = 'selected'
                            }
                            if (language == 'ar') {
                                productName = product.title_ar
                            }
                            $('select#products').append(
                                "<option " + selected + " value='" + product.id + "'>" + productName + "</option>"
                            )
                        })
                    }
                })
            @endif
            var itemSelect = {{ $data['ad']['content_type'] }}
            
            @if($data['ad']['content_type'] == 2)
            $.ajax({
                url : "/admin-panel/ads/fetchcategories/",
                type : 'GET',
                success : function (data) {
                    var content = {{ $data['ad']['content'] }}
                    $('.productsParent').show()
                    $('select#products').prop("disabled", false)
                    $('select#products').siblings('label').text(categorySelect)
                    data.forEach(function (category) {
                        var categoryName = category.title_en,
                            selected = ""
                        if (language == 'ar') {
                            categoryName = category.title_ar
                        }
                        if (content == category.id) {
                            selected = "selected"
                        }
                        $('select#products').append(
                            "<option " + selected + " value='" + category.id + "'>" + categoryName + "</option>"
                        )
                    })
                }
            })
            @endif
        @endif

        var language = "{{ Config::get('app.locale') }}",
            productSelect = "{{ __('messages.product') }}",
            categorySelect = "{{ __('messages.category') }}"

        $("#link_type").on("change", function() {
            $(".inside").show()
            $('select#products').html("")
            
            if ($(this).val() == 1) {
                $.ajax({
                    url : "/admin-panel/ads/fetchproducts",
                    type : 'GET',
                    success : function (data) {
                        
                        $('.productsParent').show()
                        $('select#products').prop("disabled", false)
                        data.forEach(function (product) {
                            var productName = product.title_en
                            if (language == 'ar') {
                                productName = product.title_ar
                            }
                            $('select#products').append(
                                "<option value='" + product.id + "'>" + productName + "</option>"
                            )
                        })
                    }
                })
            }else if($(this).val() == 2) {
                $('select#products').html('')
                $.ajax({
                    url : "/admin-panel/ads/fetchcategories",
                    type : 'GET',
                    success : function (data) {
                        console.log(data)
                        $('.productsParent').show()
                        $('select#products').prop("disabled", false)
                        $('select#products').siblings('label').text(categorySelect)
                        data.forEach(function (category) {
                            var categoryName = category.title_en
                            if (language == 'ar') {
                                categoryName = category.title_ar
                            }
                            $('select#products').append(
                                "<option value='" + category.id + "'>" + categoryName + "</option>"
                            )
                        })
                    }
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
                        <h4>{{ __('messages.ad_edit') }}</h4>
                 </div>
        </div>
        <form action="" method="post" enctype="multipart/form-data" >
            @csrf
            <div class="form-group mb-4">
                <label for="">{{ __('messages.current_image') }}</label><br>
                <img src="https://res.cloudinary.com/dzqo7w4cp/image/upload/w_100,q_100/v1600733461/{{ $data['ad']['image'] }}"  />
            </div>
            <div class="custom-file-container" data-upload-id="myFirstImage">
                <label>{{ __('messages.change_image') }} ({{ __('messages.single_image') }}) <a href="javascript:void(0)" class="custom-file-container__image-clear" title="Clear Image">x</a></label>
                <label class="custom-file-container__custom-file" >
                    <input type="file" name="image" class="custom-file-container__custom-file__custom-file-input" accept="image/*">
                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                </label>
                <div class="custom-file-container__image-preview"></div>
            </div>

            <div class="form-group">
                <label for="sel1">{{ __('messages.ad_type') }}</label>
                <select id="ad_type" name="type" class="form-control">
                    <option selected>{{ __('messages.select') }}</option>
                    <option {{ $data['ad']['type'] == 2 ? 'selected' : '' }} value="2">{{ __('messages.outside_the_app') }}</option>
                    <option {{ $data['ad']['type'] == 1 ? 'selected' : '' }} value="1">{{ __('messages.inside_the_app') }}</option>
                </select>
            </div>
                   
            <div style="display: {{ $data['ad']['type'] == 2 ? 'none' : '' }}" class="form-group">
                <label for="link_type">{{ __('messages.link_type') }}</label>
                <select {{ $data['ad']['type'] == 2 ? 'disabled' : '' }} id="link_type" name="content_type" class="form-control">
                    <option selected>{{ __('messages.select') }}</option>
                    <option {{ $data['ad']['content_type'] == 1 ? 'selected' : '' }} value="1">{{ __('messages.product') }}</option>
                    <option {{ $data['ad']['content_type'] == 2 ? 'selected' : '' }} value="2">{{ __('messages.category') }}</option>
                </select>
            </div> 

            <div style="display: {{ $data['ad']['type'] == 1 ? 'none' : '' }}" class="form-group mb-4 outside">
                <label for="link">{{ __('messages.link') }}</label>
                <input required type="text" name="content" class="form-control" id="link" placeholder="{{ __('messages.link') }}" value="{{ $data['ad']['content'] }}" >
            </div>
            

            <div style="display: none" class="form-group productsParent">
                <label for="products">{{ __('messages.product') }}</label>
                <select id="products" class="form-control" name="content">
                </select>
            </div>
            
            <input type="submit" value="{{ __('messages.submit') }}" class="btn btn-primary">
        </form>
    </div>
@endsection