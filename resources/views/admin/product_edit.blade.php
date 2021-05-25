@extends('admin.app')

@section('title' , __('messages.product_edit'))
@push('styles')
<style>
    .wizard > .content > .body .select2-search input {
        border : none
    }
    input[disabled] {
        background-color: #eeeeee !important;
    }
    input[name="final_price[]"],
    input[name="total_amount[]"],
    input[name="remaining_amount[]"],
    input[name="barcodes[]"],
    input[name="stored_numbers[]"],
    input[disabled] {
        font-size: 10px
    }

    #properties-items .col-sm-5 {
        margin-bottom: 20px
    }
</style>
@endpush
@push('scripts')
    <script>
        var language = "{{ Config::get('app.locale') }}",
            select = "{{ __('messages.select') }}",
            siblingsCont = $("#category_options_sibling").html()
            
        $("#category").on("change", function() {
            $('select#brand').html("")
            
            var categoryId = $(this).find("option:selected").val(),
                productCategoryId = "{{ $data['product']['category_id'] }}"
            if (categoryId == productCategoryId) {
                $("#category_options_sibling .form-group").show()
                $("#category_options_sibling .form-group input").prop("disabled", false)
            }else {
                $("#category_options_sibling .form-group").hide()
                $("#category_options_sibling .form-group input").prop("disabled", true)
            }
            
            
            $.ajax({
                url : "/admin-panel/sub_categories/fetchbrand/" + categoryId,
                type : 'GET',
                success : function (data) {
                    $('#brandsParent').show()
                    $('select#brand').prop("disabled", false)
                    $('#sub_category_select').parent('.form-group').hide()
                    $('select#sub_category_select').prop("disabled", true)
                    $('select#brand').prepend(
                            `<option selected disabled>${select}</option>`
                        )
                    data.forEach(function (brand) {
                        var brandName = brand.title_en
                        if (language == 'ar') {
                            brandName = brand.title_ar
                        }
                        $('select#brand').append(
                            "<option value='" + brand.id + "'>" + brandName + "</option>"
                        )
                    })
                }
            })

            $("#category_options .row").html("")
            $.ajax({
                url : "/admin-panel/products/fetchcategoryoptions/" + categoryId,
                type : 'GET',
                success : function (data) {
                    
                    $("#category_options").show()
                    
                    data.forEach(function (option) {
                        var optionName = option.title_en,
                            elms = "{{ $data['prod_options'] }}",
                            checked = ""

                        if (language == 'ar') {
                            optionName = option.title_ar
                        }
                        if (elms.includes(option.id)) {
                            checked = "checked"
                        }
                        
                        $("#category_options .row").append(`
                        <div class="col-6">
                            <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                              <input ${checked} data-label="${optionName}" value="${option.id}" type="checkbox" class="new-control-input">
                              <span class="new-control-indicator"></span><span class="new-chk-content">${optionName}</span>
                            </label>
                        </div> 
                        `)
                    })
                }
            })

            // fetch sub category by category id
            $('select#sub_category_select').html("")
            $.ajax({
                url : "/admin-panel/products/fetchsubcategorybycategory/" + categoryId,
                type : 'GET',
                success : function (data) {
                    $('#sub_category_select').parent('.form-group').show()
                    $('select#sub_category_select').prop("disabled", false)
                    $('select#sub_category_select').prepend(
                            `<option selected disabled>${select}</option>`
                        )
                    data.forEach(function (subCategory) {
                        var subCategoryName = subCategory.title_en
                        if (language == 'ar') {
                            subCategoryName = subCategory.title_ar
                        }
                        $('select#sub_category_select').append(
                            "<option value='" + subCategory.id + "'>" + subCategoryName+ "</option>"
                        )
                    })
                }
            })
        })
        $("#brand").on("change", function() {
            $('select#sub_category_select').html("")
            var brandId = $(this).find("option:selected").val();
            
            $.ajax({
                url : "/admin-panel/products/fetchsubcategories/" + brandId,
                type : 'GET',
                success : function (data) {
                    $('#sub_category_select').parent('.form-group').show()
                    $('select#sub_category_select').prop("disabled", false)
                    $('select#sub_category_select').prepend(
                            `<option selected disabled>${select}</option>`
                        )
                    data.forEach(function (subCategory) {
                        var subCategoryName = subCategory.title_en
                        if (language == 'ar') {
                            subCategoryName = subCategory.title_ar
                        }
                        $('select#sub_category_select').append(
                            "<option value='" + subCategory.id + "'>" + subCategoryName+ "</option>"
                        )
                    })
                }
            })
        })

        var categoryId = $("#category").find("option:selected").val();
            
            $.ajax({
                url : "/admin-panel/sub_categories/fetchbrand/" + categoryId,
                type : 'GET',
                success : function (data) {
                    $('#brandsParent').show()
                    $('select#brand').prop("disabled", false)
                    
                    $('select#brand').prepend(
                        `<option selected disabled>${select}</option>`
                    )
                    data.forEach(function (brand) {
                        
                        var brandName = brand.title_en
                        if (language == 'ar') {
                            brandName = brand.title_ar
                        }
                        var selected = "",
                            brandId = "{{ $data['product']['brand_id'] }}"
                        if (brandId == brand.id) {
                            selected = "selected"
                        }
                        $('select#brand').append(
                            "<option " + selected + " value='" + brand.id + "'>" + brandName + "</option>"
                        )
                    })
                }
            })

            $.ajax({
                url : "/admin-panel/products/fetchcategoryoptions/" + categoryId,
                type : 'GET',
                success : function (data) {
                    
                    $("#category_options").show()
                    
                    data.forEach(function (option) {
                        var optionName = option.title_en,
                            elms = "{{ $data['prod_options'] }}",
                            checked = ""

                        if (language == 'ar') {
                            optionName = option.title_ar
                        }
                        if (elms.includes(option.id)) {
                            checked = "checked"
                        }
                        
                        $("#category_options .row").append(`
                        <div class="col-6">
                            <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                              <input ${checked} data-label="${optionName}" value="${option.id}" type="checkbox" class="new-control-input">
                              <span class="new-control-indicator"></span><span class="new-chk-content">${optionName}</span>
                            </label>
                        </div> 
                        `)
                    })
                }
            })
            
          
            $.ajax({
                url : "/admin-panel/products/fetchsubcategorybycategory/" + categoryId,
                type : 'GET',
                success : function (data) {
                    $('#sub_category_select').parent('.form-group').show()
                    $('select#sub_category_select').prop("disabled", false)

                    $('select#sub_category_select').prepend(
                            `<option selected disabled>${select}</option>`
                        )
                    
                    data.forEach(function (subCategory) {
                        var subCategoryName = subCategory.title_en
                        if (language == 'ar') {
                            subCategoryName = subCategory.title_ar
                        }
                        var selected = "",
                            subCategoryId = "{{ $data['product']['sub_category_id'] }}"
                        if (subCategoryId == subCategory.id) {
                            selected = "selected"
                        }
                        
                        $('select#sub_category_select').append(
                            "<option " + selected + " value='" + subCategory.id + "'>" + subCategoryName+ "</option>"
                        )
                    })
                }
            })

            var subCategoryId = "{{ $data['product']['sub_category_id'] }}"

            $.ajax({
                url : "/admin-panel/products/fetchsubcategorymultioptions/" + categoryId,
                type : 'GET',
                success : function (data) {
                    
                    $('#multi_options_radio').show()

                    var multiOptionsArr = {{ $data['encoded_multi_options'] }},
                        multiOptionsIds = {{ $data['encoded_multi_options_id'] }},
                        noneString = "{{ __('messages.none') }}"

                    $("#multi_options_radio .row").append(`
                    <div class="col-sm-2 text-option">
                        <div class="n-chk">
                            <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                              <input id="checked_none" checked name="multi_option_id" type="radio" value="none" class="new-control-input all-permisssions">
                              <span class="new-control-indicator"></span><span class="new-chk-content">${noneString}</span>
                            </label>
                        </div>  
                    </div>
                    `)
                    
                    data.forEach(function (option) {
                    
                        var optionName = option.title_en,
                            select = "{{ __('messages.select') }}",
                            anotherChoice = "{{ __('messages.another_choice') }}",
                            checkedOption = "",
                            disabledSelect = "disabled"

                            
                        if (language == 'ar') {
                            optionName = option.title_ar
                        }

                        if (multiOptionsIds.includes(option.id)) {
                            checkedOption = "checked",
                            disabledSelect = ""
                        }
                        var propValOption = ""
                        
                        option.values.forEach(function(propVal) {
                        var optionVal = propVal.value_en,
                            selectedVal = ""
                        if (language == 'ar') {
                            optionVal = propVal.value_ar
                        }

                        if (multiOptionsArr.includes(propVal.id)) {
                            selectedVal = "selected"
                        }
                            propValOption += `
                            <option ${selectedVal} value="${propVal.id}">
                                ${optionVal}
                            </option>
                            `
                        })
                        
                        var propValSelect = `
                        <select id="multi${option.id}" class="form-control multi_tags" ${disabledSelect} multiple name="multi_option_value_id[]">
                            ${propValOption}
                        </select>
                        `
                        $("#multi_options_radio .row").append(`
                        
                        <div class="col-sm-1 text-option">
                            <div class="n-chk">
                                <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                                  <input data-multi="multi${option.id}" ${checkedOption} name="multi_option_id" type="radio" value="${option.id}" class="new-control-input all-permisssions">
                                  <span class="new-control-indicator"></span><span class="new-chk-content">${optionName}</span>
                                </label>
                            </div>  
                        </div>
                        <div class="col-sm-4">${propValSelect}</div>
                        
                        `)
                    })
                    $(".multi_tags").select2({
                        tags: true
                    });
                }
            })

            // enable | disable multi options select
            $("#multi_options_radio .row").on('change', 'input[type="radio"]', function() {
                var multiId = $(this).data('multi')
                if ($(this).is(":checked")) {
                    $("select.multi_tags").prop('disabled', true)
                    $(`#${multiId}`).prop('disabled', false)
                }
            })

            var exampleAppended = $("#example tbody").html()

            // if none checked then show single product elements
            $("#multi_options_radio .row").on("change", "input[type='radio']", function() {
                if ($(this).val() == "none") {
                    console.log("checked")
                    $("#example tbody").html('')
                    $("#multi-options-details").hide()
                    $("#single-details").show()
                    $("#single-discount").show()
                }else {
                    console.log("pspspspsp")
                    $("#multi-options-details").show()
                    $("#single-details").hide()
                    $("#single-discount").hide()
                    $("#example tbody").html(exampleAppended)
                }
            })

            // set multi options values
            @if (count($data['product']->multiOptions) == 0)
            
                $("#multi_options_radio .row").on('change', 'select.multi_tags', function() {
                    if ($("#discount").is(":checked")) {
                        $(".th-discount").show()
                    }else {
                        $(".th-discount").hide()
                    }
                
                    if ($(this).val().length > 0 && $("#checked_none").is(":checked") == false) {
                        var selectId = $(this).attr('id')
                        $("#multi-options-details").show()
                        $("#single-details").hide()
                        $("#single-discount").hide()
                    }else {
                        $("#multi-options-details").hide()
                        $("#single-details").show()
                        $("#single-discount").show()
                    }
                    
                    
                    $("#example tbody").html('')
                    var optionsText = []
                    $(this).find("option:selected").each(function () {
                        optionsText.push($(this).text())
                    })
                    
                    for (var i = 0; i < $(this).val().length; i ++) {
                        var priceAfterDiscountInput = ""
                        if ($("#discount").is(":checked")) {
                            priceAfterDiscountInput = `<td><input type="text" disabled class="form-control" > <input type="hidden" class="form-control" name="price_after_discount[]" ></td>`
                        }
                        $("#example tbody").append(`
                        <tr>
                            <td><i style="color : red; cursor:pointer" data-option="${selectId}" class="fa fa-trash" aria-hidden="true"></i> ${optionsText[i]} <input type="hidden" name="multi_option_value_id[]" value="${$(this).val()[i]}" /></td>
                            <td><input type="text" required class="form-control" name="total_amount[]" ></td>
                            <td><input type="text" required class="form-control" name="remaining_amount[]" ></td>
                            <td><input type="text" required class="form-control" name="final_price[]" ></td>
                            ${priceAfterDiscountInput}
                            <td><input unique="1" type="text" class="form-control" name="barcodes[]" ></td>
                            <td><input unique="1" type="text" class="form-control" name="stored_numbers[]" ></td>
                        </tr>
                        `)
                    }
                    
                    
                    
                })
            @else
            console.log("iii")
            $("#multi_options_radio .row").on('change', 'select.multi_tags', function() {
                if ($("#discount").is(":checked")) {
                    $(".th-discount").show()
                }else {
                    $(".th-discount").hide()
                }
            
                if ($(this).val().length > 0 && $("#checked_none").is(":checked") == false) {
                    var selectId = $(this).attr('id')
                    $("#multi-options-details").show()
                    $("#single-details").hide()
                    $("#single-discount").hide()
                }else {
                    $("#multi-options-details").hide()
                    $("#single-details").show()
                    $("#single-discount").show()
                }
                
                
                $("#example tbody").html('')
                var optionsText = []
                $(this).find("option:selected").each(function () {
                    optionsText.push($(this).text())
                })
                
                for (var i = 0; i < $(this).val().length; i ++) {
                    var priceAfterDiscountInput = ""
                    if ($("#discount").is(":checked")) {
                        priceAfterDiscountInput = `<td><input type="text" disabled class="form-control" > <input type="hidden" class="form-control" name="price_after_discount[]" ></td>`
                    }
                    $("#example tbody").append(`
                    <tr>
                        <td><i style="color : red; cursor:pointer" data-option="${selectId}" class="fa fa-trash" aria-hidden="true"></i> ${optionsText[i]} <input type="hidden" name="multi_option_value_id[]" value="${$(this).val()[i]}" /></td>
                        <td><input type="text" required class="form-control" name="total_amount[]" ></td>
                        <td><input type="text" required class="form-control" name="remaining_amount[]" ></td>
                        <td><input type="text" required class="form-control" name="final_price[]" ></td>
                        ${priceAfterDiscountInput}
                        <td><input unique="1" type="text" class="form-control" name="barcodes[]" ></td>
                        <td><input unique="1" type="text" class="form-control" name="stored_numbers[]" ></td>
                    </tr>
                    `)
                }
                
                
                
            })

            @endif

            // remove multi option row
            $("#example tbody").on("click", "tr td .fa-trash", function () {
                var elementVal = $(this).siblings('input').val(),
                    optionId = $(this).data('option'),
                    valArray = $("#multi_options_radio .row").find("select#" + optionId).val()
                    console.log(valArray)
                var index = valArray.indexOf(elementVal)

                    
                    if (index > -1) {
                        valArray.splice(index, 1);
                    }
                    
                    //$("#multi_options_radio .row").find("select").val(valArray)
                    
                    if (valArray.length > 0) {
                        
                        
                        var textOptions = []
                          $("#multi_options_radio .row").find("select#" + optionId).find("option").each(function () {
                            var $this = $(this);
                            if ($this.length) {
                                var selText = $this.text().trim();
                                textOptions.push(selText)
                            }
                        });
                        var avlsOptions = []
                          $("#multi_options_radio .row").find("select#" + optionId).find("option").each(function () {
                            var $this = $(this);
                            if ($this.length) {
                                var selVal = $this.val();
                                avlsOptions.push(selVal)
                            }
                        });
                        var optionsEle = ""
                        for (var k = 0; k < avlsOptions.length; k ++) {
                            var selected = ""

                            if (valArray.includes(avlsOptions[k])) {
                                selected = "selected"
                            }
                            optionsEle += `
                            <option ${selected} value="${avlsOptions[k]}">${textOptions[k]}</option>
                            `
                        }
                        $("#multi_options_radio .row").find("select#" + optionId).html(optionsEle)
                        $(this).parent('td').parent('tr').remove()
                    }
            })

            // change discount value on change price in each multi option
            $("#example tbody").on('keyup', 'tr td input[name="final_price[]"]', function() {
                var priceVal = $(this).val(),
                    discountValue = $("#offer_percentage").val(),
                    discountNumber = Number(priceVal) * (Number(discountValue) / 100),
                    total = Number(priceVal) - discountNumber
				total = Math.round((total + Number.EPSILON) * 1000) / 1000
                $(this).parent("td").next('td').children('input[disabled="disabled"]').val(total)
                $(this).parent("td").next('td').children('input[name="price_after_discount[]"]').val(total)
            })
            
            // action on checked discount
            $("#discount").click(function() {
                if ($(this).is(':checked')) {
                    $("#offer_percentage").parent(".form-group").show()
                    $("#offer_percentage").prop('disabled', false)
                    if ($("#example tbody").children("tr").length > 0) {
                        $(".th-discount").show()
                        for (var n = 0; n < $("#example tbody").children("tr").length; n ++) {
                            $("#example tbody").children("tr").eq(n).find("input[name='final_price[]']").parent('td').after(`
                            <td><input type="text" disabled class="form-control" > <input type="hidden" class="form-control" name="price_after_discount[]" ></td>
                            `)
                        }
                    }else {
                        
                        $("#final_price").parent(".form-group").show()
                    }
                    
                }else {
                    $("#offer_percentage").parent(".form-group").hide()
                    $("#offer_percentage").prop('disabled', true)
                    if ($("#example tbody").children("tr").length > 0) {
                        $(".th-discount").hide()
                        for (var n = 0; n < $("#example tbody").children("tr").length; n ++) {
                            $("#example tbody").children("tr").eq(n).children('td').eq(4).remove()
                        }
                    }else {
                        $("#final_price").parent(".form-group").hide()
                    }
                }
            })

            // show price after discount
            $("#offer_percentage").on("keyup", function () {
                if ($("#example tbody").children("tr").length > 0) {
                    
                    for (var m = 0; m < $("#example tbody").children("tr").length; m ++) {
                        var discountValue = $("#offer_percentage").val(),
                            price = $("#example tbody").children("tr").eq(m).children('td').eq(3).children("input").val(),
                            discountNumber = Number(price) * (Number(discountValue) / 100),
                            total = Number(price) - discountNumber
                            total = Math.round((total + Number.EPSILON) * 1000) / 1000
                            $("#example tbody").children("tr").eq(m).children('td').eq(4).children("input").eq(0).val(total)
                            $("#example tbody").children("tr").eq(m).children('td').eq(4).children("input").eq(1).val(total)
                    }
                }else {
                    var discountValue = $("#offer_percentage").val(),
                    price = $("#price_before_offer").val(),
                    discountNumber = Number(price) * (Number(discountValue) / 100),
                    total = Number(price) - discountNumber
					total = Math.round((total + Number.EPSILON) * 1000) / 1000
                $("#final_price").val(total)
                }
                
            })

            $("#price_before_offer").on("keyup", function () {
                var discountValue = $("#offer_percentage").val(),
                    price = $("#price_before_offer").val(),
                    discountNumber = Number(price) * (Number(discountValue) / 100),
                    total = Number(price) - discountNumber
				total = Math.round((total + Number.EPSILON) * 1000) / 1000
                $("#final_price").val(total)
            })

            $("#category_options .row").on('click', 'input', function() {
                var label = $(this).data("label"),
                        labelEn = "English " + label,
                        labelAr = "Arabic " + label,
                        elementValue = $(this).val() + "element",
                        optionId = $(this).val()
                   
                   if (language == 'ar') {
                        labelEn = label + " باللغة الإنجليزية"
                        labelAr = label + " باللغة العربية"
                   }
               if($(this).is(':checked')) {
                    $("#category_options_sibling").append(`
                    <div class="form-group mb-4 ${elementValue}">
                        <label for="title_en">${labelEn}</label>
                        <input required type="text" name="value_en[]" class="form-control" id="title_en" placeholder="${labelEn}" value="" >
                    </div>
                    <div class="form-group mb-4 ${elementValue}">
                        <label for="title_en">${labelAr}</label>
                        <input required type="text" name="value_ar[]" class="form-control" id="title_en" placeholder="${labelAr}" value="" >
                    </div>
                    <input name="option[]" value="${optionId}" type="hidden" class="new-control-input ${elementValue}">
                    `)
               }else {
                   console.log("." + elementValue)
                $("." + elementValue).remove()
               }
            })

            $("#add_home").on("change", function() {
                if ($(this).is(':checked')) {
                    $("#home_section").prop("disabled", false)
                    $("#home_section").parent(".form-group").show()
                }else {
                    $("#home_section").prop("disabled", true)
                    $("#home_section").parent(".form-group").hide()
                }
            })

            var previous = "{{ __('messages.previous') }}",
                next = "{{ __('messages.next') }}",
                finish = "{{ __('messages.finish') }}"

                
            $(".actions ul").find('li').eq(0).children('a').text(previous)
            $(".actions ul").find('li').eq(1).children('a').text(next)
            $(".actions ul").find('li').eq(2).children('a').text(finish)

            // add class next1 to next button to control the first section
            $(".actions ul").find('li').eq(1).children('a').addClass("next1")
            
            // section one validation
            $(".actions ul").find('li').eq(1).on("mouseover", "a.next1", function() {
                var image = $('input[name="images[]"]').val(),
                    categorySelect = $("#category").val(),
                    subCategorySelect = $("#sub_category_select").val(),
                    titleEnInput = $("input[name='title_en']").val(),
                    titleArInput = $("input[name='title_ar']").val(),
                    descriptionEnText = $('textarea[name="description_en"]').val(),
                    descriptionArText = $('textarea[name="description_ar"]').val(),
                    subCategory2Select = $("#sub_category_two").val(),
                    weightInput = $("input[name='weight']").val(),
                    numbersInput = $("input[name='numbers']").val(),
                    kgArInput = $('input[name="kg_ar"]').val(),
                    kgArRequired = "{{ __('messages.kg_ar_required') }}",
                    kgEnInput = $('input[name="kg_en"]').val(),
                    kgEnRequired = "{{ __('messages.kg_en_required') }}"

                if (categorySelect > 0 && subCategorySelect > 0 && weightInput > 0 && numbersInput > 0  && titleEnInput.length > 0 && titleArInput.length > 0 && descriptionEnText.length > 0 && descriptionArText.length > 0&& kgArInput.length > 0 && kgEnInput.length > 0) {
                    $(this).attr('href', '#next')
                    $(this).addClass('next2')
                    
                }else {
                    $(this).attr('href', '#')
                }
                
            })

            // show validation messages on section 1
            $(".actions ul").find('li').eq(1).on("click", "a", function () {
                var categorySelect = $("#category").val(),
                    subCategorySelect = $("#sub_category_select").val(),
                    titleEnInput = $("input[name='title_en']").val(),
                    titleArInput = $("input[name='title_ar']").val(),
                    descriptionEnText = $('textarea[name="description_en"]').val(),
                    descriptionArText = $('textarea[name="description_ar"]').val()
                    imagesRequired = "{{ __('messages.images_required') }}",
                    categoryRequired = "{{ __('messages.category_required') }}",
                    subCategoryRequired = "{{ __('messages.sub_category_required') }}",
                    titleEnRequired = "{{ __('messages.title_en_required') }}",
                    titleArRequired = "{{ __('messages.title_ar_required') }}",
                    descriptionEnRequired = "{{ __('messages.description_en_required') }}",
                    descriptionArRequired = "{{ __('messages.description_ar_required') }}",
                    subCategory2Select = $("#sub_category_two").val(),
                    weightInput = $("input[name='weight']").val(),
                    numbersInput = $("input[name='numbers']").val(),
                    subCat2Required = "{{ __('messages.sub_cat2_required') }}",
                    weightRequired = "{{ __('messages.weight_required') }}",
                    numbersRequired = "{{ __('messages.numbers_required') }}"

                
                
                
                if (categorySelect > 0) {
                    $(".category-required").remove()
                }else {
                    if ($(".category-required").length) {

                    }else {
                        $("#category").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 category-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${categoryRequired}</div>
                        `)
                    }
                }

                if (subCategorySelect > 0) {
                    $(".sub-category-required").remove()
                }else {
                    if ($(".sub-category-required").length) {

                    }else {
                        $("#sub_category_select").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 sub-category-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${subCategoryRequired}</div>
                        `)
                    }
                }

                if (subCategory2Select > 0) {
                    $(".sub-category2-required").remove()
                }else {
                    if ($(".sub-category2-required").length) {

                    }else {
                        $("#sub_category_two").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 sub-category2-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${subCat2Required}</div>
                        `)
                    }
                }

                if (weightInput.length == 0) {
                    if ($(".weight-required").length) {

                    }else {
                        $("input[name='weight']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 weight-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${weightRequired}</div>
                        `)
                    }
                }else {
                    $(".weight-required").remove()
                }

                if (numbersInput.length == 0) {
                    if ($(".numbers-required").length) {

                    }else {
                        $("input[name='numbers']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 numbers-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${numbersRequired}</div>
                        `)
                    }
                }else {
                    $(".numbers-required").remove()
                }

                if (titleEnInput.length == 0) {
                    if ($(".titleEn-required").length) {

                    }else {
                        $("input[name='title_en']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 titleEn-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${titleEnRequired}</div>
                        `)
                    }
                }else {
                    $(".titleEn-required").remove()
                }

                if (titleArInput.length == 0) {
                    if ($(".titleAr-required").length) {

                    }else {
                        $("input[name='title_ar']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 titleAr-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${titleArRequired}</div>
                        `)
                    }
                }else {
                    $(".titleAr-required").remove()
                }

                if (descriptionEnText.length == 0) {
                    if ($(".descEn-required").length) {

                    }else {
                        $('textarea[name="description_en"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 descEn-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${descriptionEnRequired}</div>
                        `)
                    }
                }else {
                    $(".descEn-required").remove()
                }

                if (descriptionArText.length == 0) {
                    if ($(".descAr-required").length) {

                    }else {
                        $('textarea[name="description_ar"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 descAr-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${descriptionArRequired}</div>
                        `)
                    }
                }else {
                    $(".descAr-required").remove()
                }
            })

            // section three validation 
            $(".actions ul").find('li').eq(2).on("mouseover", "a", function() {
                var totalQInput = $("input[name='total_quatity']").val(),
                    remainingQInput = $("input[name='remaining_quantity']").val(),
                    priceBOfferInput = $('input[name="price_before_offer"]').val(),
                    offerCheckbox = $('input[name="offer"]'),
                    offerPerc = ""

                if (offerCheckbox.is(':checked')) {
                    offerPerc = $('input[name="offer_percentage"]').val()

                    if (offerPerc > 0 && totalQInput > 0 && remainingQInput > 0 && priceBOfferInput > 0) {
                        $(this).prop('href', '#finish')
                    }else {
                        $(this).attr('href', '#')
                    }
                }else {
                    if (totalQInput > 0 && remainingQInput > 0 && totalQInput >= remainingQInput  && priceBOfferInput > 0) {
                        $(this).attr('href', '#finish')
                    }else {
                        $(this).attr('href', '#')
                    }
                }
            })

            // show validation messages on section 3
            $(".actions ul").find('li').eq(2).on("click", "a[href='#']", function() {
                var totalQInput = $("input[name='total_quatity']").val(),
                    remainingQInput = $("input[name='remaining_quantity']").val(),
                    priceBOfferInput = $('input[name="price_before_offer"]').val(),
                    offerCheckbox = $('input[name="offer"]'),
                    offerPerc = "",
                    totalQRequired = "{{ __('messages.total_quantity_required') }}",
                    remainingQRequired = "{{ __('messages.remaining_quantity_required') }}",
                    remainingQLessTotal = "{{ __('messages.remaining_q_less_total') }}",
                    priceRequired = "{{ __('messages.price_required') }}",
                    oferrVRequired = "{{ __('messages.offer_required') }}"

                if (offerCheckbox.is(':checked')) {
                    offerPerc = $('input[name="offer_percentage"]').val()

                    if (offerPerc <= 0) {
                        if ($(".offerV-required").length) {
    
                        }else {
                            $('input[name="offer_percentage"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${oferrVRequired}</div>
                            `)
                        }
                    }else {
                        $(".offerV-required").remove()
                    }

                    if (totalQInput <= 0) {
                        if ($(".totalQ-required").length) {
    
                        }else {
                            $('input[name="total_quatity"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 totalQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${totalQRequired}</div>
                            `)
                        }
                    }else {
                        $(".totalQ-required").remove()
                    }

                    if (remainingQInput <= 0) {
                        if ($(".remainingQ-required").length) {
    
                        }else {
                            $("input[name='remaining_quantity']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQRequired}</div>
                            `)
                        }
                    }else {
                        $(".remainingQ-required").remove()
                    }

                    if (remainingQInput > totalQInput) {
                        
                        if ($(".remainingQLess-required").length) {
    
                        }else {
                            $("input[name='remaining_quantity']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQLess-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQLessTotal}${totalQInput}</div>
                            `)
                        }
                        
                    }else {
                        $(".remainingQLess-required").remove()
                    }

                    if (priceBOfferInput <= 0) {
                        if ($(".priceBOffer-required").length) {
    
                        }else {
                            $('input[name="price_before_offer"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 priceBOffer-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${priceRequired}</div>
                            `)
                        }
                    }else {
                        $(".priceBOffer-required").remove()
                    }

                    
                }else {

                    if (totalQInput <= 0) {
                        if ($(".totalQ-required").length) {
    
                        }else {
                            $('input[name="total_quatity"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 totalQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${totalQRequired}</div>
                            `)
                        }
                    }else {
                        $(".totalQ-required").remove()
                    }

                    if (remainingQInput <= 0) {
                        if ($(".remainingQ-required").length) {
    
                        }else {
                            $("input[name='remaining_quantity']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQRequired}</div>
                            `)
                        }
                    }else {
                        $(".remainingQ-required").remove()
                    }

                    if (remainingQInput > totalQInput) {
                        if ($(".remainingQLess-required").length) {
    
                        }else {
                            $("input[name='remaining_quantity']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQLess-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQLessTotal}${totalQInput}</div>
                            `)
                        }
                        
                    }else {
                        $(".remainingQLess-required").remove()
                    }

                    if (priceBOfferInput <= 0) {
                        if ($(".priceBOffer-required").length) {
    
                        }else {
                            $('input[name="price_before_offer"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 priceBOffer-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${priceRequired}</div>
                            `)
                        }
                    }else {
                        $(".priceBOffer-required").remove()
                    }
                }
            })

            /*
            *  show / hide message on change value
            */
            
            // image
            $('input[name="images[]"]').on("change", function() {
                var image = $('input[name="images[]"]').val(),
                    imagesRequired = "{{ __('messages.images_required') }}"

                if (image.length > 0) {
                    if ($(".image-required").length) {
                        $(".image-required").remove()
                    }
                }else {
                    if ($(".image-required").length) {
                        
                    }else {
                        $('input[name="images[]"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 image-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${imagesRequired}</div>
                        `)
                    }
                }
            })

            // category
            $("#category").on("change", function() {
                var categorySelect = $("#category").val(),
                    categoryRequired = "{{ __('messages.category_required') }}"

                if (categorySelect > 0) {
                    if ($(".category-required").length) {
                        $(".category-required").remove()
                    }
                }else {
                    if ($(".category-required").length) {

                    }else {
                        $("#category").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 category-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${categoryRequired}</div>
                            `)
                    }
                }
            })

            // sub category
            $("#sub_category_select").on("change", function() {
                var subCategorySelect = $("#sub_category_select").val(),
                    subCategoryRequired = "{{ __('messages.sub_category_required') }}",
                    subCategoryId = $(this).val()

                if (subCategorySelect > 0) {
                    if ($(".sub-category-required").length) {
                        $(".sub-category-required").remove()
                    } 
                }else {
                    if ($(".sub-category-required").length) {

                    }else {
                        $("#sub_category_select").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 sub-category-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${subCategoryRequired}</div>
                        `)
                    }
                }

                $("#multi_options_radio .row").html('')
                $.ajax({
                    url : "/admin-panel/products/fetchsubcategorymultioptions/" + subCategoryId,
                    type : 'GET',
                    success : function (data) {
                        
                        $('#multi_options_radio').show()
                        
                        data.forEach(function (option) {
                        
                            var optionName = option.title_en,
                                select = "{{ __('messages.select') }}",
                                anotherChoice = "{{ __('messages.another_choice') }}"
                            if (language == 'ar') {
                                optionName = option.title_ar
                            }
                            var propValOption = ""
                            
                            option.values.forEach(function(propVal) {
                            var optionVal = propVal.value_en
                            if (language == 'ar') {
                                optionVal = propVal.value_ar
                            }
                                propValOption += `
                                <option value="${propVal.id}">
                                    ${optionVal}
                                </option>
                                `
                            })
                            propValOption += `
                            <option value="0">
                                ${anotherChoice}
                            </option>
                            `
                            var propValSelect = `
                            <select id="multi${option.id}" class="form-control multi_tags" disabled multiple name="multi_option_value_id[]">
                                ${propValOption}
                            </select>
                            `
                            $("#multi_options_radio .row").append(`
                            
                            <div class="col-sm-2 text-option">
                                <div class="n-chk">
                                    <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                                    <input data-multi="multi${option.id}" name="multi_option_id" type="radio" value="${option.id}" class="new-control-input all-permisssions">
                                    <span class="new-control-indicator"></span><span class="new-chk-content">${optionName}</span>
                                    </label>
                                </div>  
                            </div>
                            <div class="col-sm-4">${propValSelect}</div>
                            
                            `)
                        })
                        $(".multi_tags").select2({
                            tags: true
                        });
                    }
                })
            })

            // title en
            $("input[name='title_en']").on("keyup", function() {
                var titleEnInput = $("input[name='title_en']").val(),
                    titleEnRequired = "{{ __('messages.title_en_required') }}"

                if (titleEnInput.length > 0) {
                    if ($(".titleEn-required").length) {
                        $(".titleEn-required").remove()
                    }
                }else {
                    if ($(".titleEn-required").length) {
                        
                    }else {
                        $("input[name='title_en']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 titleEn-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${titleEnRequired}</div>
                        `)
                    }
                }
            })

            // title ar
            $("input[name='title_ar']").on("keyup", function() {
                var titleArInput = $("input[name='title_ar']").val(),
                    titleArRequired = "{{ __('messages.title_ar_required') }}"

                if (titleArInput.length > 0) {
                    if ($(".titleAr-required").length) {
                        $(".titleAr-required").remove()
                    }
                }else {
                    if ($(".titleAr-required").length) {
                        
                    }else {
                        $("input[name='title_ar']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 titleAr-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${titleArRequired}</div>
                        `)
                    }
                }
            })

            // description en
            $('textarea[name="description_en"]').on("keyup", function() {
                var descriptionEnText = $('textarea[name="description_en"]').val(),
                    descriptionEnRequired = "{{ __('messages.description_en_required') }}"

                if (descriptionEnText.length > 0) {
                    if ($(".descEn-required").length) {
                        $(".descEn-required").remove()
                    }
                }else {
                    if ($(".descEn-required").length) {

                    }else {
                        $('textarea[name="description_en"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 descEn-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${descriptionEnRequired}</div>
                        `)
                    }
                }
            })
            
            // description ar
            $('textarea[name="description_ar"]').on("keyup", function() {
                var descriptionArText = $('textarea[name="description_ar"]').val(),
                    descriptionArRequired = "{{ __('messages.description_ar_required') }}"

                if (descriptionArText.length > 0) {
                    if ($(".descAr-required").length) {
                        $(".descAr-required").remove()
                    }
                }else {
                    if ($(".descAr-required").length) {

                    }else {
                        $('textarea[name="description_ar"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 descAr-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${descriptionArRequired}</div>
                        `)
                    }
                }
            })

            // kg en
            $('input[name="kg_en"]').on("keyup", function() {
                var kgEnInput = $('input[name="kg_en"]').val(),
                    kgEnRequired = "{{ __('messages.kg_en_required') }}"

                if (kgEnInput.length > 0) {
                    if ($(".kgEn-required").length) {
                        $(".kgEn-required").remove()
                    }
                }else {
                    if ($(".kgEn-required").length) {

                    }else {
                        $('input[name="kg_en"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 kgEn-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${kgEnRequired}</div>
                        `)
                    }
                }
            })

            // kg ar
            $('input[name="kg_ar"]').on("keyup", function() {
                var kgArInput = $('input[name="kg_ar"]').val(),
                    kgArRequired = "{{ __('messages.kg_ar_required') }}"

                if (kgArInput.length > 0) {
                    if ($(".kgAr-required").length) {
                        $(".kgAr-required").remove()
                    }
                }else {
                    if ($(".kgAr-required").length) {

                    }else {
                        $('input[name="kg_ar"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 kgAr-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${kgArRequired}</div>
                        `)
                    }
                }
            })

            // total quantity
            $("input[name='total_quatity']").on("keyup", function() {
                var totalQInput = $("input[name='total_quatity']").val(),
                    totalQRequired = "{{ __('messages.total_quantity_required') }}"

                if (totalQInput <= 0) {
                    if ($(".totalQ-required").length) {

                    }else {
                        $('input[name="total_quatity"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 totalQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${totalQRequired}</div>
                        `)
                    }
                }else {
                    $(".totalQ-required").remove()
                }
            })

            // remaining quantity
            $("input[name='remaining_quantity']").on("keyup", function() {
                var remainingQInput = $("input[name='remaining_quantity']").val(),
                    totalQInput = $("input[name='total_quatity']").val(),
                    remainingQRequired = "{{ __('messages.remaining_quantity_required') }}",
                    remainingQLessTotal = "{{ __('messages.remaining_q_less_total') }}"

                if (remainingQInput <= 0) {
                    if ($(".remainingQ-required").length) {

                    }else {
                        $("input[name='remaining_quantity']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQ-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQRequired}</div>
                        `)
                    }
                }else {
                    $(".remainingQ-required").remove()
                }

                if (remainingQInput > totalQInput) {
                    if ($(".remainingQLess-required").length) {

                    }else {
                        $("input[name='remaining_quantity']").after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 remainingQLess-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQLessTotal}${totalQInput}</div>
                        `)
                    }
                    
                }else {
                    $(".remainingQLess-required").remove()
                }
            })

            // price before offer
            $('input[name="price_before_offer"]').on("keyup", function() {
                var priceBOfferInput = $('input[name="price_before_offer"]').val(),
                    priceRequired = "{{ __('messages.price_required') }}"

                if (priceBOfferInput <= 0) {
                    if ($(".priceBOffer-required").length) {
    
                    }else {
                        $('input[name="price_before_offer"]').after(`
                        <div style="margin-top:20px" class="alert alert-outline-danger mb-4 priceBOffer-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${priceRequired}</div>
                        `)
                    }
                }else {
                    $(".priceBOffer-required").remove()
                }
            })

            // offer value where offer checked
            $('input[name="offer"]').on("click", function() {

                if ($(this).is(":checked")) {
                    var offerPerc = $('input[name="offer_percentage"]').val(),
                        oferrVRequired = oferrVRequired = "{{ __('messages.offer_required') }}"

                    if (offerPerc <= 0) {
                        if ($(".offerV-required").length) {
    
                        }else {
                            $('input[name="offer_percentage"]').after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${oferrVRequired}</div>
                            `)
                        }
                    }else {
                        $(".offerV-required").remove()
                    }
                }
            })

            // show validation messages last section
            $(".actions ul").find('li').eq(2).on("click", "a[href='#']", function () {
                
                if ($("#example tbody").find("tr").length > 0) {
                    
                }else {
                    var totalQRequired = "{{ __('messages.total_quantity_required') }}",
                        remainingQRequired = "{{ __('messages.remaining_quantity_required') }}",
                        priceRequired = "{{ __('messages.price_required') }}",
                        offerRequired = "{{ __('messages.offer_required') }}",
                        remainingQLess = "{{ __('messages.remaining_q_less_total') }}",
                        totalQInput = $("input[name='total_quatity']").val(),
                        remainingQInput = $("input[name='remaining_quantity']").val(),
                        priceBeforeOfferInput = $("input[name='price_before_offer']").val(),
                        offerPercInput = $("#offer_percentage").val()

                    if (totalQInput == 0) {
                        if ($("input[name='total_quatity']").next('.offerV-required').length) {

                        }else {
                            $("input[name='total_quatity']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${totalQRequired}</div>
                            `)
                        }
                    }else {
                        $("input[name='total_quatity']").next('.offerV-required').remove()
                    }

                    if (remainingQInput == 0) {
                        if (Number(remainingQInput) > Number(totalQInput)) {
                            
                            if ($("input[name='remaining_quantity']").next('.offerV-required').length) {

                            }else {
                                $("input[name='remaining_quantity']").after(`
                                <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQLess} ${totalQ}</div>
                                `)
                            }
                        }else {
                            if ($("input[name='remaining_quantity']").next('.offerV-required').length) {

                            }else {
                                $("input[name='remaining_quantity']").after(`
                                <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${remainingQRequired}</div>
                                `)
                            }
                        }
                    }else {
                        $("input[name='remaining_quantity']").next('.offerV-required').remove()
                    }

                    if (priceBeforeOfferInput == 0) {
                        if ($("input[name='price_before_offer']").next('.offerV-required').length) {
    
                        }else {
                            $("input[name='price_before_offer']").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${priceRequired}</div>
                            `)
                        }
                    }else {
                        $("input[name='price_before_offer']").next('.offerV-required').remove()
                    }

                    if (! offerPercInput) {
                        if ($("#offer_percentage").next('.offerV-required').length) {
    
                        }else {
                            $("#offer_percentage").after(`
                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${offerRequired}</div>
                            `)
                        }
                    }else {
                        $("#offer_percentage").next('.offerV-required').remove()
                    }
    
                }
            })

            //section two | three | four validation
            $(".actions ul").find('li').eq(1).on('click', function() {
                var fieldRequired = "{{ __('messages.field_required') }}",
                    remaininiLessTotal = "{{ __('messages.remaining_q_less_total') }}"

                
                // section three
                if ($(".steps ul").find('li').eq(2).hasClass('current')) {
                    $("#multi_options_radio .row").on('change', 'input[type="radio"]', function() {
                        if ($(this).val() != "none") {
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#e0e6ed')
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#e0e6ed')
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                            $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                $(this).attr('href', "#")
                            })
                        }else {
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#e0e6ed')
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#e0e6ed')
                            $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                            $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                $(this).attr('href', "#next")
                            })
                        }
                        var multiId = $(this).data('multi')
                        if ($(this).is(":checked")) {
                            $("select.multi_tags").prop('disabled', true)
                            $(`#${multiId}`).prop('disabled', false)
                            $(`#${multiId}`).prop('required', true)
                            $(`#${multiId}`).parent('.col-sm-4').siblings('.col-sm-4').children('select').prop('required', false)
                            $(`#${multiId}`).on("change", function () {
                                if ($(`#${multiId}`).val().length > 0) {
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                                    $(`#${multiId}`).siblings('.offerV-required').remove()
                                    $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                        $(this).attr('href', "#next")
                                    })
                                }else {
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', 'red')
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', 'red')
                                    $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', 'red solid 1px')
                                    
                                }
                            })
                        }
                    })
                    
                    // on click make select none disabled with danger
                    $(".actions ul").find('li').eq(1).on("click", "a", function() {
                        if ($("#multi_options_radio").find(".col-sm-4").length > 0) {
                            if ($("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").length > 0 && $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").val().length == 0) {
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', 'red')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', 'red')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', 'red solid 1px')
                            }else {
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                            }
                        }
                    })
                }
                // section four
                if ($(".steps ul").find('li').eq(3).hasClass('current')) {
                    if ($("#example tbody").find("tr").length > 0) {
                        for (var r = 0; r < $("#example tbody").find("tr").length; r ++) {
                            
                            for (var q = 0; q < $("#example tbody").find("tr").eq(r).find("td").length; q ++) {
                                
                                if (q != 0 && q != 4 && q != $("#example tbody").find("tr").eq(r).find("td").length-1 && q != $("#example tbody").find("tr").eq(r).find("td").length-2) {
                                    
                                    $("#example tbody").find("tr").eq(r).find("td").eq(q).on("keyup", "input", function() {
                                        
                                        if ($(this).attr('name') == 'remaining_amount[]') {
                                            var remainingVal = $(this).val(),
                                                totalVal = $(this).parent('td').prev('td').children('input').val()
                                                
                                            if (Number(remainingVal) <= Number(totalVal) && $(this).val()) {
                                                $(this).attr("placeholder", "")
                                                $(this).css("border", "#ccc solid 1px")
                                                $(this).attr("valid", "1")
                                                
                                            }else {
                                                if (Number(remainingVal) > Number(totalVal)) {
                                                    $(this).attr("placeholder", remaininiLessTotal)
                                                }else {
                                                    $(this).attr("placeholder", fieldRequired)
                                                }
                                                $(this).css("border", "red solid 1px")
                                                $(this).attr("valid", "0")
                                            }
                                        }else {
                                            if ( !$(this).val() ) {
                                                $(this).attr("placeholder", fieldRequired)
                                                $(this).css("border", "red solid 1px")
                                                $(this).attr("valid", "0")
                                            }else {
                                                $(this).attr("placeholder", "")
                                                $(this).css("border", "#ccc solid 1px")
                                                $(this).attr("valid", "1")
                                            }
                                        }
                                    })
                                    
                                }   
                            }
                        }
                    }else {
                        var totalQRequired = "{{ __('messages.total_quantity_required') }}",
                            remainingQRequired = "{{ __('messages.remaining_quantity_required') }}",
                            priceRequired = "{{ __('messages.price_required') }}",
                            offerRequired = "{{ __('messages.offer_required') }}",
                            remainingQLess = "{{ __('messages.remaining_q_less_total') }}"

                        $("input[name='total_quatity']").on('keyup', function() {
                            if ( !$(this).val() ) {
                                $(this).attr('valid', "0")
                                
                            }else {
                                $(this).attr('valid', "1")
                                {{-- $(this).next('.offerV-required').remove() --}}
                            }
                        })

                        $("input[name='remaining_quantity']").on('keyup', function() {
                            var remainingQ = $(this).val(),
                                totalQ = $("input[name='total_quatity']").val()
                            if ( !$(this).val() || Number(remainingQ) > Number(totalQ) ) {
                                $(this).attr('valid', "0")
                                
                                
                                
                            }else {
                                $(this).attr('valid', "1")
                               
                            }
                        })

                        $("input[name='price_before_offer']").on('keyup', function() {
                            if ( !$(this).val() ) {
                                $(this).attr('valid', "0")
                                
                            }else {
                                $(this).attr('valid', "1")
                                
                            }
                        })

                        $("#offer_percentage").on('keyup', function() {
                            if ( !$(this).val() ) {
                                $(this).attr('valid', "0")
                                
                            }else {
                                $(this).attr('valid', "1")
                                
                            }
                        })
                    }

                    // validation on click section 4
                    $(".actions ul").find('li').eq(2).on("mouseover", "a", function() {
                        
                        if ($("#example tbody").find("tr").length > 0) {
                            
                            $(".actions ul").find('li').eq(2).find("a").attr('href', "#")
                            var inputsNumber = $("#example tbody").find("tr input").length - $("#example tbody").find("tr").length - (2 * $("#example tbody").find("tr").length)
                            var validInputs = $("#example tbody").find("tr input[valid='1']").length,
                                uniqueInputs = $("#example tbody").find("tr input[unique='1']").length,
                                offerValueRequired = "{{ __('messages.offerValueRequired') }}"
                            
                            
                            if ($("#discount").is(":checked")) {
                                inputsNumber = ($("#example tbody").find("tr input").length - $("#example tbody").find("tr").length) - (4 * $("#example tbody").find("tr").length)
                                
                                $("input[name='offer_percentage']").on("keyup", function() {
                                    if ( !$(this).val() ) {
                                        $(this).attr('valid', "0")
                                        if ($(".offerV-required").length) {

                                        }else {
                                            $(this).after(`
                                            <div style="margin-top:20px" class="alert alert-outline-danger mb-4 offerV-required" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button><i class="flaticon-cancel-12 close" data-dismiss="alert"></i> ${offerValueRequired}</div>
                                            `)
                                        }
                                    }else {
                                        $(this).next(".offerV-required").remove()
                                        $(this).attr('valid', "1")
                                    }
                                    
                                })
                                
                                if (validInputs == inputsNumber &&  $("input[name='offer_percentage']").attr('valid') == "1" && uniqueInputs == (2 * $("#example tbody").find("tr").length)) {
                                
                                    $(this).attr('href', "#finish")
                                    
                                }
                                
                            }else {
                                
                                
                                if (validInputs == inputsNumber && uniqueInputs == (2 * $("#example tbody").find("tr").length)) {
                                
                                    $(this).attr('href', "#finish")
                                    
                                }
                            }
                            
                        }else {
                            
                            if ($("#discount").is(":checked")) {
                                
                                if ($("input[name='price_before_offer']").val() > 0 && 
                                    $("input[name='remaining_quantity']").val() > 0 && 
                                    $("input[name='total_quatity']").val() > 0 &&
                                    $("input[name='offer_percentage']").val() > 0) {
                                        
                                        $(this).attr('href', "#finish")
                                    }else {
                                        
                                        $(this).attr('href', "#")
                                    }
                            }else {
                                if ($("input[name='price_before_offer']").val() > 0 && 
                                $("input[name='remaining_quantity']").val() > 0 && 
                                $("input[name='total_quatity']").val() > 0 ) {
                                    $(this).attr('href', "#finish")
                                }else {
                                    $(this).attr('href', "#")
                                }
                            }
                        }
                        
                    })
                }
            })

            // on click prev
            $(".actions ul").find('li').eq(0).on("click", "a", function() {
                $("#multi_options_radio .row").on('change', 'input[type="radio"]', function() {
                    if ($(this).val() != "none") {
                        $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                        $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                        $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#e0e6ed')
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#e0e6ed')
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                        $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                            $(this).attr('href', "#")
                        })
                    }else {
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#e0e6ed')
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#e0e6ed')
                        $("#multi_options_radio").find(".col-sm-4 select[disabled]").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                        $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                            $(this).attr('href', "#next")
                        })
                    }
                    var multiId = $(this).data('multi')
                    if ($(this).is(":checked")) {
                        $("select.multi_tags").prop('disabled', true)
                        $(`#${multiId}`).prop('disabled', false)
                        $(`#${multiId}`).prop('required', true)
                        $(`#${multiId}`).parent('.col-sm-4').siblings('.col-sm-4').children('select').prop('required', false)
                        $(`#${multiId}`).on("change", function () {
                            if ($(`#${multiId}`).val().length > 0) {
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                                $(`#${multiId}`).siblings('.offerV-required').remove()
                                $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                    $(this).attr('href', "#next")
                                })
                            }else {
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', 'red')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', 'red')
                                $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', 'red solid 1px')
                                
                            }
                        })
                    }
                })
                
                // on click make select none disabled with danger
                $(".actions ul").find('li').eq(1).on("click", "a", function() {
                    if ($("#multi_options_radio").find(".col-sm-4").length > 0) {
                        if ($("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").length > 0 && $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").val().length == 0) {
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', 'red')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', 'red')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', 'red solid 1px')
                        }else {
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-control-indicator').css('background', '#1b55e2')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").prev('.col-sm-1').find('.new-chk-content').css('color', '#1b55e2')
                            $("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").parent(".col-sm-4").find('.select2-selection--multiple').css('border', '#bfc9d4 solid 1px')
                        }
                    }
                })
                // if select empty prevent next
                if ($(".steps ul").find('li').eq(2).hasClass('current')) {
                    if ($("#multi_options_radio").find(".col-sm-4 select:not(:disabled)").length > 0 ) {
                        
                        $("#multi_options_radio").find(".col-sm-4").on("change", "select:not(:disabled)", function() {
                            
                            if ($(this).val().length == 0) {
                                
                                $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                    
                                    $(".actions ul").find('li').eq(1).find("a").attr('href', '#')
                                })
                                
                            }else {
                                $(".actions ul").find('li').eq(1).on("mouseover", "a", function() {
                                    
                                    $(".actions ul").find('li').eq(1).find("a").attr('href', '#next')
                                })
                            }
                        })
                    }
                }
            })
            

            // submit form on click finish
            $(".actions ul").find('li').eq(2).on("click", 'a[href="#finish"]', function () {
                $("form").submit()
            })

    </script>
