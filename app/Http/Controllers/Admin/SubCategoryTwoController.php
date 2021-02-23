<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\DB;
use App\Category;
use App\SubCategory;
use App\SubTwoCategory;

class SubCategoryTwoController extends AdminController{
    // get all sub categories
    public function show(){
        $data['sub_categories'] = SubTwoCategory::where('deleted', 0)->orderBy('id' , 'desc')->get();
        return view('admin.sub_two_categories' , ['data' => $data]);
    }

    // add get
    public function addGet() {
        $data['categories'] = Category::where('deleted', 0)->orderBy('id' , 'desc')->get();
        $data['sub_categories'] = SubCategory::where('deleted', 0)->orderBy('id' , 'desc')->get();

        return view('admin.sub_two_categories_form', ['data' => $data]);
    }

    // add post
    public function AddPost(Request $request){
        $post = $request->all();
        // $image_name = $request->file('image')->getRealPath();
        // Cloudder::upload($image_name, null);
        // $imagereturned = Cloudder::getResult();
        // $image_id = $imagereturned['public_id'];
        // $image_format = $imagereturned['format'];    
        // $image_new_name = $image_id.'.'.$image_format;
        // $post = $request->all();
        // $post['image'] = $image_new_name;
        SubTwoCategory::create($post);

        return redirect()->route('sub_categories_two.index');
    }

    // edit get
    public function EditGet(Request $request) {
        $data['sub_category'] = SubTwoCategory::find($request->subCategory);
        $data['categories'] = Category::where('deleted', 0)->orderBy('id' , 'desc')->get();
        $data['sub_categories'] = SubCategory::where('deleted', 0)->orderBy('id' , 'desc')->get();
        // dd($data['categories']);
        return view('admin.sub_two_categories_edit', ['data' => $data]);
    }

      // post edit
      public function EditPost(Request $request) {
       $subCategory = SubTwoCategory::find($request->subCategory);

       $subCategory->title_en = $request->title_en;
       $subCategory->title_ar = $request->title_ar;
       $subCategory->sub_category_id = $request->sub_category_id;
       $subCategory->category_id = $request->category_id;
       
       $subCategory->save();

        return redirect()->route('sub_categories_two.index');
    }

    // fetch brands
    public function fetchBrands(Category $category) {
        $row = $category->brands()->where('deleted', 0)->get();
        $data = json_decode($row);
        
        return response($data, 200);
    }

    // details
    public function details(SubCategory $subCategory) {
        $data['sub_category'] = $subCategory;

        return view('admin.sub_category_details', ['data' => $data]);
    }

    // delete sub category
    public function delete(Request $request){

        $subCategory = SubTwoCategory::find($request->subCategory);
        $subCategory->deleted = 1;
        $subCategory->save();
        return redirect()->back();
    }
}