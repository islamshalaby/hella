<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JD\Cloudder\Facades\Cloudder;
use App\Product;
use App\Category;
use App\Option;
use App\Brand;
use App\SubCategory;
use App\SubTwoCategory;
use App\ProductImage;
use App\ProductOption;
use App\HomeSection;
use App\HomeElement;
use App\ProductMultiOption;
use App\MultiOptionValue;
use App\MultiOption;

class ProductController extends AdminController{
    // show products
    public function show(Request $request) {
        $data['categories'] = Category::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['brands'] = Brand::where('deleted', 0)->orderBy('id', 'desc')->get();
        if($request->expire){
            $data['products'] = Product::where('deleted', 0)->where('remaining_quantity' , '<' , 10)->orderBy('id' , 'desc')->get();
            $data['expire'] = 'soon';
        }else{
            $data['products'] = Product::where('deleted', 0)->orderBy('id' , 'desc')->get();
            $data['expire'] = 'no';
        }
        
        
        $data['encoded_products'] = json_encode($data['products']);
        return view('admin.products', ['data' => $data]);
    }

    // fetch category brands
    public function fetch_category_brands(Category $category) {
        $rows = $category->brands;

        $data = json_decode(($rows));

        return response($data, 200);
    }

    // fetch brand sub categories
    public function fetch_brand_sub_categories(Brand $brand) {
        $rows = $brand->subCategories;

        $data = json_decode(($rows));

        return response($data, 200);
    }

    // fetch sub category products
    public function sub_category_products(SubCategory $subCategory) {
        $rows = Product::where('sub_category_id', $subCategory->id)->with('images', 'category')->get();
        $data = json_decode(($rows));

        return response($data, 200);
    }

    // edit get
    public function EditGet(Product $product) {
        $data['product'] = $product;
        $data['productPrands'] = $product->productBrands()->pluck('id')->toArray();
        // dd($data['productPrands']);
        $data['barcode'] = uniqid();
        // dd($product);
        $data['categories'] = Category::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['sub_two_category'] = SubTwoCategory::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['brands'] = Brand::where('deleted', 0)->orderBy('id', 'desc')->get();
        
        $data['category'] = Category::find($data['product']['category_id']);
        // dd($data['category']);
        $data['options'] = [];
        $data['product_options'] = [];
        $data['Home_sections'] = HomeSection::where('type', 4)->get();
        $data['Home_sections_ids'] = HomeSection::where('type', 4)->pluck('id');
        $data['elements'] = HomeElement::where('element_id', $product->id)->whereIn('home_id', $data['Home_sections_ids'])->pluck('home_id')->toArray();
        // dd($data['product']->multiOptions[0]);
        $data['multi_options'] = $data['product']->multiOptions()->pluck('multi_option_value_id')->toArray();
        $data['encoded_multi_options'] = json_encode($data['multi_options']);
        $data['multi_options_id'] = $data['product']->multiOptions()->pluck('multi_option_id')->toArray();
        $data['encoded_multi_options_id'] = json_encode($data['multi_options_id']);
        
        if (count($data['product']->options) > 0) {
            for ($i = 0; $i < count($data['product']->options); $i ++) {
                $arr['option_id'] = $data['product']->options[$i]->option_id;
                $arr['id']  = $data['product']->options[$i]->id;
                $arr['value_en']  = $data['product']->options[$i]->value_en;
                $arr['value_ar']  = $data['product']->options[$i]->value_ar;
                $option = Option::findOrFail($data['product']->options[$i]->option_id);
                $arr['option_title_en'] = $option->title_en;
                $arr['option_title_ar'] = $option->title_ar;
                array_push($data['product_options'], $arr['option_id']);
                array_push($data['options'], $arr);
            }
        }
        $data['prod_options'] = json_encode($data['product_options']);
        return view('admin.product_edit', ['data' => $data]);
    }

