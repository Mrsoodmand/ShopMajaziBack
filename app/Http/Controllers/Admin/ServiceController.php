<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /* لیست خدمات */
    public function index(Request $request)
    {
        $list = Product::where('type', 'service');
        if ($request->has('search') && $request->input('search') != null) {
            $list->where('slug', 'LIKE', '%' . $request->input('search') . '%');
        }
        $list = $list->orderby('country_id', 'DESC')->paginate(50);
        return view('admin-v2.virtual-service.index', compact('list'));
    }

    /* ایجاد محصول */
    public function create()
    {
        $Categories = Category::where('type', 'product')->get();
        return view('admin-v2.virtual-service.create', compact('Categories'));
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
                'code' => $request->input('code'),

            ];

            foreach ($request->input('notes') as $key => $value) {
                $Notes[$request->input('Notekey')[$key]] = $value;
            }
            $faq = Add_Faq($request);
            $Product = new Product();

            $Product->type = 'service';
            $Product->slug = $request->input('slug');
            $Product->country_id = $request->input('category_id');
            $Product->detail = json_encode($Detail);
            $Product->price = $request->input('price');
            $this->fillCountPricing($Product, $request);
            $Product->filds = json_encode($Fileds);
            $Product->commission = $request->input('commission');
            $Product->notes = json_encode($Notes);
            $Product->faq = json_encode($faq);
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
                    $Plan->save();
                    $PlanId[] = $Plan->id;
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
        return view('admin-v2.virtual-service.create', compact('edit', 'Categories', 'detail', 'Plans'));
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
                $Notes[$request->input('Notekey')[$key]] = $value;
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
                'code' => $request->input('code'),
            ];

            $faq = Add_Faq($request);
            $Product = Product::find($request->input('id'));
            $Product->type = 'service';
            $Product->faName = $request->input('faName');
            $Product->slug = $request->input('slug');
            $Product->country_id = $request->input('category_id');
            $Product->detail = json_encode($Detail);
            $Product->price = $request->input('price');
            $this->fillCountPricing($Product, $request);
            $Product->status = $request->input('status');
            $Product->filds = json_encode($Fileds);
            $Product->notes = json_encode($Notes);
            $Product->faq = json_encode($faq);
            $Product->commission = $request->input('commission');
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

    /* قیمت هر ۱۰۰ عدد برای انتخاب تعداد دلخواه (خالی = پلن ها) */
    private function fillCountPricing(Product $Product, Request $request)
    {
        $PerHundred = intval($request->input('price_per_100'));
        $Min = max(100, intval(round(intval($request->input('min_count', 100)) / 100) * 100));
        $Max = max($Min, intval(round(intval($request->input('max_count', 10000)) / 100) * 100));
        $Product->price_per_100 = $PerHundred > 0 ? $PerHundred : null;
        $Product->min_count = $Min;
        $Product->max_count = $Max;
    }
}
