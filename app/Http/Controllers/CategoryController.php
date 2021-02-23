<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Category;
use App\Brand;
use App\Product;
use App\SubCategory;


class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getcategories' , 'get_sub_categories']]);
    }

    public function getcategories(Request $request){
        if($request->lang == 'en'){
            if(!isset($request->brand_id) || $request->brand_id == 0) {
                $categoriesArray = Product::where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck('category_id')->toArray();
                $unrepeatedCategoriesArray1 = array_unique($categoriesArray);
                $unrepeatedCategoriesArray = [];
                foreach ($unrepeatedCategoriesArray1 as $key => $value) {
                    array_push($unrepeatedCategoriesArray, $value);
                }
                $categories = Category::whereIn('id', $unrepeatedCategoriesArray)->where('deleted' , 0)->select('id' , 'title_en as title' , 'image')->get();  
            }else {
                $categoriesArray = Product::where('brand_id', $request->brand_id)->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck('category_id')->toArray();
                $unrepeatedCategoriesArray1 = array_unique($categoriesArray);
                $unrepeatedCategoriesArray = [];
                foreach ($unrepeatedCategoriesArray1 as $key => $value) {
                    array_push($unrepeatedCategoriesArray, $value);
                }
                $categories = Category::whereIn('id', $unrepeatedCategoriesArray)->select('id' , 'title_en as title' , 'image')->get();
            }
             
        }else{
            if(!isset($request->brand_id) || $request->brand_id == 0) {
                $categoriesArray = Product::where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck('category_id')->toArray();
                $unrepeatedCategoriesArray1 = array_unique($categoriesArray);
                $unrepeatedCategoriesArray = [];
                foreach ($unrepeatedCategoriesArray1 as $key => $value) {
                    array_push($unrepeatedCategoriesArray, $value);
                }
                $categories = Category::whereIn('id', $unrepeatedCategoriesArray)->where('deleted' , 0)->select('id' , 'title_ar as title' , 'image')->get();
            }else {
                $categoriesArray = Product::where('brand_id', $request->brand_id)->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck('category_id')->toArray();
                $unrepeatedCategoriesArray1 = array_unique($categoriesArray);
                $unrepeatedCategoriesArray = [];
                foreach ($unrepeatedCategoriesArray1 as $key => $value) {
                    array_push($unrepeatedCategoriesArray, $value);
                }
                $categories = Category::whereIn('id', $unrepeatedCategoriesArray)->select('id' , 'title_ar as title' , 'image')->get();
            } 
        }
         
        // for($i = 0 ; $i < count($categories); $i++){
        //     if($request->lang == 'en'){
        //         $categories[$i]['brands'] = Brand::where('deleted' , 0)->where('category_id' , $categories[$i]['id'])->select('id' , 'title_en as title')->get();
        //     }else{
        //         $categories[$i]['brands'] = Brand::where('deleted' , 0)->where('category_id' , $categories[$i]['id'])->select('id' , 'title_ar as title')->get();
        //     }

        //     for($j = 0; $j < count($categories[$i]['brands']); $j++){
        //         if($request->lang == 'en'){
        //             $categories[$i]['brands'][$j]['sub_categories'] = SubCategory::where('deleted' , 0)->where('brand_id' , $categories[$i]['brands'][$j]['id'])->where('category_id' , $categories[$i]['id'])->select('id' , 'image' , 'title_en as title')->get();
        //         }else{
        //             $categories[$i]['brands'][$j]['sub_categories'] = SubCategory::where('deleted' , 0)->where('brand_id' , $categories[$i]['brands'][$j]['id'])->where('category_id' , $categories[$i]['id'])->select('id' , 'image' , 'title_ar as title')->get();
        //         }
        //     }
            
        // }

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $categories , $request->lang);
        return response()->json($response , 200);
    }
	
	public function get_sub_categories(Request $request){
        $category = Category::find($request->category_id);
        $data['category_id'] = $category['id'];
        $subArray = Product::where('category_id', $request->category_id)->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck("sub_category_id");
        if($request->brand_id){
            $subArray = Product::where('category_id', $request->category_id)->where('brand_id', $request->brand_id)->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->pluck("sub_category_id");
        }
        if($request->lang == 'en'){
            $data['category_name'] = $category['title_en'];
            
            $data['sub_categories'] = SubCategory::where('deleted' , 0)->whereIn('id' , $subArray)->select('id' , 'image' , 'title_en as title')->get();
        }else{
            $data['category_name'] = $category['title_ar'];
            $data['sub_categories'] = SubCategory::where('deleted' , 0)->whereIn('id' , $subArray)->select('id' , 'image' , 'title_ar as title')->get();
        }

        if($request->brand_id){
            $brand = Brand::find($request->brand_id);
			if ($request->lang == 'en') {
                $data['brand_name'] = $brand['title_en'];
            }else {
                $data['brand_name'] = $brand['title_ar'];
            }
            $data['cover_image'] = $brand['cover_image'];
            $data['logo'] = $brand['image'];
            $data['brand_id'] = $request->brand_id;
        }

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }
    
    
	
	

}    