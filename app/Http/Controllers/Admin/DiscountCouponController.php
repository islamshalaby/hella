<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\DB;
use App\DiscountCoupon;
use Carbon\Carbon;

class DiscountCouponController extends AdminController{
    // index
    public function show() {
        $data['coupons'] = DiscountCoupon::get();

        return view('admin.discount_coupones', ['data' => $data]);
    }

    // add get
    public function AddGet() {
        return view('admin.discount_coupon_form');
    }

    // add post
    public function AddPost(Request $request) {
        $request->validate([
            'code' => 'required|unique:discount_coupons,code',
            'value' => 'required',
            'period' => 'required'
        ]);
        $post = $request->all();

        DiscountCoupon::create($post);
        return redirect()->route('discount_coupons.index');
    }

    // edit get
    public function EditGet(DiscountCoupon $coupon) {
        $data['coupon'] = $coupon;

        return view('admin.discount_coupon_edit', ['data' => $data]);
    }

    // edit post
    public function EditPost(Request $request, DiscountCoupon $coupon) {
        $request->validate([
            'code' => 'required|unique:discount_coupons,code,' . $coupon->id,
            'value' => 'required',
            'period' => 'required'
        ]);
        $post = $request->all();

        $coupon->update($post);

        return redirect()->route('discount_coupons.index');
    }

    // details
    public function details(DiscountCoupon $coupon) {
        $data['coupon'] = $coupon;
        $data['status'] = true;
        if (Carbon::now()->toDateTimeString() >= $data['coupon']['created_at']->addDays($coupon->period)) {
            $data['status'] = false;
        }

        return view('admin.discount_coupon_details', ['data' => $data]);
    }

    // delete
    public function delete(DiscountCoupon $coupon) {
        $coupon->delete();

        return redirect()->back();
    }
}