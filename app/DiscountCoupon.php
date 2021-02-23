<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountCoupon extends Model
{
    protected $fillable = ['code', 'value', 'period', 'max_discount'];
}