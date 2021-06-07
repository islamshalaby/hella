<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Product;
use App\Category;
use App\Brand;
use App\ProductMultiOption;
use App\SubCategory;
use App\SubTwoCategory;
use App\ProductImage;
use App\Option;
use App\ProductOption;
use App\Favorite;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getdetails' , 'getproducts' , 'getbrandproducts', 'fetchBrandProducts', 'fetchCategoryProducts']]);
    }


    public function getdetails(Request $request){
        $id = $request->id;
        if($request->lang == 'en'){
            $data['product'] = Product::select('id' , 'title_en as title' , 'description_en as description' , 'offer' , 'price_before_offer' , 'final_price' , 'offer_percentage' , 'category_id' , 'remaining_quantity', 'multi_options')->find($id)->makeHidden(['mOptions', 'mOptionsValuesEn', 'mOptionsValuesAr']);
            if ($data['product']['multi_options'] == 1) {
                $multi_options = [];
                $multi_options['option_name'] = $data['product']->mOptions[0]['title_en'];
                $multi_options['option_values'] = $data['product']->mOptionsValuesEn;
                
                for($k = 0; $k < count($multi_options["option_values"]); $k ++) {
                    $product_m_option = ProductMultiOption::select('final_price', 'price_before_offer', 'total_quatity', 'remaining_quantity', 'barcode', 'stored_number', 'multi_option_value_id as option_value_id', 'product_multi_options.id as option_id')->where('product_id', $data['product']['id'])->where('remaining_quantity', '>', 0)->where('multi_option_value_id', $multi_options["option_values"][$k]['option_value_id'])->first();
                    $multi_options["option_values"][$k]['option_data'] = $product_m_option;
                }
                $data['product']['multiple_option'] = $multi_options;
            }
            $data['product']['category_name'] = Category::select('title_en')->find($data['product']['category_id'])->title_en;

            $product_options = ProductOption::where('product_id' , $data['product']['id'])->select('id' , 'option_id' , 'value_en as value')->get();
            for($i = 0 ; $i < count($product_options) ; $i++){
                $product_options[$i]['key'] = Option::find($product_options[$i]['option_id'])->title_en;
            }

        }else{
            $data['product'] = Product::select('id' , 'title_ar as title' , 'description_ar as description' , 'offer' , 'price_before_offer' , 'final_price' , 'offer_percentage' , 'category_id' , 'remaining_quantity', 'multi_options')->find($id)->makeHidden(['mOptions', 'mOptionsValuesEn', 'mOptionsValuesAr']);
            if ($data['product']['multi_options'] == 1) {
                $multi_options = [];
                $multi_options['option_name'] = $data['product']->mOptions[0]['title_ar'];
                $multi_options['option_values'] = $data['product']->mOptionsValuesAr;
                for($k = 0; $k < count($multi_options["option_values"]); $k ++) {
                    $product_m_option = ProductMultiOption::select('final_price', 'price_before_offer', 'total_quatity', 'remaining_quantity', 'barcode', 'stored_number', 'multi_option_value_id as option_value_id', 'product_multi_options.id as option_id')->where('product_id', $data['product']['id'])->where('multi_option_value_id', $multi_options["option_values"][$k]['option_value_id'])->first();
                    $multi_options["option_values"][$k]['option_data'] = $product_m_option;
                }
                $data['product']['multiple_option'] = $multi_options;
            }
            $data['product']['category_name'] = Category::select('title_ar')->find($data['product']['category_id'])->title_ar;

            $product_options = ProductOption::where('product_id' , $data['product']['id'])->select('id' , 'option_id' , 'value_ar as value')->get();
            for($i = 0 ; $i < count($product_options) ; $i++){
                $product_options[$i]['key'] = Option::find($product_options[$i]['option_id'])->title_ar;
            }
        }
        $data['product']['images'] = ProductImage::where('product_id' , $data['product']['id'])->pluck('image');

        // $data['product']['favorite'] = false;
        if(auth()->user()){
            $user_id = auth()->user()->id;

            $prevfavorite = Favorite::where('product_id' , $data['product']['id'])->where('user_id' , $user_id)->first();
            if($prevfavorite){
                $data['product']['favorite'] = true;
            }else{
                $data['product']['favorite'] = false;
            }

        }else{
            $data['product']['favorite'] = false;
        }

        $data['product']['options'] = $product_options;
        
        if($request->lang == 'en'){
            $data['related'] = Product::select('id', 'title_en as title' , 'final_price' , 'price_before_offer' , 'weight' ,'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_en as kg' )->where('deleted' , 0)->where('category_id' , $data['product']['category_id'])->where('id' , '!=' , $data['product']['id'])->get()->makeHidden(['mOptions', 'mOptionsValuesEn', 'mOptionsValuesAr', 'multiOptions']);
        }else{
            $data['related'] = Product::select('id', 'title_ar as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_ar as kg')->where('deleted' , 0)->where('category_id' , $data['product']['category_id'])->where('id' , '!=' , $data['product']['id'])->get()->makeHidden(['mOptions', 'mOptionsValuesEn', 'mOptionsValuesAr', 'multiOptions']);
        }
        
        for($j = 0; $j < count($data['related']) ; $j++){
            if ($data['related'][$j]['multi_options'] == 1) {
                $data['related'][$j]['final_price'] = $data['related'][$j]['multiOptions'][0]['final_price'];
                $data['related'][$j]['price_before_offer'] = $data['related'][$j]['multiOptions'][0]['price_before_offer'];
            }

            if(auth()->user()){
                $user_id = auth()->user()->id;
    
                $prevfavorite = Favorite::where('product_id' , $data['related'][$j]['id'])->where('user_id' , $user_id)->first();
                if($prevfavorite){
                    $data['related'][$j]['favorite'] = true;
                }else{
                    $data['related'][$j]['favorite'] = false;
                }
    
            }else{
                $data['related'][$j]['favorite'] = false;
            }


            if($request->lang == 'en'){
                $data['related'][$j]['category_name'] = Category::where('id' , $data['related'][$j]['category_id'])->pluck('title_en as title')->first();
            }else{
                $data['related'][$j]['category_name'] = Category::where('id' , $data['related'][$j]['category_id'])->pluck('title_ar as title')->first();
            }
            

            $data['related'][$j]['image'] = ProductImage::where('product_id' , $data['related'][$j]['id'])->pluck('image')->first();;
        }


        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    public function getproducts(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'sub_category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $sub_two_category_id = $request->sub_two_category_id;


        // if($request->lang == 'en'){
        //     $categories = Category::where('deleted' , 0)->select('id' , 'title_en as title' , 'image')->get();   
        // }else{
        //     $categories = Category::where('deleted' , 0)->select('id' , 'title_ar as title' , 'image')->get();   
        // }

        // for($i = 0; $i < count($categories); $i++){
            // if($categories[$i]['id'] == $request->category_id){
            //     $categories[$i]['selected'] = 1;


                // $categories[$i]['subcategories'] = $subcategories;
                
            // }else{
            //     $categories[$i]['selected'] = 0;
            // }
        // }

        
        if($request->lang == 'en'){
            $products = Product::select('id', 'title_en as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_en as kg' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('category_id' , $request->category_id);
        }else {
            $products = Product::select('id', 'title_ar as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_ar as kg' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('category_id' , $request->category_id);
        }
        
        if (isset($request->brand_id)) {
            if ($request->brand_id != 0) {
                $products = $products->where('brand_id', $request->brand_id);
            }
        }

        if($request->lang == 'en'){
            $subcategories = SubCategory::where('deleted' , 0)->where('category_id' , $request->category_id)->select('id' , 'title_en as title' , 'image')->get()->toArray();
            $all_element = array();
            $all_element['id'] = 0;
            $all_element['title'] = 'All';
            $all_element['image'] = '';
            array_unshift($subcategories , $all_element);
        }else{
            $subcategories = SubCategory::where('deleted' , 0)->where('category_id' , $request->category_id)->select('id' , 'title_ar as title' , 'image')->get()->toArray();
            $all_element = array();
            $all_element['id'] = 0;
            $all_element['title']  = 'الكل';
            $all_element['image'] = '';
            array_unshift($subcategories , $all_element);
        }
        for($j =0; $j < count($subcategories); $j++){
            if($subcategories[$j]['id'] == $request->sub_category_id){
                $subcategories[$j]['selected'] = 1;
            }else{
                $subcategories[$j]['selected'] = 0;
            }

        }

        $data['sub_category'] = $subcategories;
		
		if(isset($request->sub_category_id) && $request->sub_category_id != 0){
            
            $products = $products->where('sub_category_id' , $request->sub_category_id);
        }
        if($request->lang == 'en'){
            $sub2categories = SubTwoCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->select('id' , 'title_en as title' , 'image')->get()->toArray();
            $all2_element = array();
            $all2_element['id'] = 0;
            $all2_element['title'] = 'All';
            $all2_element['image'] = '';
            array_unshift($subcategories , $all2_element);
        }else{
            $sub2categories = SubTwoCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->select('id' , 'title_ar as title' , 'image')->get()->toArray();
            $all2_element = array();
            $all2_element['id'] = 0;
            $all2_element['title']  = 'الكل';
            $all2_element['image'] = '';
            array_unshift($sub2categories , $all2_element);
        }
        if (isset($request->sub_two_category_id)) {
            for($k =0; $k < count($sub2categories); $k++){
                if($sub2categories[$k]['id'] == $request->sub_two_category_id){
                    $sub2categories[$k]['selected'] = 1;
                }else{
                    $sub2categories[$k]['selected'] = 0;
                }
    
            }
        }
        
        if(isset($request->sub_two_category_id) && $request->sub_two_category_id != 0){
            
            $products = $products->where('sub_two_category_id' , $request->sub_two_category_id);
            
        }
        $data['sub_two_category'] = $sub2categories;

        $products = $products->simplePaginate(16);
        $products->makeHidden(['multiOptions']);

        for($i = 0; $i < count($products); $i++){
            if ($products[$i]['multi_options'] == 1) {
                if (count($products[$i]['multiOptions']) > 0) {
                    $products[$i]['final_price'] = $products[$i]['multiOptions'][0]['final_price'];
                    $products[$i]['price_before_offer'] = $products[$i]['multiOptions'][0]['price_before_offer'];
                    unset($products[$i]['multi_options']);
                }
            }
            if(auth()->user()){
                $user_id = auth()->user()->id;

                $prevfavorite = Favorite::where('product_id' , $products[$i]['id'])->where('user_id' , $user_id)->first();
                if($prevfavorite){
                    $products[$i]['favorite'] = true;
                }else{
                    $products[$i]['favorite'] = false;
                }

            }else{
                $products[$i]['favorite'] = false;
            }

            if($request->lang == 'en'){
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_en as title')->first();
            }else{
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_ar as title')->first();
            }
            
            $products[$i]['image'] = ProductImage::where('product_id' , $products[$i]['id'])->pluck('image')->first();
        }
        
        $data['products'] = $products;
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    public function fetchBrandProducts(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $brand = Brand::where('id', $request->brand_id)->select('cover_image')->first();
        $data['cover_image'] = "";

        if ($brand['cover_image']) {
            $data['cover_image'] = $brand['cover_image'];
        }

        $products = Product::
        select('id', 'title_' . $request->lang . ' as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_en as kg' )
        ->where('deleted' , 0)
        ->where('hidden' , 0)
        ->where('remaining_quantity', '>', 0)
        ->whereHas('productBrands', function($q) use ($request) {
            $q->where('brands.id', $request->brand_id);
        });

        $brandCategories = $products->pluck('category_id')->toArray();

        $data['categories'] = Category::where('deleted', 0)->whereIn('id', $brandCategories)->select("id", "title_" . $request->lang . " as title")->get()->toArray();
        $all = "All";
        if ($request->lang == 'ar') {
            $all = "الكل";
        }
        $allObj = [
            "id" => 0,
            "title" => $all,
            "selected" => false
        ];
        

        array_unshift($data['categories'], $allObj);

        for ($i = 0; $i < count($data['categories']); $i ++) {
            // dd($data['categories'][0]);
            if ($request->category_id == $data['categories'][$i]['id']) {
                $data['categories'][$i]['selected'] = true;
            }else {
                $data['categories'][$i]['selected'] = false;
            }
        }
        
        
        if ($request->category_id != 0) {
            $products = $products->where('category_id' , $request->category_id);
        }

        $products = $products->simplePaginate(16);
        $products->makeHidden(['multiOptions']);

        for($i = 0; $i < count($products); $i++){
            if ($products[$i]['multi_options'] == 1) {
                if (count($products[$i]['multiOptions']) > 0) {
                    $products[$i]['final_price'] = $products[$i]['multiOptions'][0]['final_price'];
                    $products[$i]['price_before_offer'] = $products[$i]['multiOptions'][0]['price_before_offer'];
                    unset($products[$i]['multi_options']);
                }
            }
            if(auth()->user()){
                $user_id = auth()->user()->id;

                $prevfavorite = Favorite::where('product_id' , $products[$i]['id'])->where('user_id' , $user_id)->first();
                if($prevfavorite){
                    $products[$i]['favorite'] = true;
                }else{
                    $products[$i]['favorite'] = false;
                }

            }else{
                $products[$i]['favorite'] = false;
            }

            if($request->lang == 'en'){
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_en as title')->first();
            }else{
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_ar as title')->first();
            }
            
            $products[$i]['image'] = ProductImage::where('product_id' , $products[$i]['id'])->pluck('image')->first();
        }

        $data['products'] = $products;


        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    public function fetchCategoryProducts(Request $request) {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $products = Product::
        select('id', 'title_' . $request->lang . ' as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'multi_options', 'numbers', 'kg_en as kg', 'sub_category_id' )
        ->where('deleted' , 0)
        ->where('hidden' , 0)
        ->where('remaining_quantity', '>', 0)
        ->where('category_id', $request->category_id);

        $categorySubCategories = $products->pluck('sub_category_id')->toArray();


        $data['sub_categories'] = SubCategory::where('deleted' , 0)->whereIn('id', $categorySubCategories)->where('category_id' , $request->category_id)->select('id' , 'title_en as title' , 'image')->get()->toArray();

        $all = "All";

        if ($request->lang == 'ar') {
            $all = "الكل";
        }

        $allObj = [
            "id" => 0,
            "title" => $all,
            "selected" => false
        ];

        array_unshift($data['sub_categories'], $allObj);

        for ($i = 0; $i < count($data['sub_categories']); $i ++) {
            if ($request->sub_category_id == $data['sub_categories'][$i]['id']) {
                $data['sub_categories'][$i]['selected'] = true;
            }else {
                $data['sub_categories'][$i]['selected'] = false;
            }
        }
        if ($request->sub_category_id != 0) {
            $products = $products->where('sub_category_id', $request->sub_category_id);
        }

        $products = $products->simplePaginate(16);
        $products->makeHidden(['multiOptions']);

        for($i = 0; $i < count($products); $i++){
            if ($products[$i]['multi_options'] == 1) {
                if (count($products[$i]['multiOptions']) > 0) {
                    $products[$i]['final_price'] = $products[$i]['multiOptions'][0]['final_price'];
                    $products[$i]['price_before_offer'] = $products[$i]['multiOptions'][0]['price_before_offer'];
                    unset($products[$i]['multi_options']);
                }
            }
            if(auth()->user()){
                $user_id = auth()->user()->id;

                $prevfavorite = Favorite::where('product_id' , $products[$i]['id'])->where('user_id' , $user_id)->first();
                if($prevfavorite){
                    $products[$i]['favorite'] = true;
                }else{
                    $products[$i]['favorite'] = false;
                }

            }else{
                $products[$i]['favorite'] = false;
            }

            if($request->lang == 'en'){
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_en as title')->first();
            }else{
                $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_ar as title')->first();
            }
            
            $products[$i]['image'] = ProductImage::where('product_id' , $products[$i]['id'])->pluck('image')->first();
        }

        $data['products'] = $products;


        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

     public function getbrandproducts(Request $request){
         if($request->lang == 'en'){
             $products = Product::select('id', 'title_en as title' , 'final_price' , 'price_before_offer' , 'offer' , 'offer_percentage' , 'category_id' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('brand_id' , $request->brand_id)->simplePaginate(16);
         }else{
             $products = Product::select('id', 'title_ar as title' , 'final_price' , 'price_before_offer' , 'offer' , 'offer_percentage' , 'category_id' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('brand_id' , $request->brand_id)->simplePaginate(16);
         }


         for($i = 0; $i < count($products); $i++){
            
             if(auth()->user()){
                 $user_id = auth()->user()->id;

                 $prevfavorite = Favorite::where('product_id' , $products[$i]['id'])->where('user_id' , $user_id)->first();
                 if($prevfavorite){
                     $products[$i]['favorite'] = true;
                 }else{
                     $products[$i]['favorite'] = false;
                 }

             }else{
                 $products[$i]['favorite'] = false;
             }

             if($request->lang == 'en'){
                 $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_en as title')->first();
             }else{
                 $products[$i]['category_name'] = Category::where('id' , $products[$i]['category_id'])->pluck('title_ar as title')->first();
             }
            
             $products[$i]['image'] = ProductImage::where('product_id' , $products[$i]['id'])->pluck('image')->first();
         }
        
         $data['products'] = $products;
         $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
         return response()->json($response , 200);

     }

}