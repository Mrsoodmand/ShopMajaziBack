<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Product;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountsController extends Controller
{
    /* لیست اکانت های خارجی */
    public function index(Request $request)
    {
        $list = Product::where('type', 'accounts');
        if ($request->has('search') && $request->input('search') != null) {
            $list->where('slug', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->orderby('country_id', 'DESC')->paginate(50);
        return view('admin-v2.accounts.index', compact('list'));
    }

    /* ایجاد اکانت */
    public function create()
    {
        return view('admin-v2.accounts.create');
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'name' => 'required|string',
                'type' => 'required|string',
                'price_type' => 'required|string',
                'short_detail' => 'required|string',
                'detail' => 'required|string',
                'slug' => 'required|string',
                'meta_title' => 'required|string',
                'meta_desc' => 'required|string',
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
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'price_type' => $request->input('price_type'),
                'short_detail' => $request->input('short_detail'),
                'long_detail' => $request->input('detail'),
                'slug' => $request->input('slug'),
                'meta_title' => $request->input('meta_title'),
                'meta_desc' => $request->input('meta_desc'),
                'image_gallery' => $request->input('image_gallery'),
                'seo' => $request->input('seo'),

            ];

            foreach ($request->input('notes') as $key => $value) {
                $Notes[$request->input('Notekey')[$key]] = $value;
            }

            $Product = new Product();
            $Product->faName = $request->input('name');
            $Product->type = 'accounts';
            $Product->slug = $request->input('slug');
            $Product->detail = json_encode($Detail);
            $Product->price = 0;
            $Product->filds = json_encode($Fileds);
            $Product->commission = $request->input('commission');
            $Product->notes = json_encode($Notes);
            $Product->status = $request->input('status');
            $Product->save();

            if ($request->has('dynamic_name')) {
                foreach ($request->input('dynamic_name') as $key => $value) {
                    $Plan = new Plan();
                    $Plan->name = $value;
                    $Plan->product_id = $Product->id;
                    $Plan->price = $request->input('dynamic_price')[$key];
                    $Plan->discount = $request->input('dynamic_discount')[$key];
                    $Plan->count = $request->input('dynamic_count')[$key];
                    $Plan->status = $request->input('dynamic_status')[$key];
                    $Plan->sort = $request->input('sort_plan')[$key];
                    $Plan->type = 'accounts';

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
        $Plans = Plan::where('product_id', $id)->where('type','accounts')->get()->toarray();
        return view('admin-v2.accounts.create', compact('edit', 'Categories', 'detail', 'Plans'));
    }

    /* به روز محصول */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'name' => 'required|string',
                'type' => 'required|string',
                'price_type' => 'required|string',
                'short_detail' => 'required|string',
                'detail' => 'required|string',
                'slug' => 'required|string',
                'meta_title' => 'required|string',
                'meta_desc' => 'required|string',
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
                $Notes[$request->input('Notekey')[$key]] = $value;
            }

            $Detail = [
                'name' => $request->input('name'),
                'type' => $request->input('type'),
                'price_type' => $request->input('price_type'),
                'short_detail' => $request->input('short_detail'),
                'long_detail' => $request->input('detail'),
                'slug' => $request->input('slug'),
                'meta_title' => $request->input('meta_title'),
                'meta_desc' => $request->input('meta_desc'),
                'image_gallery' => $request->input('image_gallery'),
                'seo' => $request->input('seo'),
            ];

            $Product = Product::find($request->input('id'));
            $Product->type = 'accounts';
            $Product->faName = $request->input('name');
            $Product->slug = $request->input('slug');
            $Product->detail = json_encode($Detail);
            $Product->price = 0;
            $Product->filds = json_encode($Fileds);
            $Product->commission = $request->input('commission');
            $Product->notes = json_encode($Notes);
            $Product->status = $request->input('status');
            $Product->save();

            if ($request->has('dynamic_name')) {
                foreach ($request->input('dynamic_name') as $key => $value) {
                    if ($request->input('dynamic_id')[$key] == null) {
                        $Plan = new Plan();
                    } else {
                        $Plan = Plan::find($request->input('dynamic_id')[$key]);
                    }
                    $Plan->name = $value;
                    $Plan->product_id = $Product->id;
                    $Plan->price = $request->input('dynamic_price')[$key];
                    $Plan->discount = $request->input('dynamic_discount')[$key];
                    $Plan->count = $request->input('dynamic_count')[$key];
                    $Plan->status = $request->input('dynamic_status')[$key];
                    $Plan->sort = $request->input('sort_plan')[$key];
                    $Plan->type = 'accounts';
                    $Plan->save();
                    $PlanId[] = $Plan->id;
                }
                Plan::where('product_id', $Product->id)->whereNotin('id', $PlanId)->delete();
            }
            return success($request, 'محصول با موفقیت ویرایش شد');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }
}
