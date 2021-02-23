<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['image', 'title_en', 'title_ar', 'deleted'];

    public function brands() {
        return $this->hasMany('App\Brand', 'category_id');
    }

    public function products() {
        return $this->hasMany('App\Product', 'category_id')->where('deleted', 0)->where('hidden', 0);
    }

    public function options() {
        return $this->hasMany('App\Option', 'category_id');
    }

    public function multiOptions() {
        return $this->belongsToMany('App\MultiOption', 'multi_options_categories', 'category_id', 'multi_option_id');
    }

    public function multiOptionsWithValues() {
        return $this->multiOptions()->with('values');
    }

    public function subCategories() {
        return $this->hasMany('App\SubCategory', 'category_id');
    }
}
