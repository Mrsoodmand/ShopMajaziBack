<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $list = Product::orderby('id', 'DESC')
            ->where('type', 'virtual-number');
            $list =  $list->paginate(50);
        return view('admin.virtual-products.index', compact('list'));
    }

    /* لیست خدمات */
    public function services(Request $request)
    {
        $list = Product::where('type', 'service');
        if ($request->has('search') && $request->input('search') != null) {
            $list->where('slug', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->orderby('id','DESC')->paginate(50);
        return view('admin.product.index', compact('list'));
    }

    /* ایجاد محصول */
    public function create()
    {
        $Categories = Category::where('type', 'product')->get();
        return view('admin.product.create', compact('Categories'));
    }

    /* ثبت محصول */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'short_detail' => 'required',
                'detail' => 'required',
//                'category_id' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
                'image_gallery' => 'required',
                'slug' => 'required',
            ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            foreach ($request->input('filed')['type'] as $key => $value) {
                $select_value = "null";
                if ($value == 'select_box') {
                    $select_value = $request->input('filed')['select_value'][$key];
                }
                $Fileds[] = [
                    'type' => $value,
                    'title' => $request->input('filed')['title'][$key],
                    'col' => $request->input('filed')['col'][$key],
                    'filed_name' => $request->input('filed')['filed_name'][$key],
                    'select_value' => $select_value,
                ];
            }
            $Detail = [
                'name' => $request->input('faName'),
                'enName' => $request->input('enName'),
                'short_detail' => $request->input('short_detail'),
                'detail' => $request->input('detail'),
                'meta_title' => $request->input('meta_title'),
                'meta_desc' => $request->input('meta_desc'),
                'seo' => $request->input('seo'),
                'image_gallery' => $request->input('image_gallery'),
            ];
            foreach ($request->input('notes') as $key => $value) {
                $Notes[] = $value;
            }
            $Product = new Product();
            $Product->type = 'service';
            $Product->slug = $request->input('slug');
            $Product->country_id = $request->input('category_id');
            $Product->detail = json_encode($Detail);
            $Product->price = null;
            $Product->filds = json_encode($Fileds);
            $Product->commission = $request->input('commission');
            $Product->notes = json_encode($Notes);
            $Product->save();
            if ($request->has('dynamic_name')) {
                foreach ($request->input('dynamic_name') as $key => $value) {
                    $Plan = new Plan();
                    $Plan->name = $value;
                    $Plan->product_id = $Product->id;
                    $Plan->en_name = $request->input('dynamic_enName')[$key];
                    $Plan->price = $request->input('dynamic_price')[$key];
                    $Plan->discount = $request->input('dynamic_discount')[$key];
                    $Plan->save();
                }
            }
            return success($request, 'محصول با موفقیت ثبت شد');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ویرایش محصول */
    public function edit($id)
    {
        $edit = Product::find($id);
        $Categories = Category::where('type', 'product')->get();
        $detail = json_decode($edit->detail);
        $Plans = Plan::where('product_id', $id)->get()->toarray();
        return view('admin.product.create', compact('edit', 'Categories', 'detail', 'Plans'));
    }

    /* به روز محصول */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'short_detail' => 'required',
                'detail' => 'required',
                'category_id' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
                'image_gallery' => 'required',
                'slug' => 'required',
            ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {
            foreach ($request->input('filed')['type'] as $key => $value) {
                $select_value = "null";
                if ($value == 'select_box') {
                    $select_value = $request->input('filed')['select_value'][$key];
                }
                $Fileds[] = [
                    'type' => $value,
                    'title' => $request->input('filed')['title'][$key],
                    'col' => $request->input('filed')['col'][$key],
                    'filed_name' => $request->input('filed')['filed_name'][$key],
                    'select_value' => $select_value,
                ];
            }
            foreach ($request->input('notes') as $key => $value) {
                $Notes[] = $value;
            }
            $Detail = [
                'name' => $request->input('faName'),
                'enName' => $request->input('enName'),
                'short_detail' => $request->input('short_detail'),
                'detail' => $request->input('detail'),
                'meta_title' => $request->input('meta_title'),
                'meta_desc' => $request->input('meta_desc'),
                'seo' => $request->input('seo'),
                'image_gallery' => $request->input('image_gallery'),
            ];
            $Product = Product::find($request->input('id'));
            $Product->type = 'service';
            $Product->slug = $request->input('slug');
            $Product->country_id = $request->input('category_id');
            $Product->detail = json_encode($Detail);
            $Product->price = null;
            $Product->filds = json_encode($Fileds);
            $Product->notes = json_encode($Notes);
            $Product->commission = $request->input('commission');
            $Product->save();

            if ($request->has('dynamic_name')) {
                Plan::where('product_id', $Product->id)->delete();
                foreach ($request->input('dynamic_name') as $key => $value) {
                    $Plan = new Plan();
                    $Plan->name = $value;
                    $Plan->product_id = $Product->id;
                    $Plan->en_name = $request->input('dynamic_enName')[$key];
                    $Plan->price = $request->input('dynamic_price')[$key];
                    $Plan->discount = $request->input('dynamic_discount')[$key];
                    $Plan->save();
                }
            }
            return success($request, 'محصول با موفقیت ویرایش شد');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }
}
