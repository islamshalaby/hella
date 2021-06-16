<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserAddress;
use App\Area;
use App\Visitor;
use App\Product;
use App\ProductImage;
use App\Cart;
use App\Order;
use App\OrderItem;
use App\Setting;
use App\ProductMultiOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use Carbon\Carbon;


class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['excute_pay' , 'pay_sucess' , 'pay_error']]);
    }
    
    public function create(Request $request){
        $now = Carbon::now();
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $orderNumber = $now->year . $now->month . $now->day . "01";
        if ($lastOrder) {
            $subSOrder = (int)$lastOrder->id + 1;
            if ($subSOrder < 9) {
                $subSOrder = '0' . $subSOrder;
            }
            $orderNumber = $now->year . $now->month . $now->day . $subSOrder;
        }
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'address_id' => 'required',
            'payment_method' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة'  , null , $request->lang);
            return response()->json($response , 406);
        }

        $user_id = auth()->user()->id;
        $visitor  = Visitor::where('unique_id' , $request->unique_id)->first();
        $user_id_unique_id = $visitor->user_id;
        $visitor_id = $visitor->id;
        $cart = Cart::where('visitor_id' , $visitor_id)->get();

        if(count($cart) == 0){
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة'  , null , $request->lang);
            return response()->json($response , 406);
        }

        $sub_total_price = 0;
        for($i = 0; $i < count($cart); $i++){
            $product = Product::select('id' , 'final_price' , 'remaining_quantity')->find($cart[$i]['product_id']);
            // dd($user_id);
            if ($cart[$i]['option_id'] != 0) {
                $productmoption = ProductMultiOption::select('remaining_quantity')->where('id', $cart[$i]['option_id'])->first();
                if($productmoption->remaining_quantity < $cart[$i]['count']){
                    $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                    return response()->json($response , 406);
                }
            }
            if($product->remaining_quantity < $cart[$i]['count']){
                $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                return response()->json($response , 406);
            }
            if($request->payment_method == 2 || $request->payment_method == 3){
                $product->remaining_quantity = $product->remaining_quantity - $cart[$i]['count'];
                
                $product->save();
                if ($cart[$i]['option_id'] != 0) {
                    for ($n = 0; $n < count($product->multiOptions); $n ++) {
                        if ($product->multiOptions[$n]['id'] == $cart[$i]['option_id']) {
                            $moption = ProductMultiOption::select('remaining_quantity', 'id')->where('id', $cart[$i]['option_id'])->first();
                            $moption->remaining_quantity = $moption->remaining_quantity - $cart[$i]['count'];
                            $moption->save();
                        }
                    }
                }
                
            }
            $sub_total_price = $sub_total_price + ($product['final_price'] * $cart[$i]['count']);
            if ($cart[$i]['option_id'] != 0) {
                for ($n = 0; $n < count($product->multiOptions); $n ++) {
                    if ($product->multiOptions[$n]['id'] == $cart[$i]['option_id']) {
                        $subTB = $sub_total_price + ($product->multiOptions[$n]['final_price'] * $cart[$i]['count']);
                        $sub_total_price = number_format((float)$subTB, 3, '.', '');
                    }
                }
            }
        }

        $area_id = UserAddress::find($request->address_id)['area_id'];
        $delivery_cost = Area::find($area_id)['delivery_cost'];

    if($request->payment_method == 2 || $request->payment_method == 3){
        $minFreeDelivery = Setting::find(1)['min_free_delivery'];
        if ($sub_total_price >= $minFreeDelivery) {
            $delivery_cost = 0;
        }
        $tB = $sub_total_price + $delivery_cost;
        $total_price = number_format((float)$tB, 3, '.', '');
        $discount = 0;
        $discount_value = 0;
        if (isset($request->discount_value)) {
            if ($total_price >= $request->discount_value ) {
                $tB = $total_price - $request->discount_value;
                $total_price = number_format((float)$tB, 3, '.', '');
            }else {
                $total_price = 0;
            }
            $discount = 1;
            $discount_value = $request->discount_value;
        }
        
        $order = new Order();
        $order->user_id = $user_id;
        $order->address_id = $request->address_id;
        $order->payment_method = $request->payment_method;
        $order->subtotal_price = $sub_total_price;
        $order->delivery_cost = number_format((float)$delivery_cost, 3, '.', '');
        $order->total_price = $total_price;
        $order->discount = $discount;
        $order->discount_value = $discount_value;
        $order->order_number = $orderNumber;
        $order->save();

        for($i = 0; $i < count($cart); $i++){
            $order_item =  new OrderItem();
            $order_item->order_id = $order->id;
            $order_item->product_id = $cart[$i]['product_id'];
            $order_item->count = $cart[$i]['count'];
            if ($cart[$i]['option_id'] != 0) {
                $prodOption = ProductMultiOption::where('id', $cart[$i]['option_id'])->first();
                if ($prodOption) {
                    $order_item->option_en = $prodOption['option_en'];
                    $order_item->option_ar = $prodOption['option_ar'];
                    $order_item->val_en = $prodOption['val_en'];
                    $order_item->val_ar = $prodOption['val_ar'];
                    $order_item->final_price = number_format((float)$prodOption['final_price'], 3, '.', '');
                    $order_item->price_before_offer = number_format((float)$prodOption['price_before_offer'], 3, '.', '');
                }
                $order_item->option_id = $cart[$i]['option_id'];
            }
            $order_item->save();
            $cartItem = Cart::find($cart[$i]['id']);
            $cartItem->delete();                       
        }

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , [] , $request->lang);
		$response['androidData'] = new \stdClass();
		
        return response()->json($response , 200);
    }else{
        $root_url = $request->root();
        $user = auth()->user();

        $path='https://api.myfatoorah.com/v2/SendPayment';
        $token="bearer sSoJeykHf_6sxsFLdshxb1R965vveINgLDs_wY5dON7wubKSRp6F1fgRS4IKk8XRk1FVvZnd_J98wPlWLTYykFTGXTDvjDGbNKqYV_LGy5OKBcVkJZgn2mT_BjoX4Rd7m94qjaxDN4SU9CH4NVvWEE-i46ypk32fZ0RZ6vWHY7vDJikE2yyJdFOKFPcXxTk5Go1lI2_hdJUtQPd5t8LGnasG8XXrQPAGjORpVzHVcti3P_Ck4A2SIa3MslbbBQqPYAgmB5PcI4iW7WlSsnh2Zd3Wx9PMjjLBqLa4ezbdYAoH-WIHI8vthXt2lRXNtEQa0upij-otTeoBEBXWSq7sO8jmEc_mU73NvBYCEnxTcUFIWTgieCmZ0cdN5B4EhTLT65F-po0QRq-i-VikrLbeXSbE8acqgv-yhtHCu6eQ0ERXwZnb6RRloHBoqSLUDTVF-bCUj6dxfSZfBwy9r9wGpdqh4fcXwyh0kPdNcMGzlcLS8A6KBRoPuHMlgOVF-KKZ-tFbBKZEKVSMS_eLcHet-_5hTw6Q781A9FFUcN5--7uYYJHIq9PrDWoknJODLxof31YQYJxHm7uQ1lhp3BfM9HfTL9sMDfZNv6iDRWqyuTZ-bw9tjUB0AEhba_Gdlm0u8xOwSD9XkFlvvRUC-eCALtuXroylLJWpnl0AXe0OLTkWHRUJ0Wh1F9X8nlq67tKxlXTtZA";

        $headers = array(
            'Authorization:' .$token,
            'Content-Type:application/json'
        );
        $pR = $sub_total_price + $delivery_cost;
        $price = number_format((float)$pR, 3, '.', '');
        if (isset($request->discount_value)) {
            $pR = $price - $request->discount_value;
            $price = number_format((float)$pR, 3, '.', '');
            $call_back_url = $root_url."/api/excute_pay?user_id=".$user->id."&unique_id=".$request->unique_id."&address_id=".$request->address_id."&payment_method=".$request->payment_method."&discount_value=".$request->discount_value;
        }else {
            $call_back_url = $root_url."/api/excute_pay?user_id=".$user->id."&unique_id=".$request->unique_id."&address_id=".$request->address_id."&payment_method=".$request->payment_method;
        }
        
        
        $error_url = $root_url."/api/pay/error";
        $fields =array(
            "CustomerName" => $user->name,
            "NotificationOption" => "LNK",
            "InvoiceValue" => $price,
            "CallBackUrl" => $call_back_url,
            "ErrorUrl" => $error_url,
            "Language" => "AR",
            "CustomerEmail" => $user->email
        );  

        $payload =json_encode($fields);
        $curl_session =curl_init();
        curl_setopt($curl_session,CURLOPT_URL, $path);
        curl_setopt($curl_session,CURLOPT_POST, true);
        curl_setopt($curl_session,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl_session,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_session,CURLOPT_IPRESOLVE, CURLOPT_IPRESOLVE);
        curl_setopt($curl_session,CURLOPT_POSTFIELDS, $payload);
        $result=curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result);
		//dd($result);
		$data['url'] = $result->Data->InvoiceURL;
		
        $response = APIHelpers::createApiResponse(false , 200 ,  '' , '' , $data , $request->lang );
		$response['androidData'] = new \stdClass();
		$response['androidData']->url = $result->Data->InvoiceURL;
        return response()->json($response , 200); 

    }
    }


    public function excute_pay(Request $request){
        $now = Carbon::now();
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $orderNumber = $now->year . $now->month . $now->day . "01";
        if ($lastOrder) {
            $subSOrder = (int)$lastOrder->id + 1;
            if ($subSOrder < 9) {
                $subSOrder = '0' . $subSOrder;
            }
            $orderNumber = $now->year . $now->month . $now->day . $subSOrder;
        }
        $user = User::find($request->user_id);
        $user_id = $user->id;
        $visitor  = Visitor::where('unique_id' , $request->unique_id)->first();
        $user_id_unique_id = $visitor->user_id;
        $visitor_id = $visitor->id;
        $cart = Cart::where('visitor_id' , $visitor_id)->get();


        $sub_total_price = 0;
        for($i = 0; $i < count($cart); $i++){
            $product = Product::select('id' , 'final_price' , 'remaining_quantity')->find($cart[$i]['product_id']);
       
                $product->remaining_quantity = $product->remaining_quantity - $cart[$i]['count'];
                // return $product->remaining_quantity ;
                $product->save();
			if ($cart[$i]['option_id'] != 0) {
                    for ($n = 0; $n < count($product->multiOptions); $n ++) {
                        if ($product->multiOptions[$n]['id'] == $cart[$i]['option_id']) {
                            $moption = ProductMultiOption::select('remaining_quantity', 'id')->where('id', $cart[$i]['option_id'])->first();
                            $moption->remaining_quantity = $moption->remaining_quantity - $cart[$i]['count'];
                            
                            $moption->save();
                            
                        }
                    }
                }
    
            $sTB = $sub_total_price + ($product['final_price'] * $cart[$i]['count']);
            $sub_total_price = number_format((float)$sTB, 3, '.', '');
            if ($cart[$i]['option_id'] != 0) {
                for ($n = 0; $n < count($product->multiOptions); $n ++) {
                    if ($product->multiOptions[$n]['id'] == $cart[$i]['option_id']) {
                        $sTB = $sub_total_price + ($product->multiOptions[$n]['final_price'] * $cart[$i]['count']);
                        $sub_total_price = number_format((float)$sTB, 3, '.', '');
                    }
                }
            }

        }

        $area_id = UserAddress::find($request->address_id)['area_id'];
        $delivery_cost = Area::find($area_id)['delivery_cost'];
        $minFreeDelivery = Setting::find(1)['min_free_delivery'];
        if ($sub_total_price >= $minFreeDelivery) {
            $delivery_cost = 0;
        }
        $tB = $sub_total_price + $delivery_cost;
        $total_price = number_format((float)$tB, 3, '.', '');
        $discount = 0;
        $discount_value = 0;
        if (isset($request->discount_value)) {
            $tB = $total_price - $request->discount_value;
            $total_price = number_format((float)$tB, 3, '.', '');
            $discount = 1;
            $discount_value = $request->discount_value;
        }

        $order = new Order();
        $order->user_id = $user_id;
        $order->address_id = $request->address_id;
        $order->payment_method = $request->payment_method;
        $order->subtotal_price = $sub_total_price;
        $order->delivery_cost = number_format((float)$delivery_cost, 3, '.', '');
        $order->total_price = $total_price;
        $order->discount = $discount;
        $order->discount_value = $discount_value;
        $order->order_number = $orderNumber;
        $order->save();

        for($i = 0; $i < count($cart); $i++){
            $order_item =  new OrderItem();
            $order_item->order_id = $order->id;
            $order_item->product_id = $cart[$i]['product_id'];
            $order_item->count = $cart[$i]['count'];
            if ($cart[$i]['option_id'] != 0) {
                $order_item->option_id = $cart[$i]['option_id'];
                $prodOption = ProductMultiOption::where('id', $cart[$i]['option_id'])->first();
                if ($prodOption) {
                    $order_item->option_en = $prodOption['option_en'];
                    $order_item->option_ar = $prodOption['option_ar'];
                    $order_item->val_en = $prodOption['val_en'];
                    $order_item->val_ar = $prodOption['val_ar'];
                    $order_item->final_price = $prodOption['final_price'];
                    $order_item->price_before_offer = $prodOption['price_before_offer'];

                }
            }
            $order_item->save();
            $cartItem = Cart::find($cart[$i]['id']);
            $cartItem->delete();                       
        }




        return redirect('api/pay/success'); 
    }

    public function pay_sucess(){
        return "Please wait ...";
    }

    public function pay_error(){
        return "Please wait ...";
    }


    public function getorders(Request $request){
        $user_id = auth()->user()->id;
        $orders = Order::where('user_id' , $user_id)->select('id' , 'status' , 'order_number' , 'created_at as date' , 'total_price' , 'status')->orderBy('id' , 'desc')->get();
        for($i = 0; $i < count($orders); $i++){
            $date = date_create($orders[$i]['date']);
            $orders[$i]['date'] = date_format($date , "d/m/Y");
			$orders[$i]['time'] = date_format($date , "g:i A"); 
            $ordercount = OrderItem::where('order_id' , $orders[$i]['id'])->pluck('count')->toArray();
            $orders[$i]['count'] = array_sum($ordercount);
        }
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $orders , $request->lang);
        return response()->json($response , 200);
    }
    
    public function orderdetails(Request $request){
        $order_id = $request->id;
        $order = Order::find($order_id);
        $data['id'] = $order->id;
        $data['order_number'] = $order->order_number;
        $date = date_create($order->created_at);
        $data['date'] = date_format($date ,  'd/m/Y');
        $data['status'] = $order->status;
        $data['payment_method'] = $order->payment_method;
        $data['subtotal_price'] = $order->subtotal_price;
        $data['delivery_cost'] = $order->delivery_cost;
        $data['total_price'] = $order->total_price;
		$data['coupon'] = $order->discount_value;
        $data['products_count'] = OrderItem::where('order_id' , $order_id)->count();
        $ids = OrderItem::where('order_id' , $order_id)->select('id','product_id', 'option_id')->get()->toArray();
        $products = [];
        for($i = 0; $i < count($ids); $i++){
            if($request->lang == 'en'){
                $product = Product::select('id' , 'title_en as title' , 'offer' , 'offer_percentage' , 'price_before_offer' , 'final_price')->find($ids[$i]['product_id'])->makeHidden(['multiOptions']);
            }else{
                $product = Product::select('id' , 'title_ar as title', 'offer' , 'offer_percentage' , 'price_before_offer' , 'final_price')->find($ids[$i]['product_id'])->makeHidden(['multiOptions']);
            }
            if ($ids[$i]['option_id'] != 0) {
                for ($n = 0; $n < count($product->multiOptions); $n ++) {
                    if ($ids[$i]['option_id'] == $product->multiOptions[$n]->id) {
						if ($request->lang == 'en') {
                            $product['option_name'] = $product->multiOptions[$n]->multiOption->title_en;
                            $product['option_value'] = $product->multiOptions[$n]->multiOptionValue->value_en;
                        }else {
                            $product['option_name'] = $product->multiOptions[$n]->multiOption->title_ar;
                            $product['option_value'] = $product->multiOptions[$n]->multiOptionValue->value_ar;
                        }
                        $product['final_price'] = $product->multiOptions[$n]->final_price;
                        $product['price_before_offer'] = $product->multiOptions[$n]->price_before_offer;
                    }
                }
            }
            $product['count'] = OrderItem::find($ids[$i]['id'])['count'];
            
            $product['image'] = ProductImage::where('product_id' , $product->id)->first()['image'];
            array_push($products , $product);
        }
        $address = UserAddress::select('gaddah' , 'building' , 'floor' , 'apartment_number' , 'street')->find($order->address_id);
        if($address){
            $data['address'] = $address;
        }else{
            $data['address'] = new \stdClass();
        }

        $data['products'] = $products;
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);

    }

    

}