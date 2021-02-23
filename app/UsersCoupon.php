<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsersCoupon extends Model
{
    protected $fillable = ['user_id', 'coupon_id'];
}