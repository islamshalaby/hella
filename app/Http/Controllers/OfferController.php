<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Offer;
use App\Product;
use App\Category;
use App\Brand;
use App\SubCategory;
use App\ProductImage;
use App\HomeSection;
use App\HomeElement;
use App\Favorite;
use App\Ad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;


class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getoffers' , 'getoffersandroid']]);
    }

    public function getoffers(Request $request){
        $home_data = HomeSection::orderBy('sort' , 'Asc')->get();
        $ads = [];
        for($i = 0; $i < count($home_data); $i++){
            
            $ids = HomeElement::where('home_id' , $home_data[$i]['id'])->pluck('element_id');
            if($home_data[$i]['type'] == 1){
                
                $ads = Ad::select('id' ,'image' , 'type' , 'content')->whereIn('id' , $ids)->get();

                // array_push($ads , $ad);

            }
        }
        if($request->lang == 'en'){
            $products = Product::select('id', 'title_en as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'numbers', 'multi_options', 'kg_en as kg' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('offer' , 1)->simplePaginate(16);
        }else{
            $products = Product::select('id', 'title_ar as title' , 'final_price' , 'price_before_offer' , 'weight' , 'offer' , 'offer_percentage' , 'category_id', 'numbers', 'multi_options', 'kg_ar as kg' )->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->where('offer' , 1)->simplePaginate(16);
        }
$products->makeHidden('multiOptions');
        for($i = 0; $i < count($products); $i++){
            if ($products[$i]['multi_options'] == 1) {
                    $products[$i]['final_price'] = $products[$i]['multiOptions'][0]['final_price'];
                    $products[$i]['price_before_offer'] = $products[$i]['multiOptions'][0]['price_before_offer'];
                }
                unset($products[$i]['multi_options']);
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
        $data['ads'] = $ads;
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);

    }

    // public function getoffersandroid(Request $request){

    //     $offers_before = Offer::orderBy('sort' , 'ASC')->get();
    //     $offers = [];
        
    //     for($i = 0; $i < count($offers_before); $i++){
    //         if($offers_before[$i]['type'] == 1){
    //             $result = Product::find($offers_before[$i]['target_id']);
    //             if($result['deleted'] == 0 && $result['hidden'] == 0){
    //                 array_push($offers , $offers_before[$i]);
    //             }
    //         }else{
    //             $result = Category::find($offers_before[$i]['target_id']);
    //             if($result['deleted'] == 0){
    //                 array_push($offers , $offers_before[$i]);
    //             }
    //         }



    //     }

    //     $new_offers = [];
    //     for($i = 0; $i < count($offers); $i++){
    //         if($offers[$i]->size == 1 || $offers[$i]->size == 2 ){
    //             $count = count($new_offers);
    //             $new_offers[$count] = [];
    //             array_push($new_offers[$count] , $offers[$i]);
    //             $offer_element = new \stdClass();
    //             $offer_element->id = 0;
    //             $offer_element->image  = '';
    //             $offer_element->size = $offers[$i]->size;
    //             $offer_element->type = 0;
    //             $offer_element->target_id = 0;
    //             $offer_element->sort = 0;
    //             $offer_element->created_at = "";
    //             $offer_element->updated_at = "";
    //             array_push($new_offers[$count] , $offer_element);
    //         }

    //         if($offers[$i]->size == 3){

    //             if(count($offers) > 1 ){

    //                 $count_offers = count($new_offers);

    //                 $last_count = count($new_offers[$count_offers - 1]);
                    
    //                 if($last_count == 2){
    //                     $new_offers[$count_offers] = [];
    //                     array_push($new_offers[$count_offers] , $offers[$i]);
    //                     if(count($offers) > $i+1 ){
    //                          if($offers[$i+1]->size != 3){
    //                             $offer_element = new \stdClass();
    //                             $offer_element->id = 0;
    //                             $offer_element->image  = '';
    //                             $offer_element->size = 3;
    //                             $offer_element->type = 0;
    //                             $offer_element->target_id = 0;
    //                             $offer_element->sort = 0;
    //                             $offer_element->created_at = "";
    //                             $offer_element->updated_at = "";
    //                             array_push($new_offers[$count_offers] , $offer_element);
    //                         }
    //                     }else{
    //                         $offer_element = new \stdClass();
    //                         $offer_element->id = 0;
    //                         $offer_element->image  = '';
    //                         $offer_element->size = 3;
    //                         $offer_element->type = 0;
    //                         $offer_element->target_id = 0;
    //                         $offer_element->sort = 0;
    //                         $offer_element->created_at = "";
    //                         $offer_element->updated_at = "";
    //                         array_push($new_offers[$count_offers] , $offer_element);
    //                     }
    //                 }else{
    //                     array_push($new_offers[$count_offers - 1] , $offers[$i]);
    //                 }

    //             }else{
    //                 $count = count($new_offers);
    //                 $new_offers[$count] = [];
    //                 array_push($new_offers[$count] , $offers[$i]);
    //                 $offer_element = new \stdClass();
    //                 $offer_element->id = 0;
    //                 $offer_element->image  = '';
    //                 $offer_element->size = $offers[$i]->size;
    //                 $offer_element->type = 0;
    //                 $offer_element->target_id = 0;
    //                 $offer_element->sort = 0;
    //                 $offer_element->created_at = "";
    //                 $offer_element->updated_at = "";
    //                 array_push($new_offers[$count] , $offer_element);
    //             }
                
    //         }

    //     }

    //     $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $new_offers , $request->lang);
    //     return response()->json($response , 200);

    // }

}