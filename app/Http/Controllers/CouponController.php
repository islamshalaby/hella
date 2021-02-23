<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\DiscountCoupon;
use App\UsersCoupon;
use App\Visitor;
use Carbon\Carbon;


class CouponController extends Controller
{
    // coupon validity
    public function coupon_validity(Request $request) {
        $validator = Validator::make($request->all(), [
            'coupon' => 'required',
            'total_cost' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة'  , null , $request->lang);
            return response()->json($response , 406);
        }

        $user = auth()->user();

        $coupon = DiscountCoupon::where('code', $request->coupon)->first();
        if (! isset($coupon['id'])) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Coupon does not exist' , 'كوبون غير موجود'  , null , $request->lang);
            return response()->json($response , 406);
        }
        $user_coupons = UsersCoupon::where('user_id', $user->id)->pluck('coupon_id')->toArray();
        // dd($coupon['created_at']->addDays($coupon['period']));
        if(Carbon::now()->toDateTimeString() > $coupon['created_at']->addDays($coupon['period'])) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Coupon expired' , 'كوبون غير صالح'  , null , $request->lang);
            return response()->json($response , 406);
        }

        if (isset($user->coupons) && count($user->coupons) > 0) {
            if (in_array($coupon['id'], $user_coupons)) {
                $response = APIHelpers::createApiResponse(true , 406 , 'Coupon used before' , 'كوبون تم إستخدامه من قبل'  , null , $request->lang);
                return response()->json($response , 406);
            }
        }

        UsersCoupon::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon['id']
        ]);

        $data['discount_code'] = $coupon['code'];
        $data['discount_percentage'] = $coupon['value'];
        $data['total_without_discount'] = $request->total_cost;
		$data['total_without_d'] = number_format((float)$request->total_cost, 3, '.', '');
        $data['discount_value'] = $data['total_without_discount'] * ($coupon['value'] / 100);
		$data['discount_v'] = number_format((float)$data['discount_value'], 3, '.', '');
        $data['total_with_discount'] = $data['total_without_discount'] - $data['discount_value'];
		$data['total_w_discount'] = number_format((float)$data['total_with_discount'], 3, '.', '');
        if ($data['discount_value'] > $coupon['max_discount']) {
            $data['total_with_discount'] = $data['total_without_discount'] - $coupon['max_discount'];
			$data['total_w_discount'] = number_format((float)$data['total_with_discount'], 3, '.', '');
			$data['discount_value'] = (double)$coupon['max_discount'];
			$data['discount_v'] = number_format((float)$coupon['max_discount'], 3, '.', '');
        }

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

}