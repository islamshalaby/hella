<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    protected $fillable = [
        'barcode',
        'stored_number',
        'title_en',
        'title_ar',
        'offer', 
        'description_ar', 
        'description_en', 
        'final_price', 
        'price_before_offer',
        'offer_percentage',
        'category_id',
        'brand_id',
        'sub_category_id',
        'sub_two_category_id',
        'deleted',
        'total_quatity',
        'remaining_quantity',
        'hidden',
        'weight',
		'kg_en',
        'kg_ar',
		'numbers',
        'multi_options'
    ];

    public function images() {
        return $this->hasMany('App\ProductImage', 'product_id');
    }

    public function mainImage() {
        return $this->hasOne('App\ProductImage')->oldest();
    }

    public function category() {
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function brand() {
        return $this->belongsTo('App\Brand', 'brand_id');
    }

    public function subCategory() {
        return $this->belongsTo('App\SubCategory', 'sub_category_id');
    }

    public function options() {
        return $this->hasMany('App\ProductOption', 'product_id');
    }

    public function orderItems() {
        return $this->hasMany('App\OrderItem', 'product_id');
    }

    public function orders() {
        return $this->belongsToMany('App\Order', 'order_items', 'product_id','order_id')->withPivot('count');
    }

    public function properties() {
        return $this->belongsToMany('App\Option', 'product_properties', 'product_id', 'option_id');
    }

    public function values() {
        return $this->belongsToMany('App\OptionValue', 'product_properties', 'product_id', 'value_id');
    }

    public function specValues() {
        return $this->belongsToMany('App\OptionValue', 'product_properties', 'product_id', 'value_id')->select('value_en as value', 'value_ar as value');
    }

    public function mOptions() {
        return $this->belongsToMany('App\MultiOption', 'product_multi_options', 'product_id', 'multi_option_id');
    }

    public function mOptionsValuesEn() {
        return $this->belongsToMany('App\MultiOptionValue', 'product_multi_options', 'product_id', 'multi_option_value_id')->select('value_en as value', 'multi_option_values.id as option_value_id')->where('product_multi_options.remaining_quantity', '>', 0);
    }

    public function mOptionsValuesAr() {
        return $this->belongsToMany('App\MultiOptionValue', 'product_multi_options', 'product_id', 'multi_option_value_id')->select('value_ar as value', 'multi_option_values.id as option_value_id', 'product_multi_options.remaining_quantity')->where('product_multi_options.remaining_quantity', '>', 0);
    }

    public function multiOptions() {
        return $this->hasMany('App\ProductMultiOption', 'product_id');
    }

    public function mptions() {
        return $this->hasMany('App\ProductMultiOption', 'product_id');
    }

    public function productProperties() {
        return $this->hasMany('App\ProductProperty', 'product_id');
    }

    public function productBrands() {
        return $this->belongsToMany('App\Brand', 'product_brands', 'product_id', 'brand_id')->select('brands.*');
    }
}