@endpush

@section('content')
    <div class="col-lg-12 col-12 layout-spacing">
        <div class="statbox widget box box-shadow">
            <div class="widget-header">
                <div class="row">
                    <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                        <h4>{{ __('messages.product_edit') }}</h4>
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
                <label for="">{{ __('messages.current_images') }}</label><br>
                <div class="row">
                @if (count($data['product']->images) > 0)
                    @foreach ($data['product']->images as $image)
                    <div style="position : relative" class="col-md-2 product_image">
                        <a onclick="return confirm('{{ __('messages.are_you_sure') }}')" style="position : absolute; right : 20px" href="{{ route('productImage.delete', $image->id) }}" class="close">x</a>
                        <img style="width: 100%" src="https://res.cloudinary.com/dzqo7w4cp/image/upload/w_100,q_100/v1600733461/{{ $image->image }}"  />
                    </div>
                    @endforeach
                @endif
                </div>
            </div>
            <div class="statbox widget box box-shadow">
                <div class="widget-content widget-content-area">
                    <div id="circle-basic" class="">
                        <h3>{{ __('messages.product_details') }}</h3>
                        <section>
                            <div class="custom-file-container" data-upload-id="myFirstImage">
                                <label>{{ __('messages.upload') }} ({{ __('messages.multiple_image') }}) * <a href="javascript:void(0)" class="custom-file-container__image-clear" title="Clear Image">x</a></label>
                                <label class="custom-file-container__custom-file" >
                                    <input type="file" required name="images[]" multiple class="custom-file-container__custom-file__custom-file-input" accept="image/*">
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                                    <span class="custom-file-container__custom-file__custom-file-control"></span>
                                </label>
                                <div class="custom-file-container__image-preview"></div>
                            </div>
                            <div class="form-group">
                                <label for="brand">{{ __('messages.brand') }}</label>
                                <select name="brand_id" multiple class="form-control multi_tags brand">
                                    {{-- <option value="0" selected>{{ __('messages.select') }}</option> --}}
                                    @foreach ( $data['brands'] as $brand )
                                    <option {{ in_array($brand->id, $data['productPrands']) ? 'selected' : '' }} value="{{ $brand->id }}">{{ App::isLocale('en') ? $brand->title_en : $brand->title_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="category">{{ __('messages.category') }}</label>
                                <select id="category" name="category_id" class="form-control">
                                    <option disabled selected>{{ __('messages.select') }}</option>
                                    @foreach ( $data['categories'] as $category )
                                    <option {{ $data['product']['category_id'] == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ App::isLocale('en') ? $category->title_en : $category->title_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="display: none" class="form-group">
                                <label for="sub_category_select">{{ __('messages.sub_category') }}</label>
                                <select id="sub_category_select" name="sub_category_id" class="form-control">
                                </select>
                            </div>

                             <div style="display:none" class="form-group">
                                <label for="sub_categories_two">{{ __('messages.sub_categories_two') }} *</label>
                                <select id="sub_category_two" name="sub_two_category_id" class="form-control">
                                    <option value="0" selected>{{ __('messages.select') }}</option>
                                    @foreach ( $data['sub_two_category'] as $category )
                                    <option {{ $data['product']['sub_two_category_id'] == $category->id ? 'selected' : '' }} value="{{$category->id }}" >{{$category->title_ar }}</option>
                                    @endforeach
                                </select>
                            </div> 

                            <div class="form-group mb-4">
                                <label for="weight">{{ __('messages.weight') }}</label>
                                <input required type="number" name="weight" class="form-control" id="weight" placeholder="{{ __('messages.weight') }}" value="{{ $data['product']['weight'] }}" >
                            </div>

                            <div class="form-group mb-4">
                                <label for="kg_en">{{ __('messages.kg_en') }} *</label>
                                <input required type="text" name="kg_en" class="form-control" id="weight" placeholder="{{ __('messages.kg_en') }}" value="{{ $data['product']['kg_en'] }}" >
                            </div>
                            <div class="form-group mb-4">
                                <label for="kg_ar">{{ __('messages.kg_ar') }} *</label>
                                <input required type="text" name="kg_ar" class="form-control" id="weight" placeholder="{{ __('messages.kg_ar') }}" value="{{ $data['product']['kg_ar'] }}" >
                            </div>

                            <div class="form-group mb-4">
                                <label for="numbers">{{ __('messages.numbers') }} *</label>
                                <input required type="number" name="numbers" class="form-control" id="numbers" placeholder="{{ __('messages.numbers') }}" value="{{ $data['product']['numbers'] }}" >
                            </div>

                            <div class="form-group mb-4">
                                <label for="title_en">{{ __('messages.title_en') }}</label>
                                <input required type="text" name="title_en" class="form-control" id="title_en" placeholder="{{ __('messages.title_en') }}" value="{{ $data['product']['title_en'] }}" >
                            </div>
                            <div class="form-group mb-4">
                                <label for="title_ar">{{ __('messages.title_ar') }}</label>
                                <input required type="text" name="title_ar" class="form-control" id="title_ar" placeholder="{{ __('messages.title_ar') }}" value="{{ $data['product']['title_ar'] }}" >
                            </div>
                            <div class="form-group mb-4 english-direction" >
                                <label for="demo1">{{ __('messages.english') }}</label>
                                <textarea required name="description_en" class="form-control"  rows="5">{{ $data['product']['description_en'] }}</textarea>
                            </div>
                
                            <div class="form-group mb-4 arabic-direction">
                                <label for="demo2">{{ __('messages.arabic') }}</label>
                                <textarea name="description_ar" required  class="form-control"  rows="5">{{ $data['product']['description_ar'] }}</textarea>
                            </div> 
                        </section>
                        <h3>{{ __('messages.product_specification') }} ( {{ __('messages.optional') }} )</h3>
                        <section>
                            <div id="category_options" style="margin-bottom: 20px; display : none" class="col-md-3" >
                                <label> {{ __('messages.options') }} </label>
                                <div class="row">
                                    
                                </div>  
                            </div>
                            <div id="category_options_sibling">
                                @if(isset($data['options']) && count($data['options']) > 0)
                                    @for ($i = 0; $i < count($data['options']); $i ++)
                                    @if(App::isLocale('en'))
                                    <div class="form-group mb-4 {{ $data['options'][$i]['option_id'] . "element" }}">
                                        <label>English {{ $data['options'][$i]['option_title_en']  }}</label>
                                        <input required type="text" name="value_en[]" class="form-control"  placeholder="" value="{{ $data['options'][$i]['value_en'] }}" >
                                    </div>
                                    <div class="form-group mb-4 {{ $data['options'][$i]['option_id'] . "element" }}">
                                        <label >English {{ $data['options'][$i]['option_title_en']  }}</label>
                                        <input required type="text" name="value_ar[]" class="form-control" placeholder="" value="{{ $data['options'][$i]['value_ar'] }}" >
                                    </div>
                                    @else
                                    <div class="form-group mb-4 {{ $data['options'][$i]['option_id'] . "element" }}">
                                        <label>{{ $data['options'][$i]['option_title_ar']  }} باللغة العربية</label>
                                        <input required type="text" name="value_en[]" class="form-control"  placeholder="" value="{{ $data['options'][$i]['value_en'] }}" >
                                    </div>
                                    <div class="form-group mb-4 {{ $data['options'][$i]['option_id'] . "element" }}">
                                        <label >{{ $data['options'][$i]['option_title_ar']  }} باللغة العربية</label>
                                        <input required type="text" name="value_ar[]" class="form-control" placeholder="" value="{{ $data['options'][$i]['value_ar'] }}" >
                                    </div>
                                    @endif
                                    <input name="option[]" value="{{ $data['options'][$i]['option_id'] }}" type="hidden" class="new-control-input {{ $data['options'][$i]['option_id'] . "element" }}">
                                    @endfor
                                @endif
                            </div>
                        </section>
                        <h3>{{ __('messages.multi_options') }}</h3>
                        <section>
                            <div style="margin-bottom : 20px" class="col-md-3" >
                                <label> {{ __('messages.multi_options') }} </label>
                            </div>
                            
                            <div style="display: none" id="multi_options_radio" class="table table-hover" style="width:100%">
                                <div class="row">

                                </div>
                            </div>
                        </section>
                        <h3>{{ __('messages.prices_and_inventory') }}</h3>
                        <section>
                            <div style="display: {{ count($data['product']->multiOptions) > 0 ? '' : 'none' }}" id="multi-options-details" class="widget-content widget-content-area br-6">
                                <table id="example" class="table table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.options_p') }}</th>
                                            <th>{{ __('messages.total_quatity') }}</th>
                                            <th>{{ __('messages.remaining_quantity') }}</th>
                                            <th>{{ __('messages.product_price') }}</th>
                                            <th class="th-discount" style="display: {{ $data['product']->offer == 0 ? 'none' : '' }}">{{ __('messages.price_after_discount') }}</th>
                                            <th>{{ __('messages.barcode') }}</th>
                                            <th>{{ __('messages.product_stored_number') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(count($data['product']->multiOptions) > 0)
                                            @foreach ($data['product']->multiOptions as $m_option)
                                            <tr>
                                                <td><i style="color : red; cursor:pointer" data-option="{{ "multi" . $m_option->multi_option_id}}" class="fa fa-trash" aria-hidden="true"></i> {{ App::isLocale('en') ? $m_option->multiOptionValue->value_en : $m_option->multiOptionValue->value_ar }} <input type="hidden" name="multi_option_value_id[]" value="{{ $m_option->multiOptionValue->id }}" /></td>
                                                <td><input type="text" {{ !empty($m_option->total_quatity) ? 'valid=1' : 'valid=0' }} value="{{ $m_option->total_quatity }}" required class="form-control" name="total_amount[]" ></td>
                                                <td><input type="text" {{ !empty($m_option->remaining_quantity) ? 'valid=1' : 'valid=0' }} value="{{ $m_option->remaining_quantity }}" required class="form-control" name="remaining_amount[]" ></td>
                                                <td><input type="text" {{ !empty($m_option->final_price) ? 'valid=1' : 'valid=0' }} value="{{ $m_option->price_before_offer }}" required class="form-control" name="final_price[]" ></td>
                                                @if($data['product']->offer == 1)
                                                <td><input type="text" value="{{ $m_option->final_price }}" disabled class="form-control" > <input type="hidden" value="{{ $m_option->final_price }}" class="form-control" name="price_after_discount[]" ></td>
                                                @endif
                                                <td><input unique="1" type="text" value="{{ $m_option->barcode }}" required class="form-control" name="barcodes[]" ></td>
                                                <td><input unique="1" type="text" value="{{ $m_option->stored_number }}" required class="form-control" name="stored_numbers[]" ></td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div style="display: {{ count($data['product']->multiOptions) > 0 ? 'none' : '' }}" id="single-details">
                                <div class="form-group mb-4">
                                    <label for="total_quatity">{{ __('messages.total_quatity') }}</label>
                                    <input required type="number" name="total_quatity" class="form-control" id="total_quatity" placeholder="{{ __('messages.total_quatity') }}" value="{{ $data['product']['total_quatity'] }}" >
                                </div>
                                <div class="form-group mb-4">
                                    <label for="remaining_quantity">{{ __('messages.remaining_quantity') }}</label>
                                    <input required type="number" name="remaining_quantity" class="form-control" id="remaining_quantity" placeholder="{{ __('messages.remaining_quantity') }}" value="{{ $data['product']['remaining_quantity'] }}" >
                                </div>
                                <div class="form-group mb-4">
                                    <label for="price_before_offer">{{ __('messages.product_price') }}</label>
                                    <input required type="number" step="any" min="0" name="price_before_offer" class="form-control" id="price_before_offer" placeholder="{{ __('messages.product_price') }}" value="{{ $data['product']['price_before_offer'] != 0 ? $data['product']['price_before_offer'] : $data['product']['final_price'] }}" >
                                </div>
                                <div class="form-group mb-4">
                                    <label for="stored_number">{{ __('messages.product_stored_number') }}</label>
                                    <input type="text" name="stored_number" class="form-control" id="stored_number" placeholder="{{ __('messages.product_stored_number') }}" value="{{ empty($data['product']['stored_number']) ? '' : $data['product']['stored_number'] }}" >
                                </div>
                                <div class="form-group mb-4">
                                    <label for="title_en">{{ __('messages.barcode') }}</label>
                                    <input required type="text" name="barcode" class="form-control" id="barcode" placeholder="{{ __('messages.barcode') }}" value="{{ empty($data['product']['barcode']) ? $data['barcode'] : $data['product']['barcode'] }}" >
                                </div>
                            </div>
                            
                            
                            <div style="margin-bottom: 20px; margin-top : 20px" class="col-md-3" >
                                <div >
                                   <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                                     <input {{ $data['product']['offer'] == 1 ? 'checked' : '' }} id="discount" name="offer" value="1" type="checkbox" class="new-control-input">
                                     <span class="new-control-indicator"></span><span class="new-chk-content">{{ __('messages.discount') }}</span>
                                   </label>
                               </div>     
                            </div>
                            <div style="display:{{ $data['product']['offer_percentage'] == 0 ? 'none' : '' }}" class="form-group mb-4">
                                <label for="offer_percentage">{{ __('messages.discount_value') }} ( % )</label>
                                <input {{ $data['product']['offer_percentage'] == 0 ? 'disabled valid=0' : 'valid=1' }} type="number" step="any" min="0" name="offer_percentage" class="form-control" id="offer_percentage" placeholder="{{ __('messages.discount_value') }}" value="{{ $data['product']['offer_percentage'] }}" >
                            </div>
                            <div style="display: {{ count($data['product']->multiOptions) > 0 ? 'none' : '' }}" id="single-discount">
                                <div style="display:{{ $data['product']['offer_percentage'] == 0 ? 'none' : '' }}" class="form-group mb-4">
                                    <label for="final_price">{{ __('messages.price_after_discount') }}</label>
                                    <input disabled type="number" step="any" min="0" name="final_price" class="form-control" id="final_price" placeholder="{{ __('messages.price_after_discount') }}" value="{{ $data['product']['final_price'] }}" >
                                </div>
                            </div>
                            
                             @if (count($data['Home_sections']) > 0)
                            <div style="margin-bottom: 20px" class="col-md-3" >
                                <div >
                                <label class="new-control new-checkbox new-checkbox-text checkbox-primary">
                                    <input id="add_home" {{ count($data['elements']) > 0 ? 'checked' : '' }} value="1" type="checkbox" class="new-control-input">
                                    <span class="new-control-indicator"></span><span class="new-chk-content">{{ __('messages.add_product_to_home_section') }}</span>
                                </label>
                            </div>     
                            </div>

                            <div style="display: {{ count($data['elements']) > 0 ? '' : 'none' }}" class="form-group">
                                <label for="home_section">{{ __('messages.home_section') }}</label>
                                <select  id="home_section" name="home_section" class="form-control">
                                    <option value="0" selected>{{ __('messages.select') }}</option>
                                    @foreach ( $data['Home_sections'] as $section )
                                    <option {{(in_array($section->id , $data['elements'])? 'selected' : '')}} value="{{ $section->id }}">{{ App::isLocale('en') ? $section->title_en : $section->title_ar }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif 

                        </section>
                    </div>
        
                </div>
            </div>

        </form>
    </div>
@endsection