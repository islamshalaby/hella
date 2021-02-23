<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductMultiOption extends Model
{
    protected $fillable = [
        'product_id', 
        'multi_option_id', 
        'multi_option_value_id', 
        'final_price',
        'price_before_offer',
        'total_quatity', 
        'remaining_quantity',
        'barcode',
        'stored_number',
        'sold_count',
        "option_en",
        "option_ar",
        "val_en",
        "val_ar"
    ];

    protected $hidden = ['multi_option', 'created_at', 'updated_at', 'pivot'];

    public function multiOptionValue() {
        return $this->belongsTo('App\MultiOptionValue', 'multi_option_value_id');
    }

    public function multiOption() {
        return $this->belongsTo('App\MultiOption', 'multi_option_id');
    }
}