    // edit post
    public function EditPost(Request $request, Product $product) {
        $total_quantity = (int)$request->total_quatity + 1;
        $request->validate([
            'barcode' => 'unique:products,barcode,' . $product->id . '|max:255|nullable',
            // 'stored_number' => 'unique:products,stored_number,' . $product->id . '|max:255|nullable',
            'title_en' => 'required',
            'title_ar' => 'required',
            'description_ar' => 'required',
            'description_en' => 'required',
            // 'price_before_offer' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            // 'total_quatity' => 'required',
            // 'remaining_quantity' => 'required|numeric|lt:' . $total_quantity
        ]);
        $product_post = $request->except(['images', 'option', 'value_en', 'value_ar']);
        if (empty($product_post['brand_id'])) {
            $product_post['brand_id'] = 0;
        }

        if ($request->brand_id && count($request->brand_id) > 0) {
            $product->productBrands()->sync($request->brand_id);
        }

        if (isset($request->home_section) && !empty($request->home_section)) {
            $data['Home_sections_ids'] = HomeSection::where('type', 4)->pluck('id')->toArray();
            $data['elements'] = HomeElement::where('element_id', $product->id)->whereIn('home_id', $data['Home_sections_ids'])->select('id')->first();
            if (!empty($data['elements'])) {
                $data['product_element'] = HomeElement::findOrFail($data['elements']['id']);

                $data['product_element']->update(['home_id'=>$request->home_section]);
            }else {
                HomeElement::create(['home_id'=>$request->home_section, 'element_id' => $product->id]);
            }
        }
        
        

        if (isset($request->total_amount) && is_array($request->total_amount) && isset($request->multi_option_id) && $request->multi_option_id != "none") {
            if (count($product->multiOptions) > 0) {
                $product->multiOptions()->delete();
            }
            $mOption = MultiOption::where('id', $request->multi_option_id)->select('title_en', 'title_ar')->first();
            for ($n = 0; $n < count($request->total_amount); $n ++) {
                $barcode = "";
                $stored_number = "";

                if (isset($request->barcodes[$n])) {
                    $barcode = $request->barcodes[$n];
                }

                if (isset($request->stored_numbers[$n])) {
                    $stored_number = $request->stored_numbers[$n];
                }
                if (isset($request->offer)) {
                    $final_price = number_format((float)$request->price_after_discount[$n], 3, '.', '');
                    $before_discount = number_format((float)$request->final_price[$n], 3, '.', '');
                }else {
                    $final_price = number_format((float)$request->final_price[$n], 3, '.', '');
                    $before_discount = number_format((float)$request->final_price[$n], 3, '.', '');
                }
                $mOptionVal = MultiOptionValue::where('id', $request->multi_option_value_id[$n])->select('value_en', 'value_ar')->first();
                ProductMultiOption::create([
                    'product_id' => $product->id,
                    'multi_option_id' => $request->multi_option_id,
                    'option_en' => $mOption['title_en'],
                    'option_ar' => $mOption['title_ar'],
                    'multi_option_value_id' => $request->multi_option_value_id[$n],
                    'val_en' => $mOptionVal['value_en'],
                    'val_ar' => $mOptionVal['value_ar'],
                    'final_price' => $final_price,
                    'price_before_offer' => $before_discount,
                    'total_quatity' => $request->total_amount[$n],
                    'remaining_quantity' => $request->remaining_amount[$n],
                    'barcode' => $barcode,
                    'stored_number' => $stored_number
                ]);
            }

            if (isset($request->offer)) {
                $product->update([
                    'offer' => 1,
                    'offer_percentage' => (double)$request->offer_percentage,
                    'multi_options' => 1,
                    'final_price' => "0",
                    'price_before_offer' => "0",
                    'total_quatity' => $product->multiOptions()->sum('total_quatity'),
                    'remaining_quantity' => $product->multiOptions()->sum('remaining_quantity')
                ]);
            }else {
                $selected_prod_data['offer'] = 0;
                $selected_prod_data['offer_percentage'] = 0;
                $selected_prod_data['price_before_offer'] = 0;
                $product->update([
                    'offer' => 0,
                    'offer_percentage' => 0,
                    'multi_options' => 1,
                    'final_price' => "0",
                    'price_before_offer' => "0",
                    'total_quatity' => $product->multiOptions()->sum('total_quatity'),
                    'remaining_quantity' => $product->multiOptions()->sum('remaining_quantity')
                ]);
            }
        }else {
            if (count($product->multiOptions) > 0) {
                $product->multiOptions()->delete();
            }
            if (isset($request->offer)) {
                $price_before = number_format((float)$request->price_before_offer, 3, '.', '');
                $discount_value = (double)$request->offer_percentage / 100;
                $price_value = $price_before * $discount_value;
                $finalVP = $price_before - $price_value;
                $selected_prod_data['final_price'] = number_format((float)$finalVP, 3, '.', '');
            }
    
            if (!isset($request->offer)) {
                $selected_prod_data['final_price'] = number_format((float)$request->price_before_offer, 3, '.', '');
            }
    
            if (isset($request->offer)) {
                $selected_prod_data['offer'] = 1;
                $selected_prod_data['offer_percentage'] = (double)$request->offer_percentage;
            }else {
                $selected_prod_data['offer'] = 0;
                $selected_prod_data['offer_percentage'] = 0;
                $selected_prod_data['price_before_offer'] = 0;
            }
            $selected_prod_data['total_quatity'] = $request->total_quatity;
            $selected_prod_data['remaining_quantity'] = $request->remaining_quantity;
            $selected_prod_data['multi_options'] = 0;
            $product->update($selected_prod_data);
        }
        if ( $images = $request->file('images') ) {
            foreach ($images as $image) {
                $image_name = $image->getRealPath();
                Cloudder::upload($image_name, null);
                $imagereturned = Cloudder::getResult();
                $image_id = $imagereturned['public_id'];
                $image_format = $imagereturned['format'];    
                $image_new_name = $image_id.'.'.$image_format;
                ProductImage::create(["image" => $image_new_name, "product_id" => $product->id]);
            }
        }

        if (isset($product->options) && count($product->options) > 0) {
            $product->options()->delete();
        }

        if (isset($request->option) && count($request->option) > 0 && isset($request->value_en) && count($request->value_en) > 0) {
            for ($i = 0; $i < count($request->option); $i ++) {
                $post_option['option_id'] = $request->option[$i];
                $post_option['product_id'] = $product->id;
                $post_option['value_en'] = $request->value_en[$i];
                $post_option['value_ar'] = $request->value_ar[$i];
                ProductOption::create($post_option);
            }
        }

        return redirect()->route('products.index');
        
    }

