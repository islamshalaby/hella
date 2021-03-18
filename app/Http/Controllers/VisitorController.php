<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Visitor;
use App\Cart;
use App\Favorite;
use App\Product;
use App\ProductImage;
use App\Setting;
use App\ProductMultiOption;

class VisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['create' , 'add' , 'delete' , 'get' , 'changecount' , 'getcartcount', 'freeDelivery']]);
    }

    // create visitor 
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            //'fcm_token' => "required",
            'type' => 'required' // 1 -> iphone ---- 2 -> android
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $last_visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($last_visitor){
			if (isset($request->fcm_token)) {
				$last_visitor->fcm_token = $request->fcm_token;
				$last_visitor->save();
			}
            
            $visitor = $last_visitor;
        }else{
            $visitor = new Visitor();
            $visitor->unique_id = $request->unique_id;
			if (isset($request->fcm_token)) {
				$visitor->fcm_token = $request->fcm_token;
			}
            $visitor->type = $request->type;
            $visitor->save();
        }


        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $visitor , $request->lang);
        return response()->json($response , 200);
    }

    // add to cart
    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'product_id' => 'required',
            'count' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){
            $option_id = 0;
            if (isset($request->option_id)) {
                $option_id = $request->option_id;
                $cart = Cart::where('visitor_id' , $visitor->id)->where('product_id' , $request->product_id)->where('option_id', $request->option_id)->first();
                $product_m_option = ProductMultiOption::select('remaining_quantity')->where('id', $request->option_id)->first();
                if ($product_m_option->remaining_quantity < 1) {
                    $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                    return response()->json($response , 406);
                }
            }else {
                $cart = Cart::where('visitor_id' , $visitor->id)->where('product_id' , $request->product_id)->first();
                $product = Product::find($request->product_id);
                if($product->remaining_quantity < 1){
                    $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                    return response()->json($response , 406);
                }
            }
            if($cart){
                $cart->count = $request->count;
                $cart->save();
            }else{
                $cart = new Cart();
                $cart->count = $request->count;
                $cart->product_id = $request->product_id;
                $cart->visitor_id = $visitor->id;
                $cart->option_id = $option_id;
                $cart->save();
            }

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $cart , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }

    }

    // remove from cart
    public function delete(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'product_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){

            if (isset($request->option_id) || $request->option_id != 0) {
                $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->where('option_id', $request->option_id)->first();
            }else {
                $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->first();
            }
            $cart->delete();

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }



    }

    // get cart
    public function get(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){
            $visitor_id =  $visitor['id'];
            $cart = Cart::where('visitor_id' , $visitor_id)->select('product_id as id' , 'count', 'option_id')->get();
            $data['subtotal_price'] = 0;
            for($i = 0; $i < count($cart); $i++){
                if($request->lang == 'en'){
                    $product = Product::with('multiOptions')->select('id', 'title_en as title' , 'offer' , 'offer_percentage' , 'final_price' , 'price_before_offer', 'remaining_quantity')->find($cart[$i]['id']);
                }else{
                    $product = Product::with('multiOptions')->select('id', 'title_ar as title' , 'offer' , 'offer_percentage' , 'final_price' , 'price_before_offer', 'remaining_quantity')->find($cart[$i]['id']);
                }

                if(auth()->user()){
                    $user_id = auth()->user()->id;
                    $prevfavorite = Favorite::where('product_id' , $cart[$i]['id'])->where('user_id' , $user_id)->first();
                    if($prevfavorite){
                        $cart[$i]['favorite'] = true;
                    }else{
                        $cart[$i]['favorite'] = false;
                    }
    
                }else{
                    $cart[$i]['favorite'] = false;
                }

                if ($cart[$i]['option_id'] != 0) {
                    // dd($product->multiOptions);
                    for ($k = 0; $k < count($product->multiOptions); $k ++) {
                        // dd($product->multiOptions[$k]->id);
                        if ($product->multiOptions[$k]->id == $cart[$i]['option_id']) {
                            $f_price = $product->multiOptions[$k]->final_price * $cart[$i]['count'];
							//dd($product->multiOptions[$k]->final_price * $cart[$i]['count']);
                            $cart[$i]['final_price'] = number_format((float)$f_price,3,'.','');
                            $p_before = $product->multiOptions[$k]->price_before_offer* $cart[$i]['count'];
                            $cart[$i]['price_before_offer'] = number_format((float)$p_before,3,'.','');
							$cart[$i]['remaining_quantity'] = $product->multiOptions[$k]->remaining_quantity;
                            $data['subtotal_price'] = $data['subtotal_price'] + ($product->multiOptions[$k]->final_price * $cart[$i]['count']);
if ($request->lang == 'en') {
                                $cart[$i]['option_name'] = $product->multiOptions[$k]->multiOption->title_en;
                                $cart[$i]['option_value'] = $product->multiOptions[$k]->multiOptionValue->value_en;
                            }else {
                                $cart[$i]['option_name'] = $product->multiOptions[$k]->multiOption->title_ar;
                                $cart[$i]['option_value'] = $product->multiOptions[$k]->multiOptionValue->value_ar;
                            }
                        }
                        
                    }
                }else {
                    $f_price = $product['final_price'] * $cart[$i]['count'];
                    $cart[$i]['final_price'] = number_format((float)$f_price,3,'.','');
                    $p_before = $product['price_before_offer'] * $cart[$i]['count'];
                    $cart[$i]['price_before_offer'] = number_format((float)$p_before,3,'.','');
					$cart[$i]['remaining_quantity'] = $product['remaining_quantity'];
                    //$data['subtotal_price'] = $data['subtotal_price'] + ($product['final_price'] * $cart[$i]['count']);
                }
				

                $cart[$i]['title'] = $product['title'];
				$cart[$i]['offer'] = $product['offer'];
				$cart[$i]['offer_percentage'] = $product['offer_percentage'];
                
                $cart[$i]['image'] = ProductImage::select('image')->where('product_id' , $cart[$i]['id'])->first()['image'];
                $subTotal = $data['subtotal_price'] + ($product['final_price'] * $cart[$i]['count']);
                $data['subtotal_price'] = floatval(number_format((float)$subTotal, 3, '.', ''));
				$data['sub_t_price'] = number_format((float)$subTotal, 3, '.', '');
            }
            
            $data['cart'] = $cart;
            $minFreeDelivery = Setting::find(1)['min_free_delivery'];
            $data['count'] = count($cart);
			
            $data['min_free_delivery'] = number_format((float)$minFreeDelivery, 3, '.', '');
            $data['free_delivery'] = 0;
            if ($data['subtotal_price'] >= $data['min_free_delivery']) {
                $data['free_delivery'] = 1;
            }
            
            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
        

    }

    // get cart count 
    public function getcartcount(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){

            $visitor_id =  $visitor['id'];
            $cart = Cart::where('visitor_id' , $visitor_id)->select('product_id as id' , 'count')->get();
            $count['count'] = count($cart);

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $count , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    // change count
    public function changecount(Request $request){
 
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'product_id' => 'required',
            'new_count' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();

        $product = Product::find($request->product_id);
        if (isset($request->option_id)) {
            $product_m_option = ProductMultiOption::select('remaining_quantity')->where('id', $request->option_id)->first();
            if($product_m_option->remaining_quantity < $request->new_count){
                $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                return response()->json($response , 406);
            }
        }else {
            $product = Product::find($request->product_id);
            if($product->remaining_quantity < $request->new_count){
                $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                return response()->json($response , 406);
            }
        }

        if($visitor){

            if (isset($request->option_id) || $request->option_id != 0) {
                $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->where('option_id', $request->option_id)->first();
            }else {
                $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->first();
            }

            if (isset($cart->count)) {
                $cart->count = $request->new_count;
                $cart->save();
                $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $cart , $request->lang);
                return response()->json($response , 200);
            }else {
                $response = APIHelpers::createApiResponse(true , 406 , 'This product is not exist in cart' , 'هذا المنتج غير موجود بالعربة' , null , $request->lang);
                return response()->json($response , 406);
            }

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
        
    }

    // free delivery
    public function freeDelivery(Request $request) {
        $data['min_free_delivery'] = Setting::find(1)['min_free_delivery'];

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }


}