    // fetch category products
    public function fetch_category_products(Category $category) {
        $rows = Product::where('category_id', $category->id)->with('images', 'category')->get();
        // dd($rows);
        $data = json_decode(($rows));


        return response($data, 200);
    }

    // fetch brand products
    public function fetch_brand_products(Brand $brand) {
        $rows = Product::where('brand_id', $brand->id)->with('images', 'category')->get();
        $data = json_decode(($rows));


        return response($data, 200);
    }

    // delete product image
    public function delete_product_image(ProductImage $productImage) {
        $image = $productImage->image;
        $publicId = substr($image, 0 ,strrpos($image, "."));    
        Cloudder::delete($publicId);
        $productImage->delete();

        return redirect()->back();
    }

    // details
    public function details(Product $product) {
        $data['product'] = $product;
        $data['options'] = [];
        if (count($data['product']->options) > 0) {
            for ($i = 0; $i < count($data['product']->options); $i ++) {
                $arr['option_id'] = $data['product']->options[$i]->option_id;
                $arr['id']  = $data['product']->options[$i]->id;
                $arr['value_en']  = $data['product']->options[$i]->value_en;
                $arr['value_ar']  = $data['product']->options[$i]->value_ar;
                $option = Option::findOrFail($data['product']->options[$i]->option_id);
                $arr['option_title_en'] = $option->title_en;
                $arr['option_title_ar'] = $option->title_ar;

                array_push($data['options'], $arr);
            }
        }

        

        return view('admin.product_details', ['data' => $data]);
    }

    // delete
    public function delete(Product $product) {
        
        $product->update(['deleted' => 1]);

        return redirect()->back();
    }

    // fetch category options
    public function fetch_category_options(Category $category) {
        $rows = $category->options;
        
        $data = json_decode(($rows));

        return response($data, 200);
    }

    // fetch sub category multi options
    public function fetch_sub_category_multi_options(Category $category) {
        $rows = $category->multiOptionsWithValues;
        // dd($rows);
        $data = json_decode(($rows));

        return response($data, 200);
    }

    // product search
    public function product_search(Request $request) {
        $data['categories'] = Category::where('deleted', 0)->orderBy('id', 'desc')->get();
        if (isset($request->name)) {
            $data['products'] = Product::with('images')->where('title_en', 'like', '%' . $request->name . '%')
                                ->orWhere('title_ar', 'like', '%' . $request->name . '%')->get();
            // dd($data['products']);
            return view('admin.searched_products', ['data' => $data]);
        }else {
            return view('admin.product_search', ['data' => $data]);
        }
    }

    // update quantity
    public function update_quantity(Request $request, Product $product) {
        $total_quatity = (int)$request->remaining_quantity + (int)$product->total_quatity;
        $remaining_quantity = (int)$request->remaining_quantity + (int)$product->remaining_quantity;;
        $product->update(['total_quatity' => $total_quatity, 'remaining_quantity' => $remaining_quantity]);

        return redirect()->back();
    }

    // update quantity
    public function update_quantity_m_option(Request $request, ProductMultiOption $option) {
        // dd($request->all());
        $product = Product::find($option->product_id);
        // dd($product);
        $product->update([
            'total_quatity' => (int)$request->remaining_quantity + (int)$product->total_quatity,
            'remaining_quantity' => (int)$request->remaining_quantity + (int)$product->remaining_quantity
            ]);
        
        $total_quatity = (int)$request->remaining_quantity + (int)$option->total_quatity;
        $remaining_quantity = (int)$request->remaining_quantity + (int)$option->remaining_quantity;
        $option->update(['total_quatity' => $total_quatity, 'remaining_quantity' => $remaining_quantity]);

        return redirect()->back();
    }

    // add get
    public function addGet(Request $request) {
        $data['categories'] = Category::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['sub_two_category'] = SubTwoCategory::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['brands'] = Brand::where('deleted', 0)->orderBy('id', 'desc')->get();
        $data['Home_sections'] = HomeSection::where('type', 4)->get();
        $data['barcode'] = uniqid();

        if (isset($request->sub_cat)) {
            $data['sub_cat'] = SubCategory::findOrFail($request->sub_cat);
        }

        return view('admin.product_form', ['data' => $data]);
    }

    // add post
    public function addPost(Request $request) {
        $total_quantity = (int)$request->total_quatity + 1;
        $request->validate([
            'barcode' => 'unique:products,barcode|max:255|nullable',
            'stored_number' => 'unique:products,stored_number|max:255|nullable',
            'title_en' => 'required',
            'title_ar' => 'required',
            'description_ar' => 'required',
            'description_en' => 'required',
            // 'price_before_offer' => 'required',
            'category_id' => 'required',
            'sub_category_id' => 'required',
            // 'total_quatity' => 'required',
            // 'remaining_quantity' => 'required|numeric|lt:' . $total_quantity,
            'weight' => 'required'
        ]);
        $product_post = $request->except(['images', 'option', 'value_en', 'value_ar', 'home_section', 'total_quatity', 'remaining_quantity', 'final_price', 'total_amount', 'remaining_amount', 'price_after_discount', 'barcodes', 'stored_numbers', 'brand_id', 'sub_two_category_id']);
        
        if (isset($product_post['offer'])) {
            $price_before = number_format((float)$product_post['price_before_offer'], 3, '.', '');
            $discount_value = (int)$product_post['offer_percentage'] / 100;
            $price_value = $price_before * $discount_value;
            $finalVP = $price_before - $price_value;
            $product_post['final_price'] = number_format((float)$finalVP, 3, '.', '');
        }

        if (!isset($product_post['final_price']) || empty($product_post['final_price'])) {
            $product_post['final_price'] = number_format((float)$product_post['price_before_offer'], 3, '.', '');
        }

        if (isset($product_post['offer'])) {
            $product_post['offer'] = 1;
        }else {
            $product_post['offer'] = 0;
            $product_post['offer_percentage'] = 0;
            $product_post['price_before_offer'] = 0;
        }
        // dd($product_post);
        $createdProduct = Product::create($product_post);

        if ($request->brand_id && count($request->brand_id) > 0) {
            $createdProduct->productBrands()->sync($request->brand_id);
        }

        if (isset($request->home_section)) {
            HomeElement::create(['home_id' => $request->home_section, 'element_id' => $createdProduct['id']]);
        }

        if ( $images = $request->file('images') ) {
            foreach ($images as $image) {
                $image_name = $image->getRealPath();
                Cloudder::upload($image_name, null);
                $imagereturned = Cloudder::getResult();
                $image_id = $imagereturned['public_id'];
                $image_format = $imagereturned['format'];    
                $image_new_name = $image_id.'.'.$image_format;
                ProductImage::create(["image" => $image_new_name, "product_id" => $createdProduct['id']]);
            }
        }

        $selected_product = Product::where('id', $createdProduct['id'])->first();
        
        if (isset($request->total_amount) && is_array($request->total_amount) && isset($request->multi_option_id) && $request->multi_option_id != "none") {
            $mOption = MultiOption::where('id', $request->multi_option_id)->select('title_en', 'title_ar')->first();
            for ($n = 0; $n < count($request->total_amount); $n ++) {
                if (isset($request->offer)) {
                    $final_price = number_format((float)$request->price_after_discount[$n], 3, '.', '');
                    $before_discount = number_format((float)$request->final_price[$n], 3, '.', '');
                }else {
                    $final_price = number_format((float)$request->final_price[$n], 3, '.', '');
                    $before_discount = number_format((float)$request->final_price[$n], 3, '.', '');
                }
                $barcode = "";
                $stored_number = "";

                if (isset($request->barcodes[$n])) {
                    $barcode = $request->barcodes[$n];
                }

                if (isset($request->stored_numbers[$n])) {
                    $stored_number = $request->stored_numbers[$n];
                }
                $mOptionVal = MultiOptionValue::where('id', $request->multi_option_value_id[$n])->select('value_en', 'value_ar')->first();
                ProductMultiOption::create([
                    'product_id' => $createdProduct['id'],
                    'multi_option_id' => $request->multi_option_id,
                    'option_en' => $mOption['title_en'],
                    'option_ar' => $mOption['title_ar'],
                    'multi_option_value_id' => $request->multi_option_value_id[$n],
                    'val_en' => $mOptionVal['value_en'],
                    'val_ar' => $mOptionVal['value_ar'],
                    'final_price' => $final_price,
                    'price_before_offer' => $before_discount,
                    'total_quatity' => $request->total_amount[$n],
                    'remaining_quantity' => $request->remaining_amount[$n],
                    'barcode' => $barcode,
                    'stored_number' => $stored_number
                ]);
            }

            
            if (isset($request->offer)) {
                $selected_product->update([
                    'offer' => 1,
                    'offer_percentage' => (double)$request->offer_percentage,
                    'multi_options' => 1,
                    'total_quatity' => $selected_product->multiOptions()->sum('total_quatity'),
                    'remaining_quantity' => $selected_product->multiOptions()->sum('remaining_quantity')
                ]);
            }else {
                $selected_prod_data['offer'] = 0;
                $selected_prod_data['offer_percentage'] = 0;
                $selected_prod_data['price_before_offer'] = 0;
                $selected_product->update([
                    'offer' => 0,
                    'offer_percentage' => 0,
                    'multi_options' => 1,
                    'total_quatity' => $selected_product->multiOptions()->sum('total_quatity'),
                    'remaining_quantity' => $selected_product->multiOptions()->sum('remaining_quantity')
                ]);
            }
        }else {
            if (isset($request->offer)) {
                $price_before = number_format((float)$request->price_before_offer, 3, '.', '');
                $discount_value = (double)$request->offer_percentage / 100;
                $price_value = $price_before * $discount_value;
                $finalVP = $price_before - $price_value;
                $selected_prod_data['final_price'] = number_format((float)$finalVP, 3, '.', '');
            }
    
            if (!isset($request->offer)) {
                $selected_prod_data['final_price'] = number_format((float)$request->price_before_offer, 3, '.', '');
            }
    
            if (isset($request->offer)) {
                $selected_prod_data['offer'] = 1;
                $selected_prod_data['offer_percentage'] = (double)$request->offer_percentage;
            }else {
                $selected_prod_data['offer'] = 0;
                $selected_prod_data['offer_percentage'] = 0;
                $selected_prod_data['price_before_offer'] = 0;
            }
            $selected_prod_data['total_quatity'] = $request->total_quatity;
            $selected_prod_data['remaining_quantity'] = $request->remaining_quantity;
            $selected_product->update($selected_prod_data);
        }

        if (isset($request->option) && count($request->option) > 0 && isset($request->value_en) && count($request->value_en) > 0) {
            for ($i = 0; $i < count($request->option); $i ++) {
                $post_option['option_id'] = $request->option[$i];
                $post_option['product_id'] = $createdProduct['id'];
                $post_option['value_en'] = $request->value_en[$i];
                $post_option['value_ar'] = $request->value_ar[$i];
                ProductOption::create($post_option);
            }
        }

        return redirect()->route('products.index')
                ->with('success', __('Created successfully'));
    }

    // get products by subcat
    public function get_product_by_sub_cat(Request $request) {
        $data['products'] = Product::with('images')->where('deleted' , 0)->where('remaining_quantity' , '<' , 10)->where('sub_category_id', $request->sub_cat)->get();
        $data['sub_cat'] = $request->sub_cat;

        return view('admin.searched_products', ['data' => $data]);
    }

    // fetch sub categories by category
    public function fetch_sub_categories_by_category(Category $category) {
        $rows = SubCategory::where('deleted', 0)->where('category_id', $category->id)->get();

        $data = json_decode($rows);
        return response($data, 200);
    }

    // visibility status product
    public function visibility_status_product(Product $product, $status) {
        $product->update(['hidden' => $status]);

        return redirect()->back();
    }

    
}